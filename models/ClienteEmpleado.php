<?php

namespace Models;

use MVC\Config;
use Throwable;

/**
 * Relacion cliente-empleado con su tipo de cobro (nuevo, viejo...).
 * Ej: PAMELA -> VALERIA = nuevo, PAMELA -> JOSE = viejo.
 * Las tarifas de cada tipo estan en config "clientes.tarifas".
 */
class ClienteEmpleado extends BaseModel
{
    protected static $tabla = "cliente_empleado";
    protected static $columnasDB = ["id", "cliente_id", "empleado_id", "tipo"];

    public $id, $cliente_id, $empleado_id, $tipo;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->cliente_id = $args["cliente_id"] ?? null;
        $this->empleado_id = $args["empleado_id"] ?? null;
        $this->tipo = $args["tipo"] ?? "";
    }

    // Tipos de cobro configurados (claves de clientes.tarifas)
    public static function tiposDisponibles(): array
    {
        return array_keys(Config::get("clientes.tarifas", []));
    }

    // Tipo que se usa cuando una hora no tiene relacion registrada
    public static function tipoPorDefecto(): string
    {
        $tipos = self::tiposDisponibles();
        $defecto = strtolower((string) Config::get("clientes.tipo_defecto", ""));
        return in_array($defecto, $tipos, true) ? $defecto : (string) ($tipos[0] ?? "");
    }

    // Relaciones de un cliente: [empleado_id => tipo]
    public static function deCliente(int $clienteId): array
    {
        $filas = self::ejecutar(
            "SELECT empleado_id, tipo FROM " . static::$tabla . " WHERE cliente_id = ?",
            [$clienteId]
        )->get_result()->fetch_all(MYSQLI_ASSOC);

        return array_column($filas, "tipo", "empleado_id");
    }

    // Todas las relaciones con el nombre del empleado, agrupadas por cliente: [cliente_id => [[empleado, tipo], ...]]
    public static function agrupadasPorCliente(): array
    {
        $filas = self::ejecutar(
            "SELECT ce.cliente_id, ce.tipo, CONCAT(e.nombre, ' ', e.apellido) AS empleado
             FROM " . static::$tabla . " ce
             JOIN empleados e ON e.id = ce.empleado_id
             ORDER BY e.nombre, e.apellido"
        )->get_result()->fetch_all(MYSQLI_ASSOC);

        $grupos = [];
        foreach ($filas as $fila) {
            $grupos[(int) $fila["cliente_id"]][] = ["empleado" => $fila["empleado"], "tipo" => $fila["tipo"]];
        }
        return $grupos;
    }

    // Indice para el calculo de cobro: ["clienteId:empleadoId" => tipo] (una sola consulta)
    public static function indice(): array
    {
        $filas = self::ejecutar("SELECT cliente_id, empleado_id, tipo FROM " . static::$tabla)
            ->get_result()->fetch_all(MYSQLI_ASSOC);

        $indice = [];
        foreach ($filas as $fila) {
            $indice[$fila["cliente_id"] . ":" . $fila["empleado_id"]] = $fila["tipo"];
        }
        return $indice;
    }

    // Tipos con al menos una relacion registrada (en minusculas)
    public static function tiposEnUso(): array
    {
        $filas = self::ejecutar("SELECT DISTINCT tipo FROM " . static::$tabla)->get_result()->fetch_all(MYSQLI_ASSOC);
        return array_values(array_unique(array_map(fn($f) => strtolower(trim((string) $f["tipo"])), $filas)));
    }

    /**
     * Normaliza y valida las filas del formulario del cliente.
     * @param array $filas [["empleado_id" => .., "tipo" => ..], ...]
     * @return array{0: array<int,string>, 1: string[]} [empleado_id => tipo] y errores
     */
    public static function validarFilas(array $filas): array
    {
        $errores = [];
        $relaciones = [];
        $tipos = self::tiposDisponibles();
        $empleados = [];
        foreach (Empleados::all() as $empleado) {
            $empleados[(int) $empleado->id] = $empleado;
        }

        foreach ($filas as $fila) {
            $empleadoId = (int) ($fila["empleado_id"] ?? 0);
            $tipo = strtolower(trim((string) ($fila["tipo"] ?? "")));
            if (!$empleadoId) continue; // fila vacia

            $empleado = $empleados[$empleadoId] ?? null;
            if (!$empleado) {
                $errores[] = "Empleados: uno de los empleados seleccionados no existe";
                continue;
            }
            $nombre = mb_strtoupper($empleado->nombreCompleto());
            if (isset($relaciones[$empleadoId])) {
                $errores[] = "Empleados: $nombre esta repetido";
                continue;
            }
            if (!in_array($tipo, $tipos, true)) {
                $errores[] = "Empleados: el tipo de $nombre debe ser uno de: " . implode(", ", $tipos);
                continue;
            }
            $relaciones[$empleadoId] = $tipo;
        }
        return [$relaciones, $errores];
    }

    // Reemplaza todas las relaciones de un cliente en una transaccion
    public static function reemplazarDeCliente(int $clienteId, array $relaciones): void
    {
        self::$db->begin_transaction();
        try {
            self::ejecutar("DELETE FROM " . static::$tabla . " WHERE cliente_id = ?", [$clienteId]);
            foreach ($relaciones as $empleadoId => $tipo) {
                (new static(["cliente_id" => $clienteId, "empleado_id" => (int) $empleadoId, "tipo" => $tipo]))->crear();
            }
            self::$db->commit();
        } catch (Throwable $e) {
            self::$db->rollback();
            throw $e;
        }
    }

    public static function eliminarDeCliente(int $clienteId): void
    {
        self::ejecutar("DELETE FROM " . static::$tabla . " WHERE cliente_id = ?", [$clienteId]);
    }

    public static function eliminarDeEmpleado(int $empleadoId): void
    {
        self::ejecutar("DELETE FROM " . static::$tabla . " WHERE empleado_id = ?", [$empleadoId]);
    }
}
