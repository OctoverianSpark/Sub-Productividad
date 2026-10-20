<?php

namespace Models;

class Empleados extends BaseModel
{
    protected static $tabla = "empleados";
    protected static $columnasDB = ["id", "nombre", "apellido", "tipo_documento", "documento", "sede", "modalidad", "cargo", "salario"];
    public $id, $nombre, $apellido, $tipo_documento, $documento, $sede, $modalidad, $cargo, $salario;

    private const TIPOS_DOCUMENTO = ["ppt", "pasaporte", "cc", "cv", "ce"];
    private const SEDES = ["venezuela", "colombia"];
    private const MODALIDADES = ["oficina", "hogar"];

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->nombre = $args["nombre"] ?? "";
        $this->apellido = $args["apellido"] ?? "";
        $this->tipo_documento = $args["tipo_documento"] ?? "";
        $this->documento = $args["documento"] ?? "";
        $this->sede = $args["sede"] ?? "";
        $this->modalidad = $args["modalidad"] ?? "";
        $this->cargo = $args["cargo"] ?? "";
        $this->salario = $args["salario"] ?? null;
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombre . " " . $this->apellido);
    }

    public function validar(): array
    {
        $this->errores = [];

        if (!trim($this->nombre)) {
            $this->errores[] = "El nombre es obligatorio";
        }
        if (!trim($this->apellido)) {
            $this->errores[] = "El apellido es obligatorio";
        }
        if (!trim($this->documento)) {
            $this->errores[] = "El documento es obligatorio";
        }
        if (!in_array(strtolower($this->tipo_documento), self::TIPOS_DOCUMENTO, true)) {
            $this->errores[] = "El tipo de documento no es valido";
        }
        if (!in_array(strtolower($this->sede), self::SEDES, true)) {
            $this->errores[] = "La sede no es valida";
        }
        if (!in_array(strtolower($this->modalidad), self::MODALIDADES, true)) {
            $this->errores[] = "La modalidad no es valida";
        }
        if (!trim($this->cargo)) {
            $this->errores[] = "El cargo es obligatorio";
        }
        if (!is_numeric($this->salario) || $this->salario <= 0) {
            $this->errores[] = "El salario es obligatorio y debe ser mayor a 0";
        }
        return $this->errores;
    }

    public static function getByFullName(string $name): ?static
    {
        $resultado = self::consultarSQL(
            "SELECT * FROM " . static::$tabla . " WHERE CONCAT(nombre, ' ', apellido) = ? LIMIT 1",
            [$name]
        );
        return $resultado[0] ?? null;
    }

    // Todos los empleados indexados por nombre completo en minusculas (evita una consulta por fila)
    public static function indexadosPorNombre(): array
    {
        $indice = [];
        foreach (static::all() as $empleado) {
            $indice[mb_strtolower($empleado->nombreCompleto(), "UTF-8")] = $empleado;
        }
        return $indice;
    }
}
