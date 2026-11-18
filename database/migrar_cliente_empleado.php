<?php

/**
 * Crea la tabla cliente_empleado y migra el "tipo" del cliente a cada relacion cliente-empleado.
 * Las relaciones salen de las horas cargadas y del Excel de ClickUp "CLIENTES CONTRATADOS"
 * (migraciones/relaciones_clientes_2026-11.php); el tipo de cada una es el del cliente en el docx de tarifas.
 *
 * Uso (desde la raiz del proyecto):
 *   php database/migrar_cliente_empleado.php                         -> simulacion: solo muestra lo que haria
 *   php database/migrar_cliente_empleado.php --solo-respaldo         -> solo genera el respaldo .sql completo
 *   php database/migrar_cliente_empleado.php --ejecutar              -> respalda TODO en .sql, crea, migra y elimina clientes.tipo
 *   php database/migrar_cliente_empleado.php --ejecutar --conservar-columna
 *                                                                    -> igual, pero NO elimina clientes.tipo
 *
 * Con --ejecutar, lo primero es un respaldo completo de la base en database/respaldos/<base>_<fecha>.sql.
 * Si el respaldo falla, no se modifica nada.
 * Restaurar: mysql -u USUARIO -p NOMBRE_BASE < database/respaldos/archivo.sql
 *
 * Se puede correr varias veces: lo que ya existe no se duplica.
 * Usa la conexion de .env (DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT).
 */

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../includes/config/databases.php";

use Dotenv\Dotenv;
use MVC\Config;

$opciones = array_slice($argv, 1);
$ejecutar = in_array("--ejecutar", $opciones, true);
$soloRespaldo = in_array("--solo-respaldo", $opciones, true);
$conservarColumna = in_array("--conservar-columna", $opciones, true);

$desconocidas = array_diff($opciones, ["--ejecutar", "--solo-respaldo", "--conservar-columna"]);
if ($desconocidas) {
    fwrite(STDERR, "Opcion desconocida: " . implode(" ", $desconocidas) . PHP_EOL);
    exit(1);
}

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required(["DB_HOST", "DB_USER", "DB_PORT"])->notEmpty();
$dotenv->required("DB_PASS");

$schema = $_ENV["DB_NAME"] ?? "subp";
date_default_timezone_set(Config::get("app.zona_horaria", "America/Bogota"));

function titulo(string $texto): void { echo PHP_EOL . "== $texto ==" . PHP_EOL; }
function info(string $texto): void { echo "  $texto" . PHP_EOL; }
function aviso(string $texto): void { echo "  [AVISO] $texto" . PHP_EOL; }
function fallo(string $texto): never { fwrite(STDERR, PHP_EOL . "[ERROR] $texto" . PHP_EOL); exit(1); }
function clave(string $nombre): string { return mb_strtolower(trim(preg_replace('/\s+/', " ", $nombre)), "UTF-8"); }

/**
 * Respaldo completo (estructura + datos de todas las tablas y vistas) en un archivo .sql.
 * Se escribe por partes para no cargar tablas grandes en memoria.
 * @return array<string,int> filas respaldadas por tabla
 */
