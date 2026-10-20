<?php

namespace MVC;

use RuntimeException;

/**
 * Parametros de negocio editables (tarifas, permisos, paginacion...).
 * Se leen de config/parametros.json una sola vez por peticion.
 * Los secretos NO van aqui: van en .env.
 */
class Config
{
    private const ARCHIVO = __DIR__ . "/config/parametros.json";

    private static ?array $valores = null;

    // Obtiene un valor usando notacion de puntos: Config::get("nomina.hogar.valor_hora")
    public static function get(string $clave, mixed $defecto = null): mixed
    {
        $valor = self::cargar();

        foreach (explode(".", $clave) as $parte) {
            if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                return $defecto;
            }
            $valor = $valor[$parte];
        }
        return $valor;
    }

    private static function cargar(): array
    {
        if (self::$valores === null) {
            $contenido = @file_get_contents(self::ARCHIVO);
            if ($contenido === false) {
                throw new RuntimeException("No se pudo leer " . self::ARCHIVO);
            }
            try {
                self::$valores = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new RuntimeException("parametros.json tiene un error de formato: " . $e->getMessage());
            }
        }
        return self::$valores;
    }
}
