<?php

namespace Models;

use MVC\Config;

class Time extends BaseModel
{
    protected static $tabla = "horas_extras";
    protected static $columnasDB = ["id", "empleado", "cliente", "inicio", "almuerzo", "final", "diurnas_ordinarias", "nocturnas_ordinarias", "diurnas_extras", "nocturnas_extras", "cena", "taxi", "comentarios", "auditar", "festivo"];
    protected static $conservarMayusculas = ["comentarios"];
    protected static $omitirVaciosAlActualizar = true;

    public $id, $empleado, $cliente, $inicio, $almuerzo, $final, $diurnas_ordinarias, $nocturnas_ordinarias, $diurnas_extras, $nocturnas_extras, $cena, $taxi, $comentarios, $auditar, $festivo;

    // Campos que el usuario puede enviar desde un formulario ("auditar" nunca viene del cliente)
    private const CAMPOS_FORMULARIO = ["empleado", "cliente", "inicio", "almuerzo", "final", "diurnas_ordinarias", "nocturnas_ordinarias", "diurnas_extras", "nocturnas_extras", "cena", "taxi", "comentarios", "festivo"];

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->empleado = $args["empleado"] ?? "";
        $this->cliente = $args["cliente"] ?? "";
        $this->inicio = $args["inicio"] ?? "";
        $this->almuerzo = $args["almuerzo"] ?? "";
        $this->final = $args["final"] ?? "";
        $this->diurnas_ordinarias = $args["diurnas_ordinarias"] ?? 0;
        $this->nocturnas_ordinarias = $args["nocturnas_ordinarias"] ?? 0;
        $this->diurnas_extras = $args["diurnas_extras"] ?? 0;
        $this->nocturnas_extras = $args["nocturnas_extras"] ?? 0;
        $this->cena = $args["cena"] ?? "no";
        $this->taxi = $args["taxi"] ?? "no";
        $this->comentarios = $args["comentarios"] ?? "";
        $this->auditar = $args["auditar"] ?? "si";
        $this->festivo = $args["festivo"] ?? "no";
    }

    // Crea una hora solo con los campos permitidos del formulario
    public static function desdeFormulario(array $datos): static
    {
        $limpio = array_intersect_key($datos, array_flip(self::CAMPOS_FORMULARIO));
        foreach (["inicio", "final"] as $campo) {
            if (isset($limpio[$campo])) {
                $limpio[$campo] = normalizarFecha((string) $limpio[$campo]) ?? "";
            }
        }
        $limpio["cena"] = ($limpio["cena"] ?? "") === "si" ? "si" : "no";
        $limpio["taxi"] = ($limpio["taxi"] ?? "") === "si" ? "si" : "no";
        $limpio["festivo"] = ($limpio["festivo"] ?? "") === "si" ? "si" : "no";

        return new static($limpio);
    }

    // Una hora queda pendiente de auditoria salvo que la cargue/edite un rol que no requiere auditoria
    public static function estadoAuditoriaPara(string $modo): string
    {
        if (puede("auditar")) return "no";
        return in_array($modo, Config::get("roles.cargan_sin_auditoria", []), true) ? "no" : "si";
    }

    public static function aprooveAudition(int $id): void
    {
        self::ejecutar("UPDATE " . static::$tabla . " SET auditar = 'no' WHERE id = ? LIMIT 1", [$id]);
    }

    public function validar(): array
    {
        $this->errores = [];

        if (!trim($this->empleado)) {
            $this->errores[] = "Empleado: Selecciona un empleado";
        }
        if (!trim($this->cliente)) {
            $this->errores[] = "Cliente: Selecciona un cliente";
        }
        if (!$this->inicio) {
            $this->errores[] = "Inicio: No puede quedar vacio o tiene un formato invalido";
        }
        if (!$this->final) {
            $this->errores[] = "Final: No puede quedar vacio o tiene un formato invalido";
        }
        if ($this->inicio && $this->final && $this->final <= $this->inicio) {
            $this->errores[] = "Final: Debe ser posterior al inicio";
        }
        $campos = [
            "diurnas_ordinarias" => "Diurnas Ordinarias",
            "nocturnas_ordinarias" => "Nocturnas Ordinarias",
            "diurnas_extras" => "Diurnas Extras",
            "nocturnas_extras" => "Nocturnas Extras",
        ];
        foreach ($campos as $campo => $nombre) {
            if (!is_numeric($this->$campo) || $this->$campo < 0) {
                $this->errores[] = "$nombre: Debe ser un numero mayor o igual a 0";
            }
        }
        if (!in_array((string) $this->almuerzo, ["0", "1", "2"], true)) {
            $this->errores[] = "Almuerzo: Coloca un valor en el almuerzo";
        }
        return $this->errores;
    }

    // ==========
    // CONSULTAS
    // ==========

    /**
     * Construye el WHERE comun: rango de fechas, filtro por columna y estado de auditoria.
     * Todo con placeholders; los valores van en $params.
     */
    private static function condiciones(?string $column, ?string $param, ?string $from, ?string $to, ?string $audit, array &$params): string
    {
        $partes = [];

        $desde = normalizarFecha($from);
        $hasta = normalizarFecha($to, true);
        if ($desde && $hasta) {
            $partes[] = "((inicio BETWEEN ? AND ?) OR (final BETWEEN ? AND ?))";
            array_push($params, $desde, $hasta, $desde, $hasta);
        }

        if ($like = self::condicionLike($column, $param, $params)) {
            $partes[] = $like;
        }

        if ($audit !== null) {
            $partes[] = "auditar = ?";
            $params[] = $audit;
        }

        return $partes ? "WHERE " . implode(" AND ", $partes) : "";
    }

    public static function allPaginated($page = 1, $perPage = null, string $audit = "no", array $filtros = []): array
    {
        $params = [];
        $where = self::condiciones($filtros["column"] ?? null, $filtros["param"] ?? null, null, null, $audit, $params);
        return static::paginar($where, $params, $page, $perPage);
    }

    // Horas aprobadas con su monto calculado, paginadas (pantalla "Ver Horas")
    public static function getPaymentsPaginated($page = 1, $perPage = null, ?string $column = null, ?string $param = null, ?string $from = null, ?string $to = null): array
    {
        $params = [];
        $where = self::condiciones($column, $param, $from, $to, "no", $params);
        $paginacion = static::paginar($where, $params, $page, $perPage);

        $data = self::conMontos($paginacion["data"]);
        unset($paginacion["data"]);

        return ["data" => $data, "pagination" => $paginacion];
    }

    // Horas aprobadas con su monto calculado, sin paginar (exportacion)
    public static function getPayments(?string $column = null, ?string $param = null, ?string $from = null, ?string $to = null): array
    {
        $params = [];
        $where = self::condiciones($column, $param, $from, $to, "no", $params);
        return self::conMontos(self::consultarSQL("SELECT * FROM " . static::$tabla . " $where ORDER BY id DESC", $params));
    }

    // Convierte cada hora en un arreglo con sus montos (una sola consulta de empleados para todas)
    private static function conMontos(array $times): array
    {
        $empleados = Empleados::indexadosPorNombre();
        $resultado = [];

        foreach ($times as $time) {
            $empleado = $empleados[mb_strtolower(trim($time->empleado), "UTF-8")] ?? null;
            $fila = get_object_vars($time);
            unset($fila["errores"]);
            $resultado[] = array_merge($fila, CalculadoraPagos::pagoEmpleado($time, $empleado));
        }
        return $resultado;
    }

    public static function getClientHours(string $name, ?string $from = null, ?string $to = null): array
    {
        $params = [$name];
        $where = "WHERE cliente = ? AND NOT cliente = 'administrativo'";

        $desde = normalizarFecha($from);
        $hasta = normalizarFecha($to, true);
        if ($desde && $hasta) {
            $where .= " AND ((inicio BETWEEN ? AND ?) OR (final BETWEEN ? AND ?))";
            array_push($params, $desde, $hasta, $desde, $hasta);
        }

        return self::consultarSQL("SELECT * FROM " . static::$tabla . " $where ORDER BY inicio DESC, id DESC", $params);
    }

    // Resumen de cobro por cliente (una sola consulta de horas para todos los clientes)
    public static function getAgentPayments(?string $from = null, ?string $to = null, ?string $param = null): array
    {
        $clientes = ($param !== null && trim($param) !== "") ? Clientes::getByFullName(trim($param)) : Clientes::all();
        if (!$clientes) return [];

        $params = [];
        $where = "WHERE NOT cliente = 'administrativo'";
        $desde = normalizarFecha($from);
        $hasta = normalizarFecha($to, true);
        if ($desde && $hasta) {
            $where .= " AND ((inicio BETWEEN ? AND ?) OR (final BETWEEN ? AND ?))";
            array_push($params, $desde, $hasta, $desde, $hasta);
        }

        $horasPorCliente = [];
        foreach (self::consultarSQL("SELECT * FROM " . static::$tabla . " $where", $params) as $time) {
            $horasPorCliente[mb_strtolower(trim($time->cliente), "UTF-8")][] = $time;
        }

        $resultado = [];
        foreach ($clientes as $cliente) {
            $nombre = $cliente->nombreCompleto();
            $horas = $horasPorCliente[mb_strtolower($nombre, "UTF-8")] ?? [];
            if (!$horas) continue;

            $resumen = CalculadoraPagos::resumenClienteVacio($nombre);
            foreach ($horas as $time) {
                CalculadoraPagos::acumularCliente($resumen, $time, $cliente->tipo);
            }
            $resultado[] = $resumen;
        }
        return $resultado;
    }
}