function respaldarBase(mysqli $db, string $schema, string $archivo): array
{
    $carpeta = dirname($archivo);
    if (!is_dir($carpeta) && !mkdir($carpeta, 0750, true)) {
        throw new RuntimeException("No se pudo crear la carpeta $carpeta");
    }
    $temporal = $archivo . ".parcial";
    $f = fopen($temporal, "wb");
    if (!$f) throw new RuntimeException("No se pudo escribir $temporal");

    $escribir = function (string $texto) use ($f) {
        if (fwrite($f, $texto) === false) throw new RuntimeException("Error escribiendo el respaldo (¿disco lleno?)");
    };
    $id = fn(string $nombre) => "`" . str_replace("`", "``", $nombre) . "`";

    try {
        $version = $db->query("SELECT VERSION() AS v")->fetch_assoc()["v"];
        $escribir("-- Respaldo completo de `$schema`\n-- Fecha: " . date("Y-m-d H:i:s") . " | MySQL $version\n-- Generado por database/migrar_cliente_empleado.php\n\n");
        $escribir("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET UNIQUE_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\nSET time_zone = '+00:00';\n\n");
        $db->query("SET time_zone = '+00:00'"); // TIMESTAMP se exporta en UTC y se restaura igual

        $objetos = $db->query("SHOW FULL TABLES")->fetch_all(MYSQLI_NUM);
        $tablas = array_column(array_filter($objetos, fn($o) => $o[1] === "BASE TABLE"), 0);
        $vistas = array_column(array_filter($objetos, fn($o) => $o[1] === "VIEW"), 0);

        // Consistencia: todas las tablas se leen en la misma "foto" (InnoDB)
        $db->query("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
        $db->query("START TRANSACTION WITH CONSISTENT SNAPSHOT");

        $binarios = [MYSQLI_TYPE_TINY_BLOB, MYSQLI_TYPE_MEDIUM_BLOB, MYSQLI_TYPE_LONG_BLOB, MYSQLI_TYPE_BLOB, MYSQLI_TYPE_STRING, MYSQLI_TYPE_VAR_STRING, MYSQLI_TYPE_BIT, MYSQLI_TYPE_GEOMETRY];
        $conteo = [];

        foreach ($tablas as $tabla) {
            $crear = $db->query("SHOW CREATE TABLE " . $id($tabla))->fetch_row()[1];
            $escribir("-- ----------------------------------------\n-- Tabla $tabla\n-- ----------------------------------------\n");
            $escribir("DROP TABLE IF EXISTS " . $id($tabla) . ";\n$crear;\n\n");

            $resultado = $db->query("SELECT * FROM " . $id($tabla), MYSQLI_USE_RESULT);
            $campos = $resultado->fetch_fields();
            $columnas = implode(", ", array_map(fn($c) => $id($c->name), $campos));
            $esBinario = array_map(fn($c) => $c->charsetnr === 63 && in_array($c->type, $binarios, true), $campos);

            $lote = [];
            $conteo[$tabla] = 0;
            $volcar = function () use (&$lote, $escribir, $id, $tabla, $columnas) {
                if (!$lote) return;
                $escribir("INSERT INTO " . $id($tabla) . " ($columnas) VALUES\n" . implode(",\n", $lote) . ";\n");
                $lote = [];
            };

            while ($fila = $resultado->fetch_row()) {
                $valores = [];
                foreach ($fila as $i => $valor) {
                    if ($valor === null) $valores[] = "NULL";
                    elseif ($esBinario[$i]) $valores[] = $valor === "" ? "''" : "0x" . bin2hex($valor);
                    else $valores[] = "'" . $db->real_escape_string($valor) . "'";
                }
                $lote[] = "(" . implode(", ", $valores) . ")";
                $conteo[$tabla]++;
                if (count($lote) >= 200) $volcar();
            }
            $volcar();
            $resultado->free();
            $escribir("\n");
        }

        foreach ($vistas as $vista) {
            $crear = $db->query("SHOW CREATE VIEW " . $id($vista))->fetch_row()[1];
            // Sin DEFINER para que se pueda restaurar con otro usuario
            $crear = preg_replace('/\sDEFINER=`[^`]*`@`[^`]*`/', "", $crear);
            $escribir("-- Vista $vista\nDROP VIEW IF EXISTS " . $id($vista) . ";\n$crear;\n\n");
        }

        $db->query("COMMIT");
        $escribir("SET FOREIGN_KEY_CHECKS = 1;\nSET UNIQUE_CHECKS = 1;\n\n-- FIN DEL RESPALDO (" . count($tablas) . " tablas, " . array_sum($conteo) . " filas)\n");
        fclose($f);
    } catch (Throwable $e) {
        fclose($f);
        @unlink($temporal);
        throw $e;
    }

    // Verificacion: el archivo debe terminar con la marca de fin (no quedo cortado)
    $final = file_get_contents($temporal, false, null, max(0, filesize($temporal) - 200));
    if (!str_contains($final, "-- FIN DEL RESPALDO")) {
        @unlink($temporal);
        throw new RuntimeException("El respaldo quedo incompleto");
    }
    if (!rename($temporal, $archivo)) {
        throw new RuntimeException("No se pudo renombrar $temporal a $archivo");
    }
    return $conteo;
}

try {
    $db = conectarDB($schema);
} catch (Throwable $e) {
    fallo("No se pudo conectar a la base de datos: " . $e->getMessage());
}

$filas = function (string $sql, array $params = []) use ($db): array {
    $stmt = $db->prepare($sql);
    if ($params) $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
};

$existeTabla = fn(string $tabla) => (bool) $filas(
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$schema, $tabla]
);
$columna = fn(string $tabla, string $col) => $filas(
    "SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?", [$schema, $tabla, $col]
)[0]["COLUMN_TYPE"] ?? null;
$motor = fn(string $tabla) => strtolower((string) ($filas(
    "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$schema, $tabla]
)[0]["ENGINE"] ?? ""));

