<?php

namespace Models;

use MVC\Config;

class Clientes extends BaseModel
{
    protected static $columnasDB = ["id", "nombre", "apellido", "tipo"];
    protected static $tabla = "clientes";
    public $id, $nombre, $apellido, $tipo;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->nombre = $args["nombre"] ?? "";
        $this->apellido = $args["apellido"] ?? "";
        $this->tipo = $args["tipo"] ?? "";
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
        $tipos = array_keys(Config::get("clientes.tarifas", []));
        if (!in_array(strtolower($this->tipo), $tipos, true)) {
            $this->errores[] = "Tipo: Debes elegir uno de: " . implode(", ", $tipos);
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
