<?php

namespace Models;


abstract class BaseModel
{

    protected static $db;
    protected static $tabla = "";
    protected static $columnasDB = [];
    public static $errores = [];

    public $id;

    // Conexion a la base de datos
    public static function setDB($db)
    {
        self::$db = $db;
    }

    // Retorna todas las columnas excepto la ID
  public static function getColumns()
{
    $columnas = [];
    foreach (static::$columnasDB as $columna) {
        if ($columna === "id") continue;
        $columnas[] = $columna;
    }
    return $columnas;
}

    // ===========
    // CRUD BASICO
    // ===========

    public static function all()
    {
        return self::consultarSQL("SELECT * FROM " . static::$tabla);
    }

    public static function get($limit)
    {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT " . $limit;
        return self::consultarSQL($query);
    }

    public static function find($id)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = $id";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    public function guardar()
    {
        return !$this->id ? $this->crear() : $this->actualizar();
    }

    public function crear()
    {
        $atributos = $this->sanitizarAtributos();

        $query = "INSERT INTO " . static::$tabla . " (";
        $query .= join(", ", array_keys($atributos));
        $query .= ")VALUES ('";
        $query .= strtolower(join("' , '", array_values($atributos)));
        $query .= "')";

        return self::$db->query($query);
    }

    public function actualizar()
    {
        $atributos = $this->sanitizarAtributos();
        $valores = [];

        foreach ($atributos as $key => $value) {
            if ($key === "id") continue;
            $valores[] = "$key='$value'";
        }

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= join(", ", $valores);
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' LIMIT 1";

        return self::$db->query($query);
    }

    public function eliminar()
    {
        $id = self::$db->escape_string($this->id);
        $query = "DELETE FROM " . static::$tabla . " WHERE id = $id";
        self::$db->query($query);
    }

    public static function filter($column, $param)
    {
        if ($column && $param) {
            $query = "SELECT * FROM " . static::$tabla . " WHERE $column LIKE '%$param%'";
            return self::consultarSQL($query);
        } else {
            return static::all();
        }
    }

    //==========
    //UTILIDADES
    //==========
    public static function consultarSQL($query)
    {
        $resultado = self::$db->query($query);

        $array = [];
        while ($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }

        $resultado->free();
        return $array;
    }

    protected static function crearObjeto($registro)
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    public function atributos()
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === "id") continue;
            $atributos[$columna] = $this->$columna ?? null;
        }
        return $atributos;
    }

    public function sanitizarAtributos()
    {
        $atributos = $this->atributos();
        $sanitizado = [];

        foreach ($atributos as $key => $value) {
            $sanitizado[$key] = self::$db->escape_string($value);
        }
        return $sanitizado;
    }


 public static function paginarConFiltros($page = 1, $perPage = 20, $filtros = [])
    {
        $offset = ($page - 1) * $perPage;

        $whereClause = "";
        if (!empty($filtros["column"]) && !empty($filtros["param"])) {
            $column = self::$db->escape_string($filtros["column"]);
            $param = self::$db->escape_string($filtros["param"]);
            $whereClause = " WHERE $column LIKE '%$param%'";
        }

        $countQuery = "SELECT COUNT(*) as total FROM " . static::$tabla . " $whereClause";
        $resultado = self::$db->query($countQuery);
        $totalRecords = $resultado->fetch_assoc()["total"];

        $dataQuery = "SELECT * FROM " . static::$tabla . " $whereClause ORDER BY id DESC LIMIT $perPage OFFSET $offset";
        $data = self::consultarSQL($dataQuery);

        $totalPages = ceil($totalRecords / $perPage);

        return [
             "data" => $data,
            "current_page" => $page,
            "per_page" => $perPage,
            "total_records" => $totalRecords,
            "total_pages" => $totalPages,
            "has_next" => $page < $totalPages,
            "has_prev" => $page > 1,
            "next_page" => $page < $totalPages ?  $page + 1 : null,
            "prev_page" => $page > 1 ? $page - 1 : null,
        ];
    }



}