echo "Migracion cliente_empleado | base de datos: $schema@{$_ENV["DB_HOST"]} | modo: " . ($soloRespaldo ? "SOLO RESPALDO" : ($ejecutar ? "EJECUTAR" : "SIMULACION")) . PHP_EOL;

// =====================================================================
// 1. ESTADO ACTUAL
// =====================================================================
titulo("1. Estado actual");

foreach (["clientes", "empleados", "horas_extras"] as $tabla) {
    if (!$existeTabla($tabla)) fallo("No existe la tabla $tabla en $schema");
}

$tablaExiste = $existeTabla("cliente_empleado");
$tieneTipo = $columna("clientes", "tipo") !== null;
$tiposConfig = array_keys(Config::get("clientes.tarifas", []));

info("Tabla cliente_empleado: " . ($tablaExiste ? "ya existe" : "no existe (se creara)"));
info("Columna clientes.tipo: " . ($tieneTipo ? "existe" : "ya no existe"));
info("Tipos configurados en parametros: " . ($tiposConfig ? implode(", ", $tiposConfig) : "(ninguno)"));

if (!$tiposConfig) fallo("No hay tipos en clientes.tarifas (config/parametros*.json)");

// Nombres viejos de tipo -> nuevos. Los bilingues se cobran como clientes viejos (docx Noviembre 2026)
const MAPEO_TIPOS = ["viejo" => "tarifa_1", "nuevo" => "tarifa_2", "bilingue" => "tarifa_1"];

// Tipo por cliente segun "Desglose de clientes activos por tarifa Noviembre 2026.docx"
const ARCHIVO_TIPOS_CLIENTE = __DIR__ . "/migraciones/clientes_por_tarifa_2026-11.php";
// Empleados asignados a cada cliente segun el Excel de ClickUp "CLIENTES CONTRATADOS"
const ARCHIVO_RELACIONES = __DIR__ . "/migraciones/relaciones_clientes_2026-11.php";

// Normaliza para comparar nombres: minusculas, sin tildes ni signos, espacios simples
function normalizar(string $texto): string
{
    $texto = mb_strtolower($texto, "UTF-8");
    $texto = strtr($texto, ["á" => "a", "é" => "e", "í" => "i", "ó" => "o", "ú" => "u", "ü" => "u", "ñ" => "n", "à" => "a", "è" => "e", "ì" => "i", "ò" => "o", "ù" => "u"]);
    return trim(preg_replace('/\s+/', " ", preg_replace('/[^a-z0-9]+/', " ", $texto)));
}

/**
 * Busca un nombre entre clientes o empleados ([id => nombre normalizado]).
 * Devuelve [ids, "exacta"|"parcial"|"aproximada"|"ambigua"|null].
 * exacta: mismo nombre | parcial: todas sus palabras estan en el registro | aproximada: con errores de tipeo.
 * Solo se acepta si hay UN candidato en ese nivel.
 */
function buscarNombre(string $nombre, array $clientesNorm): array
{
    $palabras = explode(" ", normalizar($nombre));
    $buscado = implode(" ", $palabras);

    $exactos = array_keys(array_filter($clientesNorm, fn($c) => $c === $buscado));
    if (count($exactos) === 1) return [$exactos, "exacta"];

    $contiene = fn(array $tokensCliente, callable $igual) => array_reduce(
        $palabras,
        fn($ok, $p) => $ok && (bool) array_filter($tokensCliente, fn($t) => $igual($p, $t)),
        true
    );

    $parciales = array_keys(array_filter($clientesNorm, fn($c) => $contiene(explode(" ", $c), fn($p, $t) => $p === $t)));
    if (count($parciales) === 1) return [$parciales, "parcial"];

    // Errores de tipeo: hasta 1 letra en palabras cortas, 2 en palabras de 5+ letras (Wulff/Wulf, Nuñes/Nuñez)
    $aproximados = array_keys(array_filter($clientesNorm, fn($c) => $contiene(
        explode(" ", $c),
        fn($p, $t) => levenshtein($p, $t) <= (strlen($p) >= 5 ? 2 : 1)
    )));
    if (count($aproximados) === 1) return [$aproximados, "aproximada"];

    return [[], count($parciales) > 1 || count($aproximados) > 1 ? "ambigua" : null];
}

