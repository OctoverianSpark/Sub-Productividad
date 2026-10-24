<?php

namespace MVC;

use RuntimeException;

/**
 * Parametros de negocio editables (tarifas, permisos, paginacion...).
 * - config/parametros.default.json: valores por defecto (versionados en git).
 * - config/parametros.json: valores editados desde la web (ignorado por git). Si existe, tiene prioridad.
 * Los secretos NO van aqui: van en .env.
 */
class Config
{
    private const ARCHIVO = __DIR__ . "/config/parametros.json";
    private const ARCHIVO_DEFECTO = __DIR__ . "/config/parametros.default.json";
    private const CARPETA_HISTORIAL = __DIR__ . "/config/historial";
    private const MAX_HISTORIAL = 30;

    private static ?array $valores = null;

    // Obtiene un valor usando notacion de puntos: Config::get("nomina.hogar.valor_hora")
    public static function get(string $clave, mixed $defecto = null): mixed
    {
        $valor = self::todo();

        foreach (explode(".", $clave) as $parte) {
            if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                return $defecto;
            }
            $valor = $valor[$parte];
        }
        return $valor;
    }

    public static function todo(): array
    {
        if (self::$valores === null) {
            $valores = self::leer(self::ARCHIVO_DEFECTO);
            if (is_file(self::ARCHIVO)) {
                // Lo editado manda; las claves nuevas que solo existan en el default se conservan
                $valores = self::combinar($valores, self::leer(self::ARCHIVO));
            }
            self::$valores = $valores;
        }
        return self::$valores;
    }

    // Secciones cuyas claves las define el usuario: se reemplazan completas (si se borra un tipo, no reaparece)
    private const REEMPLAZO_COMPLETO = ["clientes.tarifas", "roles.disponibles"];

    // Combina objetos clave por clave; las listas (ej. tabla de salarios) se reemplazan completas
    private static function combinar(array $base, array $encima, string $ruta = ""): array
    {
        foreach ($encima as $clave => $valor) {
            $rutaClave = ltrim("$ruta.$clave", ".");
            $esObjeto = is_array($valor) && !array_is_list($valor);
            $baseEsObjeto = isset($base[$clave]) && is_array($base[$clave]) && !array_is_list($base[$clave]);

            $base[$clave] = ($esObjeto && $baseEsObjeto && !in_array($rutaClave, self::REEMPLAZO_COMPLETO, true))
                ? self::combinar($base[$clave], $valor, $rutaClave)
                : $valor;
        }
        return $base;
    }

    /**
     * Guarda todos los parametros. Antes respalda la version actual en config/historial
     * y escribe en un archivo temporal que luego reemplaza al original (nunca queda a medias).
     */
    public static function guardar(array $valores): void
    {
        $json = json_encode($valores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        if (is_file(self::ARCHIVO)) {
            self::respaldar();
        }

        $temporal = self::ARCHIVO . ".tmp";
        if (@file_put_contents($temporal, $json . PHP_EOL, LOCK_EX) === false || !@rename($temporal, self::ARCHIVO)) {
            @unlink($temporal);
            throw new RuntimeException("No se pudo escribir " . self::ARCHIVO . ". Revisa que el servidor tenga permiso de escritura en la carpeta config/.");
        }

        self::$valores = $valores;
    }

    private static function respaldar(): void
    {
        if (!is_dir(self::CARPETA_HISTORIAL) && !@mkdir(self::CARPETA_HISTORIAL, 0750, true)) {
            throw new RuntimeException("No se pudo crear la carpeta " . self::CARPETA_HISTORIAL);
        }
        copy(self::ARCHIVO, self::CARPETA_HISTORIAL . "/parametros-" . date("Ymd-His") . ".json");

        // Se conservan solo los respaldos mas recientes
        $respaldos = glob(self::CARPETA_HISTORIAL . "/parametros-*.json") ?: [];
        sort($respaldos);
        foreach (array_slice($respaldos, 0, max(0, count($respaldos) - self::MAX_HISTORIAL)) as $viejo) {
            @unlink($viejo);
        }
    }

    private static function leer(string $archivo): array
    {
        $contenido = @file_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException("No se pudo leer $archivo");
        }
        try {
            return json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException(basename($archivo) . " tiene un error de formato: " . $e->getMessage());
        }
    }
}
