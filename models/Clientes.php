<?php

namespace Models;

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

    //Valida los datos del cliente.
    public function validar()
    {
        if (!$this->nombre) {
            self::$errores[] = "Nombre: El nombre es obligatorio";
        }
        if (!$this->apellido) {
            self::$errores[] = "Apellido: El apellido es obligatorio";
        }
        if (!$this->tipo) {
            self::$errores[] = "Tipo: Debes definir si es un part time o un full time";
        }
        return self::$errores;
    }

    // Busca clientes cuyo nombre coincida parcialmente con el parametro.
    public static function getByFullName($name)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE CONCAT(nombre,' ',apellido) LIKE '%$name%'";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }


     public static function filter($column, $param)
    {
        if ($column && $param) {
            $column = self::$db->escape_string($column);
            $param = self::$db->escape_string($param);
            $query = "SELECT * FROM " . static::$tabla . " WHERE $column LIKE '%$param%' ORDER BY id DESC";
            return self::consultarSQL($query);
        } else {
            $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC";
            return self::consultarSQL($query);
        }
    }


}