// =====================================================================
// 0. RESPALDO COMPLETO (antes de tocar cualquier cosa)
// =====================================================================
titulo("Respaldo completo de la base de datos");

$archivoRespaldo = __DIR__ . "/respaldos/{$schema}_" . date("Ymd_His") . ".sql";

if (!$ejecutar && !$soloRespaldo) {
    info("SIMULACION: con --ejecutar se generara primero " . $archivoRespaldo);
} else {
    info("Generando $archivoRespaldo ...");
    try {
        $inicio = microtime(true);
        $conteo = respaldarBase($db, $schema, $archivoRespaldo);
    } catch (Throwable $e) {
        fallo("El respaldo fallo, NO se modifico nada: " . $e->getMessage());
    }
    foreach ($conteo as $tabla => $n) info(str_pad($tabla, 35) . " $n filas");
    info(sprintf("-> Respaldo OK: %d tablas, %d filas, %.1f MB en %.1f s",
        count($conteo), array_sum($conteo), filesize($archivoRespaldo) / 1048576, microtime(true) - $inicio));

    if ($soloRespaldo) {
        echo PHP_EOL . "Solo respaldo (--solo-respaldo): no se modifico nada." . PHP_EOL;
        exit(0);
    }
}

// =====================================================================
// 1b. TIPOS DE TARIFA EN PARAMETROS (viejo/nuevo/bilingue -> tarifa_1..3)
// =====================================================================
titulo("1b. Tipos de tarifa en parametros");

$archivoParametros = dirname(__DIR__) . "/config/parametros.json";
$porDefecto = json_decode(file_get_contents(dirname(__DIR__) . "/config/parametros.default.json"), true, 512, JSON_THROW_ON_ERROR);
$tarifasObjetivo = $porDefecto["clientes"]["tarifas"];

if (!is_file($archivoParametros)) {
    info("No existe config/parametros.json: ya se usan los tipos por defecto (" . implode(", ", array_keys($tarifasObjetivo)) . ")");
} else {
    $parametros = Config::todo();
    $actuales = $parametros["clientes"]["tarifas"] ?? [];

    // Las tarifas oficiales (docx Noviembre 2026) + cualquier tipo propio que no sea viejo/nuevo/bilingue
    $extra = array_diff_key($actuales, $tarifasObjetivo, MAPEO_TIPOS);
    $nuevasTarifas = $tarifasObjetivo + $extra;

    $defecto = strtolower((string) ($parametros["clientes"]["tipo_defecto"] ?? ""));
    $defecto = MAPEO_TIPOS[$defecto] ?? $defecto;
    if (!isset($nuevasTarifas[$defecto])) $defecto = "tarifa_1";

    $cambia = $nuevasTarifas !== $actuales || $defecto !== ($parametros["clientes"]["tipo_defecto"] ?? null);
    info("Tipos actuales: " . (implode(", ", array_keys($actuales)) ?: "(ninguno)"));
    info("Tipos nuevos:   " . implode(", ", array_keys($nuevasTarifas)) . " | por defecto: $defecto");
    foreach ($extra as $tipo => $_) aviso("Se conserva el tipo propio \"$tipo\"");
    foreach (array_intersect_key($actuales, $tarifasObjetivo) as $tipo => $valores) {
        if ($valores != $tarifasObjetivo[$tipo]) aviso("Las tarifas de \"$tipo\" se reemplazan por las oficiales");
    }

    if (!$cambia) {
        info("Nada que hacer, ya estan configurados");
    } elseif ($ejecutar) {
        $parametros["clientes"]["tarifas"] = $nuevasTarifas;
        $parametros["clientes"]["tipo_defecto"] = $defecto;
        Config::guardar($parametros); // respalda la version anterior en config/historial
        info("-> config/parametros.json actualizado (version anterior en config/historial)");
    }
    $tiposConfig = array_keys($nuevasTarifas);
}

// =====================================================================
// 2. CREAR TABLA
// =====================================================================
titulo("2. Crear tabla cliente_empleado");

