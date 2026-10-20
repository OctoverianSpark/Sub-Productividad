<?php

namespace Models;

use mysqli;
use mysqli_stmt;
use MVC\Config;

abstract class BaseModel
{
    protected static mysqli $db;
    protected static $tabla = "";
    protected static $columnasDB = [];

    // Columnas que se guardan tal cual; el resto se guarda en minusculas
    protected static $conservarMayusculas = [];

    // Si es true, al actualizar se ignoran los campos vacios (no se sobreescriben)
    protected static $omitirVaciosAlActualizar = false;

    protected array $errores = [];

    public $id;

    public static function setDB(mysqli $db): void
    {
        self::$db = $db;
    }

    // Retorna todas las columnas excepto la ID
    public static function getColumns(): array
    {
        return array_values(array_filter(static::$columnasDB, fn($c) => $c !== "id"));
    }

    // Solo se aceptan nombres de columna declarados en el modelo (lista blanca)
    public static function columnaValida(?string $columna): bool
    {
        return $columna !== null && in_array($columna, static::$columnasDB, true);
    }

    // ===========
    // CRUD BASICO
    // ===========

    public static function all(): array
    {
        return self::consultarSQL("SELECT * FROM " . static::$tabla . " ORDER BY id DESC");
    }

    public static function find($id): ?static
    {
        $resultado = self::consultarSQL("SELECT * FROM " . static::$tabla . " WHERE id = ? LIMIT 1", [(int) $id]);
        return $resultado[0] ?? null;
    }

    public function guardar(): bool
    {
        return !$this->id ? $this->crear() : $this->actualizar();
    }

    public function crear(): bool
    {
        $atributos = $this->atributosParaGuardar();
        $columnas = array_keys($atributos);

        $query = "INSERT INTO " . static::$tabla . " (`" . implode("`, `", $columnas) . "`) VALUES (";
        $query .= implode(", ", array_fill(0, count($columnas), "?")) . ")";

        self::ejecutar($query, array_values($atributos));
        $this->id = self::$db->insert_id;
        return true;
    }

    public function actualizar(): bool
    {
        $sets = [];
        $valores = [];

        foreach ($this->atributosParaGuardar() as $columna => $valor) {
            if (static::$omitirVaciosAlActualizar && $valor === "") continue;
            $sets[] = "`$columna` = ?";
            $valores[] = $valor;
        }
        if (!$sets) return false;

        $valores[] = (int) $this->id;
        self::ejecutar("UPDATE " . static::$tabla . " SET " . implode(", ", $sets) . " WHERE id = ? LIMIT 1", $valores);
        return true;
    }

    public function eliminar(): bool
    {
        self::ejecutar("DELETE FROM " . static::$tabla . " WHERE id = ? LIMIT 1", [(int) $this->id]);
        return true;
    }

    // Busqueda parcial por una columna permitida
    public static function filter(?string $column, ?string $param): array
    {
        $params = [];
        $where = self::condicionLike($column, $param, $params);

        return self::consultarSQL(
            "SELECT * FROM " . static::$tabla . ($where ? " WHERE $where" : "") . " ORDER BY id DESC",
            $params
        );
    }

    public static function paginarConFiltros($page = 1, $perPage = null, array $filtros = []): array
    {
        $params = [];
        $where = self::condicionLike($filtros["column"] ?? null, $filtros["param"] ?? null, $params);

        return static::paginar($where ? "WHERE $where" : "", $params, $page, $perPage);
    }

    //==========
    //UTILIDADES
    //==========

    // Ejecuta una consulta preparada. Los valores SIEMPRE van como parametros, nunca concatenados.
    protected static function ejecutar(string $query, array $params = []): mysqli_stmt
    {
        $stmt = self::$db->prepare($query);

        if ($params) {
            $tipos = "";
            foreach ($params as $p) {
                $tipos .= is_int($p) ? "i" : (is_float($p) ? "d" : "s");
            }
            $stmt->bind_param($tipos, ...array_values($params));
        }
        $stmt->execute();
        return $stmt;
    }

    public static function consultarSQL(string $query, array $params = []): array
    {
        $resultado = self::ejecutar($query, $params)->get_result();

        $array = [];
        while ($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }
        $resultado->free();
        return $array;
    }

    protected static function contar(string $where, array $params): int
    {
        $fila = self::ejecutar("SELECT COUNT(*) AS total FROM " . static::$tabla . " $where", $params)
            ->get_result()->fetch_assoc();
        return (int) $fila["total"];
    }

    // Arma "`columna` LIKE ?" si la columna es valida y hay valor; agrega el parametro a $params
    protected static function condicionLike(?string $columna, ?string $valor, array &$params): ?string
    {
        if (!static::columnaValida($columna) || $valor === null || trim($valor) === "") {
            return null;
        }
        $params[] = "%" . trim($valor) . "%";
        return "`$columna` LIKE ?";
    }

    /**
     * Paginacion unica para todos los modelos.
     * $where debe venir con placeholders (?) y sus valores en $params.
     */
    protected static function paginar(string $where, array $params, $page, $perPage, string $orden = "id DESC"): array
    {
        $page = max(1, (int) $page);
        $permitidos = Config::get("paginacion.por_pagina_permitidos", [20, 50, 100]);
        $perPage = in_array((int) $perPage, $permitidos, true)
            ? (int) $perPage
            : (int) Config::get("paginacion.por_pagina_defecto", 20);

        $totalRecords = static::contar($where, $params);
        $totalPages = (int) ceil($totalRecords / $perPage);

        $data = self::consultarSQL(
            "SELECT * FROM " . static::$tabla . " $where ORDER BY $orden LIMIT ? OFFSET ?",
            [...$params, $perPage, ($page - 1) * $perPage]
        );

        return [
            "data" => $data,
            "current_page" => $page,
            "per_page" => $perPage,
            "total_records" => $totalRecords,
            "total_pages" => $totalPages,
            "has_next" => $page < $totalPages,
            "has_prev" => $page > 1,
            "next_page" => $page < $totalPages ? $page + 1 : null,
            "prev_page" => $page > 1 ? $page - 1 : null,
        ];
    }

    protected static function crearObjeto(array $registro): static
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    public function atributos(): array
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === "id") continue;
            $atributos[$columna] = $this->$columna ?? null;
        }
        return $atributos;
    }

    // Normaliza valores antes de guardarlos: null -> "" y minusculas salvo columnas excluidas
    protected function atributosParaGuardar(): array
    {
        $atributos = [];
        foreach ($this->atributos() as $columna => $valor) {
            $valor = trim((string) ($valor ?? ""));
            if (!in_array($columna, static::$conservarMayusculas, true)) {
                $valor = mb_strtolower($valor, "UTF-8");
            }
            $atributos[$columna] = $valor;
        }
        return $atributos;
    }

    public function getErrores(): array
    {
        return $this->errores;
    }
}
