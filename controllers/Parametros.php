<?php

namespace Controllers;

use Models\Clientes;
use Models\Logs;
use MVC\Config;
use MVC\Router;
use RuntimeException;

/**
 * Pantalla para editar config/parametros.json desde la web (tarifas, recargos, permisos...).
 */
class Parametros
{
    // Texto que se muestra para cada permiso de "roles.permisos"
    public const PERMISOS = [
        "gestionar_horas" => "Ver, cargar y editar horas",
        "eliminar_horas" => "Eliminar horas",
        "exportar_horas" => "Exportar reporte de horas",
        "ver_logs" => "Ver registro de eventos",
        "configuracion" => "Configuracion (empleados y clientes)",
        "exportar_maestros" => "Exportar empleados y clientes (incluye salarios)",
        "auditar" => "Auditar y aprobar horas",
        "usuarios" => "Gestionar usuarios",
        "parametros" => "Editar parametros (esta pantalla)",
    ];

    // Permisos que GOD siempre conserva, para que nadie quede sin acceso a la administracion
    private const PERMISOS_FIJOS_GOD = ["configuracion", "usuarios", "parametros"];

    private const RECARGOS_COLOMBIA = [
        "recargo_diurna_extra" => "Hora extra diurna",
        "recargo_nocturna_extra" => "Hora extra nocturna",
        "recargo_dominical" => "Hora ordinaria dominical/festiva",
        "recargo_diurna_extra_dominical" => "Hora extra diurna dominical/festiva",
        "recargo_nocturna_extra_dominical" => "Hora extra nocturna dominical/festiva",
    ];

    private const TARIFAS_CLIENTE = [
        "diurna" => "Extra diurna",
        "nocturna" => "Extra nocturna",
        "diurna_dominical" => "Diurna dominical",
        "nocturna_dominical" => "Nocturna dominical",
    ];

    public static function index(Router $router)
    {
        $parametros = Config::todo();
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            [$parametros, $errores] = self::desdeFormulario($_POST, Config::todo());

            if (empty($errores)) {
                try {
                    Config::guardar($parametros);
                    Logs::registrar("Parametros actualizados", "El usuario " . $_SESSION["name"] . " actualizo los parametros del sistema (tarifas, permisos y configuracion general)");
                    redirigir("/settings/parametros?resultado=2");
                } catch (RuntimeException $e) {
                    error_log($e->getMessage());
                    $errores[] = $e->getMessage();
                }
            }
        }