if ($tablaExiste) {
    info("Nada que hacer, ya existe");
} else {
    // Los IDs de la relacion deben tener exactamente el mismo tipo que las llaves referenciadas
    $tipoIdCliente = $columna("clientes", "id") ?? "int";
    $tipoIdEmpleado = $columna("empleados", "id") ?? "int";
    $conLlaves = $motor("clientes") === "innodb" && $motor("empleados") === "innodb";

    $sql = "CREATE TABLE cliente_empleado (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id $tipoIdCliente NOT NULL,
    empleado_id $tipoIdEmpleado NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cliente_empleado (cliente_id, empleado_id),
    KEY idx_cliente_empleado_empleado (empleado_id)" . ($conLlaves ? ",
    CONSTRAINT fk_cliente_empleado_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE CASCADE,
    CONSTRAINT fk_cliente_empleado_empleado FOREIGN KEY (empleado_id) REFERENCES empleados (id) ON DELETE CASCADE" : "") . "
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conLlaves) {
        aviso("clientes/empleados no son InnoDB: se crea sin llaves foraneas (la aplicacion borra las relaciones por codigo)");
    }
    info(str_replace("\n", "\n  ", $sql));

    if ($ejecutar) {
        $db->query($sql);
        info("-> Tabla creada");
    }
}

// =====================================================================
// 2b. TIPO POR CLIENTE SEGUN EL DOCX DE TARIFAS
// =====================================================================
titulo("2b. Tipo por cliente (Desglose de clientes activos por tarifa Noviembre 2026)");

$listaTipos = require ARCHIVO_TIPOS_CLIENTE;
$clientesNorm = [];
$nombresCliente = [];
foreach ($filas("SELECT id, nombre, apellido FROM clientes ORDER BY id") as $c) {
    $clientesNorm[(int) $c["id"]] = normalizar($c["nombre"] . " " . $c["apellido"]);
    $nombresCliente[(int) $c["id"]] = mb_strtoupper(clave($c["nombre"] . " " . $c["apellido"]));
}

$tipoPorCliente = [];   // cliente_id => tipo
$conflictos = [];
foreach ($listaTipos as $tipo => $entradas) {
    if (!in_array($tipo, $tiposConfig, true)) {
        aviso("El tipo \"$tipo\" de la lista no esta configurado: se omiten sus " . count($entradas) . " clientes");
        continue;
    }
    foreach ($entradas as $entrada) {
        $encontrados = [];
        $detalle = [];
        foreach (array_map("trim", explode("/", $entrada)) as $alternativa) {
            [$ids, $nivel] = buscarNombre($alternativa, $clientesNorm);
            foreach ($ids as $id) {
                $encontrados[$id] = true;
                if ($nivel !== "exacta") $detalle[] = "\"$alternativa\" ~ {$nombresCliente[$id]} (coincidencia $nivel)";
            }
            if ($nivel === "ambigua") $detalle[] = "\"$alternativa\" coincide con varios clientes: no se usa";
        }
        if (!$encontrados) {
            aviso("No se encontro en clientes: \"$entrada\" ($tipo). Asignalo a mano desde la pantalla del cliente");
            continue;
        }
        foreach ($detalle as $d) aviso("Revisar: $d");
        foreach (array_keys($encontrados) as $id) {
            if (isset($tipoPorCliente[$id]) && $tipoPorCliente[$id] !== $tipo) $conflictos[$id] = true;
            $tipoPorCliente[$id] = $tipo;
        }
    }
}
foreach (array_keys($conflictos) as $id) {
    aviso("{$nombresCliente[$id]} aparece en varios grupos del docx: no se le asigna tipo automaticamente");
    unset($tipoPorCliente[$id]);
}
$porTipo = array_count_values($tipoPorCliente);
ksort($porTipo);
info(count($tipoPorCliente) . " clientes identificados: " . implode(", ", array_map(fn($t, $n) => "$t=$n", array_keys($porTipo), $porTipo)));
foreach (array_diff_key($nombresCliente, $tipoPorCliente) as $id => $nombre) {
    info("    sin grupo en el docx: $nombre");
}

// =====================================================================
// 3. CALCULAR RELACIONES DESDE LAS HORAS CARGADAS
// =====================================================================
titulo("3. Relaciones a partir de horas_extras");

