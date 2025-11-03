<?php

namespace Models;

class Users extends BaseModel
{
    protected static $tabla = "users";
    protected static $columnasDB = ["id", "user", "email", "mode"];
    public $id, $user, $email, $mode;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->user = $args["user"] ?? null;
        $this->email = $args["email"] ?? null;
        $this->mode = $args["mode"] ?? null;
    }

    public static function all()
    {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY mode ASC";
        return self::consultarSQL($query);
    }


    public function actualizar()
    {
        $atributos = $this->sanitizarAtributos();
        $valores = [];

        foreach ($atributos as $key => $value) {
            if ($atributos[$key] === "" || $atributos[$key] === null) continue;
            if ($key === "creado") continue;
            $valores[] = "$key='$value'";
        }

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= strtolower(join(",", $valores));
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "'";
        $query .= " LIMIT 1";

        $resultado = self::$db->query($query);
        return $resultado;
    }


    public static function  findUser($mail = null, $user = null)
    {
        $query = "SELECT * FROM " . self::$tabla . " WHERE ";

        if ($mail) {
            $query .= "email = '$mail'";
        }
        if ($user) {
            $query .= "user = '$user'";
        }
        $resultado = self::consultarSQL(query: $query);

        return array_shift($resultado);
    }


    public static function filter($column, $param)
    {
        if ($column && $param) {
            $query = "SELECT * FROM " . static::$tabla . " WHERE $column LIKE '%$param%' ORDER BY nombre ASC";
            return self::consultarSQL($query);
        } else {
            return static::all();
        }
    }
}