        $router->render("settings/parametros/index", [
            "p" => $parametros,
            "errores" => $errores,
            "roles" => array_keys($parametros["roles"]["disponibles"] ?? []),
            "permisos" => self::PERMISOS,
            "recargos" => self::RECARGOS_COLOMBIA,
            "tarifasCliente" => self::TARIFAS_CLIENTE,
        ]);
    }

    /**
     * Convierte el POST en el arreglo de parametros. Parte de los valores actuales para no
     * perder claves que no se editan en pantalla (zona horaria, nombres de roles...).
     * @return array{0: array, 1: string[]}
     */
    private static function desdeFormulario(array $post, array $actual): array
    {
        $errores = [];
        $p = $actual;
        $roles = array_keys($actual["roles"]["disponibles"] ?? []);

        // ---- General
        $g = (array) ($post["general"] ?? []);
        $p["app"]["sesion_inactividad_minutos"] = (int) self::numero($g["sesion_inactividad_minutos"] ?? null, "Minutos de inactividad", $errores, 5, 1440);

        $permitidos = array_values(array_unique(array_filter(array_map("intval", preg_split('/[\s,;]+/', (string) ($g["por_pagina_permitidos"] ?? ""))), fn($n) => $n > 0 && $n <= 500)));
        sort($permitidos);
        if (!$permitidos) {
            $errores[] = "Registros por pagina: indica al menos una opcion (ej: 20, 50, 100)";
        }
        $porDefecto = (int) ($g["por_pagina_defecto"] ?? 0);
        if ($permitidos && !in_array($porDefecto, $permitidos, true)) {
            $errores[] = "Registros por pagina: el valor por defecto debe estar entre las opciones permitidas";
        }
        $p["paginacion"] = ["por_pagina_defecto" => $porDefecto, "por_pagina_permitidos" => $permitidos];

        // ---- Nomina
        $n = (array) ($post["nomina"] ?? []);
        $p["nomina"]["horas_mes"] = self::numero($n["horas_mes"] ?? null, "Horas laborales al mes", $errores, 1, 744);

        $col = (array) ($n["colombia_oficina"] ?? []);
        $p["nomina"]["colombia_oficina"]["moneda"] = self::texto($col["moneda"] ?? "", "Moneda Colombia oficina", $errores);
        foreach (self::RECARGOS_COLOMBIA as $clave => $nombre) {
            // En pantalla se edita como porcentaje (25 = 25%) y se guarda como fraccion (0.25)
            $p["nomina"]["colombia_oficina"][$clave] = round(self::numero($col[$clave] ?? null, "Recargo $nombre", $errores, 0, 1000) / 100, 6);
        }

        $hog = (array) ($n["hogar"] ?? []);
        $p["nomina"]["hogar"]["moneda"] = self::texto($hog["moneda"] ?? "", "Moneda hogar", $errores);
        $p["nomina"]["hogar"]["valor_hora"] = self::numero($hog["valor_hora"] ?? null, "Valor hora hogar", $errores);

        $ven = (array) ($n["venezuela_oficina"] ?? []);
        $p["nomina"]["venezuela_oficina"]["moneda"] = self::texto($ven["moneda"] ?? "", "Moneda Venezuela oficina", $errores);
        $tabla = [];
        foreach ((array) ($ven["tabla"] ?? []) as $fila) {
            $salario = trim((string) ($fila["salario"] ?? ""));
            $valor = trim((string) ($fila["valor_hora_extra"] ?? ""));
            if ($salario === "" && $valor === "") continue; // fila vacia
            $s = self::numero($salario, "Tabla Venezuela: salario", $errores, 0.01);
            $v = self::numero($valor, "Tabla Venezuela: valor hora extra", $errores, 0.01);
            if (isset($tabla[(string) $s])) {
                $errores[] = "Tabla Venezuela: el salario $s esta repetido";
            }
            $tabla[(string) $s] = ["salario" => $s, "valor_hora_extra" => $v];
        }
        if (!$tabla) {
            $errores[] = "Tabla Venezuela: debe tener al menos una fila";
        }
        ksort($tabla, SORT_NUMERIC);
        $p["nomina"]["venezuela_oficina"]["tabla_valor_hora_extra"] = array_values($tabla);

        // ---- Clientes
        $c = (array) ($post["clientes"] ?? []);
        $p["clientes"]["moneda"] = self::texto($c["moneda"] ?? "", "Moneda clientes", $errores);
        $p["clientes"]["logistica"] = [
            "cena" => self::numero($c["logistica"]["cena"] ?? null, "Logistica: cena", $errores),
            "taxi" => self::numero($c["logistica"]["taxi"] ?? null, "Logistica: taxi", $errores),
        ];

        $tarifas = [];
        foreach ((array) ($post["tipos"] ?? []) as $fila) {
            $nombre = strtolower(trim((string) ($fila["nombre"] ?? "")));
            if ($nombre === "") continue;
            if (!preg_match('/^[a-z0-9_]{1,30}$/', $nombre)) {
                $errores[] = "Tipo de cliente \"$nombre\": usa solo letras minusculas, numeros o _ (sin espacios ni tildes)";
                continue;
            }
            if (isset($tarifas[$nombre])) {
                $errores[] = "Tipo de cliente \"$nombre\" esta repetido";
            }
            foreach (self::TARIFAS_CLIENTE as $clave => $texto) {
                $tarifas[$nombre][$clave] = self::numero($fila[$clave] ?? null, "Tipo \"$nombre\": $texto", $errores);
            }
        }
        if (!$tarifas) {
            $errores[] = "Tipos de cliente: debe existir al menos uno";
        }
        $enUso = array_diff(Clientes::tiposEnUso(), array_keys($tarifas), [""]);
        if ($enUso) {
            $errores[] = "No puedes quitar el tipo de cliente \"" . implode("\", \"", $enUso) . "\" porque hay clientes registrados con ese tipo. Cambialos primero.";
        }
        $p["clientes"]["tarifas"] = $tarifas;

        // ---- Permisos
        $permisosPost = (array) ($post["permisos"] ?? []);
        $todos = (array) ($post["permisos_todos"] ?? []);
        foreach (array_keys(self::PERMISOS) as $permiso) {
            if (!empty($todos[$permiso])) {
                $p["roles"]["permisos"][$permiso] = ["*"];
                continue;
            }
            $elegidos = array_values(array_intersect($roles, (array) ($permisosPost[$permiso] ?? [])));
            if (in_array($permiso, self::PERMISOS_FIJOS_GOD, true) && !in_array("god", $elegidos, true)) {
                array_unshift($elegidos, "god");
            }
            $p["roles"]["permisos"][$permiso] = $elegidos;
        }
        $p["roles"]["cargan_sin_auditoria"] = array_values(array_intersect($roles, (array) ($post["sin_auditoria"] ?? [])));

        return [$p, $errores];
    }

    private static function numero($valor, string $campo, array &$errores, float $min = 0, ?float $max = null): float
    {
        $valor = str_replace(",", ".", trim((string) $valor));
        if ($valor === "" || !is_numeric($valor)) {
            $errores[] = "$campo: debe ser un numero";
            return 0;
        }
        $numero = (float) $valor;
        if ($numero < $min || ($max !== null && $numero > $max)) {
            $errores[] = "$campo: debe estar entre $min y " . ($max ?? "sin limite");
        }
        return $numero;
    }

    private static function texto($valor, string $campo, array &$errores): string
    {
        $valor = trim((string) $valor);
        if ($valor === "" || mb_strlen($valor) > 20) {
            $errores[] = "$campo: obligatorio, maximo 20 caracteres";
        }
        return $valor;
    }
}