// Relaciones que ya existian con nombres viejos de tipo
if ($tablaExiste) {
    foreach (MAPEO_TIPOS as $viejo => $nuevo) {
        $n = (int) $filas("SELECT COUNT(*) AS n FROM cliente_empleado WHERE tipo = ?", [$viejo])[0]["n"];
        if (!$n) continue;
        info("$n relacion(es) existentes con tipo \"$viejo\" pasan a \"$nuevo\"");
        if ($ejecutar) {
            $stmt = $db->prepare("UPDATE cliente_empleado SET tipo = ? WHERE tipo = ?");
            $stmt->bind_param("ss", $nuevo, $viejo);
            $stmt->execute();
        }
    }
}

if (!$tieneTipo) {
    info("clientes.tipo ya no existe: no hay datos que migrar");
} else {
    // Indices por nombre completo; con nombres repetidos gana el ID mas bajo (igual que la aplicacion)
    $indexar = function (array $registros, string $etiqueta) {
        $indice = [];
        foreach ($registros as $r) {
            $k = clave($r["nombre"] . " " . $r["apellido"]);
            if (isset($indice[$k])) {
                aviso("$etiqueta con nombre repetido \"$k\" (IDs {$indice[$k]["id"]} y {$r["id"]}); se usa el ID {$indice[$k]["id"]}");
                continue;
            }
            $indice[$k] = $r;
        }
        return $indice;
    };

    $clientes = $indexar($filas("SELECT id, nombre, apellido, tipo FROM clientes ORDER BY id"), "Cliente");
    $empleados = $indexar($filas("SELECT id, nombre, apellido FROM empleados ORDER BY id"), "Empleado");
    $parejas = $filas(
        "SELECT cliente, empleado, COUNT(*) AS horas FROM horas_extras
         WHERE LOWER(TRIM(cliente)) <> 'administrativo' GROUP BY cliente, empleado"
    );

    $relaciones = [];       // "clienteId:empleadoId" => [cliente_id, empleado_id, tipo, nombres]
    $sinCliente = [];
    $sinEmpleado = [];
    $tipoInvalido = [];
    $clientesConHoras = [];

    foreach ($parejas as $p) {
        $c = $clientes[clave($p["cliente"])] ?? null;
        $e = $empleados[clave($p["empleado"])] ?? null;

        if (!$c) { $sinCliente[clave($p["cliente"])] = ($sinCliente[clave($p["cliente"])] ?? 0) + $p["horas"]; continue; }
        $clientesConHoras[$c["id"]] = true;
        if (!$e) { $sinEmpleado[clave($p["empleado"])] = ($sinEmpleado[clave($p["empleado"])] ?? 0) + $p["horas"]; continue; }

        // Manda el docx; si el cliente no esta en el docx se usa su tipo anterior convertido
        $tipo = strtolower(trim((string) $c["tipo"]));
        $tipo = $tipoPorCliente[(int) $c["id"]] ?? (MAPEO_TIPOS[$tipo] ?? $tipo);
        if (!in_array($tipo, $tiposConfig, true)) {
            $tipoInvalido[$c["id"]] = clave($c["nombre"] . " " . $c["apellido"]) . " (tipo \"" . $c["tipo"] . "\")";
            continue;
        }
        $relaciones[$c["id"] . ":" . $e["id"]] = [
            "cliente_id" => (int) $c["id"],
            "empleado_id" => (int) $e["id"],
            "tipo" => $tipo,
            "texto" => mb_strtoupper(clave($c["nombre"] . " " . $c["apellido"]) . " -> " . clave($e["nombre"] . " " . $e["apellido"])) . " = $tipo",
        ];
    }

    $existentes = $tablaExiste ? array_flip(array_map(
        fn($r) => $r["cliente_id"] . ":" . $r["empleado_id"],
        $filas("SELECT cliente_id, empleado_id FROM cliente_empleado")
    )) : [];
    $nuevas = array_diff_key($relaciones, $existentes);

    info(count($parejas) . " parejas cliente-empleado distintas en horas_extras");
    info(count($relaciones) . " relaciones validas, " . count($nuevas) . " nuevas por insertar, " . (count($relaciones) - count($nuevas)) . " ya existian");
    foreach ($nuevas as $r) info("  + " . $r["texto"]);

    foreach ($sinCliente as $nombre => $n) aviso("Cliente \"$nombre\" en $n hora(s) no existe en clientes: se omite");
    foreach ($sinEmpleado as $nombre => $n) aviso("Empleado \"$nombre\" en $n hora(s) no existe en empleados: se omite (se cobrara con el tipo por defecto)");
    foreach ($tipoInvalido as $texto) aviso("Cliente $texto no es un tipo configurado: sus relaciones se omiten (se cobraran con el tipo por defecto)");

    $sinHoras = array_filter($clientes, fn($c) => !isset($clientesConHoras[$c["id"]]));
    if ($sinHoras) {
        aviso(count($sinHoras) . " cliente(s) sin horas cargadas quedan sin relaciones; asigna sus empleados desde la pantalla del cliente:");
        foreach ($sinHoras as $c) info("    - " . mb_strtoupper(clave($c["nombre"] . " " . $c["apellido"])) . " (tenia tipo \"" . $c["tipo"] . "\")");
    }

    if ($ejecutar && $nuevas) {
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("INSERT IGNORE INTO cliente_empleado (cliente_id, empleado_id, tipo) VALUES (?, ?, ?)");
            foreach ($nuevas as $r) {
                $stmt->bind_param("iis", $r["cliente_id"], $r["empleado_id"], $r["tipo"]);
                $stmt->execute();
            }
            $db->commit();
            info("-> " . count($nuevas) . " relaciones insertadas");
        } catch (Throwable $e) {
            $db->rollback();
            fallo("No se insertaron relaciones (se revirtio todo): " . $e->getMessage());
        }
    }
}

