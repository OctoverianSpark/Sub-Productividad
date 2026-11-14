<?php

namespace Models;

class Clientes extends BaseModel
{
    protected static $columnasDB = ["id", "nombre", "apellido"];
    protected static $tabla = "clientes";
    public $id, $nombre, $apellido;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->nombre = $args["nombre"] ?? "";
        $this->apellido = $args["apellido"] ?? "";
    }

    public function nombreCompleto(): string
    {
        return trim($this->nombre . " " . $this->apellido);
    }

    //Valida los datos del cliente.
    public function validar(): array
    {
        $this->errores = [];

        if (!trim($this->nombre)) {
            $this->errores[] = "Nombre: El nombre es obligatorio";
        }
        if (!trim($this->apellido)) {
            $this->errores[] = "Apellido: El apellido es obligatorio";
        }
        return $this->errores;
    }

    // Busca clientes cuyo nombre coincida parcialmente con el parametro.
    public static function getByFullName(string $name): array
    {
        return self::consultarSQL(
            "SELECT * FROM " . static::$tabla . " WHERE CONCAT(nombre, ' ', apellido) LIKE ?",
            ["%" . $name . "%"]
        );
    }
}