// =====================================================================
// 3b. APLICAR EL TIPO DEL DOCX A LAS RELACIONES YA EXISTENTES
// =====================================================================
titulo("3b. Tipo del docx en relaciones existentes");

if (!$tablaExiste && !$ejecutar) {
    info("La tabla aun no existe: las relaciones nuevas ya se crean con el tipo del docx (paso 3)");
} else {
    $cambios = [];
    foreach ($filas("SELECT cliente_id, tipo, COUNT(*) AS n FROM cliente_empleado GROUP BY cliente_id, tipo") as $r) {
        $nuevo = $tipoPorCliente[(int) $r["cliente_id"]] ?? null;
        if ($nuevo === null || $nuevo === $r["tipo"]) continue;
        $cambios[] = [(int) $r["cliente_id"], $nuevo];
        info(($nombresCliente[(int) $r["cliente_id"]] ?? "#" . $r["cliente_id"]) . ": {$r["n"]} relacion(es) \"{$r["tipo"]}\" -> \"$nuevo\"");
    }
    if (!$cambios) {
        info("Nada que cambiar");
    } elseif ($ejecutar) {
        $stmt = $db->prepare("UPDATE cliente_empleado SET tipo = ? WHERE cliente_id = ?");
        foreach ($cambios as [$clienteId, $nuevo]) {
            $stmt->bind_param("si", $nuevo, $clienteId);
            $stmt->execute();
        }
        info("-> Tipos actualizados en " . count(array_unique(array_column($cambios, 0))) . " cliente(s)");
    }
}

// =====================================================================
// 3c. RELACIONES SEGUN EL EXCEL "CLIENTES CONTRATADOS" (ClickUp)
// =====================================================================
titulo("3c. Relaciones del Excel CLIENTES CONTRATADOS");

// Tipo por defecto ya convertido (paso 1b); se usa si el cliente no esta en el docx
$tipoDefecto = $defecto ?? strtolower((string) Config::get("clientes.tipo_defecto", ""));
$tipoDefecto = MAPEO_TIPOS[$tipoDefecto] ?? $tipoDefecto;
if (!in_array($tipoDefecto, $tiposConfig, true)) $tipoDefecto = $tiposConfig[0];

$empleadosNorm = [];
$nombresEmpleado = [];
foreach ($filas("SELECT id, nombre, apellido FROM empleados ORDER BY id") as $e) {
    $empleadosNorm[(int) $e["id"]] = normalizar($e["nombre"] . " " . $e["apellido"]);
    $nombresEmpleado[(int) $e["id"]] = mb_strtoupper(clave($e["nombre"] . " " . $e["apellido"]));
}

// Busca probando cada alternativa separada por "/"; devuelve [id|null, avisos]
$resolver = function (string $entrada, array $norm, array $nombres): array {
    $avisos = [];
    foreach (array_map("trim", explode("/", $entrada)) as $alternativa) {
        [$ids, $nivel] = buscarNombre($alternativa, $norm);
        if ($nivel === "ambigua") $avisos[] = "\"$alternativa\" coincide con varios registros: no se usa";
        if (count($ids) === 1) {
            if ($nivel !== "exacta") $avisos[] = "\"$alternativa\" ~ {$nombres[$ids[0]]} (coincidencia $nivel)";
            return [$ids[0], $avisos];
        }
    }
    return [null, $avisos];
};

$existentesExcel = $existeTabla("cliente_empleado") ? array_flip(array_map(
    fn($r) => $r["cliente_id"] . ":" . $r["empleado_id"],
    $filas("SELECT cliente_id, empleado_id FROM cliente_empleado")
)) : [];

$porInsertar = [];
$yaExistian = 0;
foreach (require ARCHIVO_RELACIONES as $entradaCliente => $entradasEmpleado) {
    [$clienteId, $avisos] = $resolver($entradaCliente, $clientesNorm, $nombresCliente);
    foreach ($avisos as $a) aviso("Revisar cliente: $a");
    if ($clienteId === null) {
        aviso("No se encontro el cliente \"$entradaCliente\": se omiten sus " . count($entradasEmpleado) . " empleado(s)");
        continue;
    }
    $tipo = $tipoPorCliente[$clienteId] ?? $tipoDefecto;

    foreach ($entradasEmpleado as $entradaEmpleado) {
        [$empleadoId, $avisos] = $resolver($entradaEmpleado, $empleadosNorm, $nombresEmpleado);
        foreach ($avisos as $a) aviso("Revisar empleado ({$nombresCliente[$clienteId]}): $a");
        if ($empleadoId === null) {
            aviso("No se encontro el empleado \"$entradaEmpleado\" de {$nombresCliente[$clienteId]}: asignalo a mano desde la pantalla del cliente");
            continue;
        }
        $k = "$clienteId:$empleadoId";
        if (isset($existentesExcel[$k]) || isset($porInsertar[$k])) { $yaExistian++; continue; }
        $porInsertar[$k] = [$clienteId, $empleadoId, $tipo];
        info("  + {$nombresCliente[$clienteId]} -> {$nombresEmpleado[$empleadoId]} = $tipo");
    }
}
info(count($porInsertar) . " relaciones nuevas por insertar, $yaExistian ya existian (conservan su tipo)");

if ($ejecutar && $porInsertar) {
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO cliente_empleado (cliente_id, empleado_id, tipo) VALUES (?, ?, ?)");
        foreach ($porInsertar as [$clienteId, $empleadoId, $tipo]) {
            $stmt->bind_param("iis", $clienteId, $empleadoId, $tipo);
            $stmt->execute();
        }
        $db->commit();
        info("-> " . count($porInsertar) . " relaciones insertadas");
    } catch (Throwable $e) {
        $db->rollback();
        fallo("No se insertaron las relaciones del Excel (se revirtio este paso): " . $e->getMessage());
    }
}

// =====================================================================
// 4. RESPALDAR Y ELIMINAR clientes.tipo
// =====================================================================
titulo("4. Eliminar clientes.tipo");

if (!$tieneTipo) {
    info("Nada que hacer, la columna ya no existe");
} elseif ($conservarColumna) {
    info("Se conserva (--conservar-columna). Vuelve a correr sin esa opcion para eliminarla.");
} else {
    $respaldo = "clientes_tipo_respaldo_" . date("Ymd_His");
    info("Se respalda id, nombre, apellido, tipo en la tabla $respaldo");
    info("ALTER TABLE clientes DROP COLUMN tipo");

    if ($ejecutar) {
        $db->query("CREATE TABLE `$respaldo` AS SELECT id, nombre, apellido, tipo FROM clientes");
        $db->query("ALTER TABLE clientes DROP COLUMN tipo");
        info("-> Respaldo creado y columna eliminada");
    }
}

// =====================================================================
titulo("Resultado");
if ($ejecutar) {
    $total = $existeTabla("cliente_empleado") ? (int) $filas("SELECT COUNT(*) AS n FROM cliente_empleado")[0]["n"] : 0;
    info("Listo. cliente_empleado tiene $total relaciones.");
    info("Respaldo previo: $archivoRespaldo");
} else {
    info("SIMULACION: no se modifico nada. Respalda la base de datos y corre de nuevo con --ejecutar.");
}
echo PHP_EOL;
