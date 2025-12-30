<?php

namespace Models;

use DateTime;
use Models\Empleados;

class Time extends BaseModel
{
    protected static $tabla = "horas_extras";
    protected static $columnasDB = ["id", "empleado", "cliente", "inicio", "almuerzo", "final", "diurnas_ordinarias", "nocturnas_ordinarias", "diurnas_extras", "nocturnas_extras", "cena", "taxi", "comentarios", "auditar", "festivo"];
    public $id, $empleado, $cliente, $inicio, $almuerzo, $final, $diurnas_ordinarias, $nocturnas_ordinarias, $diurnas_extras, $nocturnas_extras, $cena, $taxi, $comentarios, $auditar, $festivo;


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


    public static function aprooveAudition($id)
    {
        $query = "UPDATE " . static::$tabla . " SET auditar = 'no' WHERE id = " . $id;
        self::$db->query($query);
    }

    public static function findByDate($range1, $range2, $audit)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        $query .= "((DATE(inicio) BETWEEN '$range1 00:00:00' AND '$range2 23:59:00') OR";
        $query .= "(DATE(final) BETWEEN '$range1 00:00:00' AND '$range2 23:59:00'))";
        $query .= " AND auditar = '$audit'";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }


    public static function findByDateAndName($column = null, $param = null,$range1, $range2, $audit = "no")
{
    // Partes del WHERE
    $whereParts = [];

    // Filtro de fechas
    $whereParts[] = "(
        (inicio BETWEEN '$range1 00:00:00' AND '$range2 23:59:59') OR
        (final BETWEEN '$range1 00:00:00' AND '$range2 23:59:59')
    )";

    // Filtro de nombre/columna (opcional)
    if ($column && $param) {
        $whereParts[] = "$column LIKE '$param%'";
    }

    // Filtro audit
    $whereParts[] = "auditar = '$audit'";

    // Construcción final
    $whereClause = "WHERE " . implode(" AND ", $whereParts);

    $query = "SELECT * FROM " . static::$tabla . " $whereClause ORDER BY id DESC";
    return self::consultarSQL($query);
}


    public static function all($audit = "no")
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE auditar = '$audit' ORDER BY id DESC,inicio ASC";
        $resultado = self::consultarSQL($query);
        return $resultado;
    }

    public static function allPaginated($page = 1, $perPage = 20, $audit = "no", $filtros = [])
    {
        $offset = ($page - 1) *  $perPage;

        $whereClause = " WHERE auditar = '$audit'";

        if (!empty($filtros["column"]) && !empty($filtros["param"])) {
            $column = self::$db->escape_string($filtros["column"]);
            $param = self::$db->escape_string($filtros["param"]);
            $whereClause .= " AND $column LIKE '%$param%'";
        }

        $countQuery = "SELECT COUNT(*) as total FROM " . static::$tabla . "$whereClause";
        $result = self::$db->query($countQuery);
        $totalRecords = $result->fetch_assoc()["total"];

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
            "next_page" => $page < $totalPages ? $page + 1 : null,
            "prev_page" => $page > 1 ? $page - 1 : null,
        ];
    }

    public static function getPaymentsPaginated($page = 1, $perPage = 20, $column = null, $param = null, $from = null, $to = null)
    {
        if ($from && $to) {
            $times = static::findByDateAndNamePaginated($from, $to,$column, $param, "no", $page, $perPage);
            
        } else {
            $times = static::filterPaginated($column, $param, "no", $page, $perPage);
        }


        $resultado = [];
        $i = 0;

        foreach ($times["data"] as $time) {
            $inicio = date_timestamp_get(new DateTime($time->inicio));
            $empleado = Empleados::getByFullName(strtolower($time->empleado));
            $salarioHora = $empleado->salario / (8 * 30);
            if ($empleado->modalidad == "oficina" && $empleado->sede == "colombia") {
                $diurnasExtra = round($salarioHora + ($salarioHora * .25), 2);
                $nocturnasExtra = round($salarioHora + ($salarioHora * .75), 2);
                $horaDominical = round($salarioHora + ($salarioHora * .75), 2);
                $diurnasExtraDominicales = round($salarioHora + $salarioHora, 2);
                $nocturnasExtraDominicales = round($salarioHora + ($salarioHora * 1.5), 2);
                $moneda = "COP";

                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->diurnas_ordinarias * $horaDominical) + ($time->diurnas_extras * $diurnasExtraDominicales) : $time->diurnas_extras * $diurnasExtra;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->nocturnas_ordinarias * $horaDominical) + ($time->nocturnas_extras * $nocturnasExtraDominicales) : $time->nocturnas_extras * $nocturnasExtra;
            } else if ($empleado->modalidad == "hogar") {
                $extras = 2.8;
                $moneda = "Dolares";

                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->diurnas_ordinarias * $extras) + ($time->diurnas_extras * $extras) : $time->diurnas_extras * $extras;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->nocturnas_ordinarias * $extras) + ($time->nocturnas_extras * $extras) : $time->nocturnas_extras * $extras;
            } else if ($empleado->modalidad == "oficina" && $empleado->sede == "venezuela") {
                $extras = 2;
                $moneda = "Dolares";

                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->diurnas_ordinarias * $extras) : $time->diurnas_extras * $extras;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->nocturnas_ordinarias * $extras) + ($time->nocturnas_extras * $extras) : $time->nocturnas_extras * $extras;
            }

            foreach ($time as $key => $value) {
                $resultado[$i][$key] = $value;
            }

            $resultado[$i]["moneda"] = $moneda;
            $i++;
        }


        return [
            "data" => $resultado,
            "pagination" => [
                "current_page" => $times["current_page"],
                "per_page" => $times["per_page"],
                "total_records" => $times["total_records"],
                "total_pages" => $times["total_pages"],
                "has_next" => $times["has_next"],
                "has_prev" => $times["has_prev"],
                "next_page" => $times["next_page"],
                "prev_page" => $times["prev_page"]
            ]
        ];
    }

    public static function findByDateAndNamePaginated($range1, $range2, $column = null, $param = null, $audit = "no", $page = 1, $perPage = 20)
{
    $offset = ($page - 1) * $perPage;

    // Filtro por fechas
    $whereParts = [];
    $whereParts[] = "((inicio BETWEEN '$range1 00:00:00' AND '$range2 23:59:59')
                    OR (final BETWEEN '$range1 00:00:00' AND '$range2 23:59:59'))";

    // Filtro por nombre/columna si existe
    if ($column && $param) {
        $whereParts[] = "$column LIKE '%$param%'";
    }

    // Filtro de audit
    $whereParts[] = "auditar = '$audit'";

    // Construcción final del WHERE
    $whereClause = "WHERE " . implode(" AND ", $whereParts);

    // Consulta para obtener total
    $countQuery = "SELECT COUNT(*) as total FROM " . static::$tabla . " $whereClause";
    $result = self::$db->query($countQuery);
    $totalRecords = $result->fetch_assoc()["total"];

    // Consulta paginada
    $dataQuery = "SELECT * FROM " . static::$tabla . " 
                  $whereClause 
                  ORDER BY id DESC 
                  LIMIT $perPage OFFSET $offset";

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
        "next_page" => $page < $totalPages ? $page + 1 : null,
        "prev_page" => $page > 1 ? $page - 1 : null,
    ];
}


    public static function filterPaginated($column, $param, $audit = "no", $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        if ($column && $param) {
            $whereClause = " WHERE $column LIKE '%$param%' AND auditar = '$audit'";
        } else {
            $whereClause = " WHERE auditar = '$audit'";
        }

        $countQuery = "SELECT COUNT(*) as total FROM " . static::$tabla . " $whereClause";
        $result = self::$db->query($countQuery);
        $totalRecords = $result->fetch_assoc()["total"];

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
            "next_page" => $page < $totalPages ? $page + 1 : null,
            "prev_page" => $page > 1 ? $page - 1 : null,
        ];
    }

    public static function findByDatePaginated($range1, $range2, $audit, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $whereClause = "WHERE ((DATE(inicio) BETWEEN '$range1 00:00:00' AND '$range2 23:59:00') OR";
        $whereClause .= "(DATE(final) BETWEEN '$range1 00:00:00' AND '$range2 23:59:00'))";
        $whereClause .= " AND auditar = '$audit'";

        $countQuery = "SELECT COUNT(*) as total FROM " . static::$tabla . " $whereClause";
        $result = self::$db->query($countQuery);
        $totalRecords = $result->fetch_assoc()['total'];

        $dataQuery = "SELECT * FROM " . static::$tabla . " $whereClause ORDER BY id DESC LIMIT $perPage OFFSET $offset";
        $data = self::consultarSQL($dataQuery);

        $totalPages = ceil($totalRecords / $perPage);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1,
            'next_page' => $page < $totalPages ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null
        ];
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
        $atributos["auditar"] = "no";
        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= strtolower(join(",", $valores));
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "'";

        $resultado = self::$db->query($query);
        return $resultado;
    }

    public static function filter($column, $param, $audit = "no")
    {
        if ($column && $param) {
            $query = "SELECT * FROM " . static::$tabla . " WHERE $column LIKE '%$param%' AND auditar = '$audit'";
            $resultado = self::consultarSQL($query);
        } else {
            $resultado = static::all($audit);
        }
        return $resultado;
    }


    public function validar()
    {
        if (!$this->inicio) {
            static::$errores[] = "Inicio: No puede quedar vacio";
        }
        if (!$this->final) {
            static::$errores[] = "Final: No puede quedar vacio";
        }
        if ($this->diurnas_extras < 0) {
            static::$errores[] = "Diurnas Extras: Valor Negativo no valido";
        }
        if ($this->nocturnas_extras < 0) {
            static::$errores[] = "Nocturnas Extras: Valor Negativo no valido";
        }
        if ($this->diurnas_ordinarias < 0) {
            static::$errores[] = "Diurnas Ordinarias: Valor Negativo no valido";
        }
        if ($this->nocturnas_ordinarias < 0) {
            static::$errores[] = "Nocturnas Ordinarias: Valor Negativo no valido";
        }
        if ($this->almuerzo == "") {
            static::$errores[] = "Almuerzo: Coloca un valor en el almuerzo";
        }

        return static::$errores;
    }


    public static function getClientHours($name, $from = null, $to = null)
    {
        $query = "SELECT * FROM " . self::$tabla . " WHERE cliente = '$name'";

        if ($from && $to) {
            $query .= "AND ((inicio BETWEEN '$from 00:00:00' AND '$to 23:59:00') OR";
            $query .= "(final BETWEEN '$from 00:00:00' AND '$to 23:59:00'))";
        }
        $query .= " AND NOT cliente = 'administrativo'";
        $query .= " ORDER BY inicio DESC, id DESC";
        $resultado = self::consultarSQL($query);

        return $resultado;
    }


    public static function getPayments($column = null, $param = null, $from = null, $to = null)
    {
            $times = static::findByDateAndName($column,$param,$from, $to, "no");

        $resultado = [];
        $i = 0;
        foreach ($times as $time) {
            $inicio = date_timestamp_get(new DateTime($time->inicio));

            $empleado = Empleados::getByFullName(strtolower($time->empleado));

            $salarioHora = $empleado->salario / (8 * 30);

            if ($empleado->modalidad == "oficina" && $empleado->sede == "colombia") {
                $diurnasExtra = round($salarioHora + ($salarioHora * .25), 2);
                $nocturnasExtra = round($salarioHora + ($salarioHora * .75), 2);
                $horaDominical = round($salarioHora + ($salarioHora * .75), 2);
                $diurnasExtraDominicales = round($salarioHora + $salarioHora, 2);
                $nocturnasExtraDominicales = round($salarioHora + ($salarioHora * 1.5), 2);
                $moneda = "COP";

                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->diurnas_ordinarias * $horaDominical) + ($time->diurnas_extras * $diurnasExtraDominicales) : $time->diurnas_extras * $diurnasExtra;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday"  || $time->festivo == "si") ? ($time->nocturnas_ordinarias * $horaDominical) + ($time->nocturnas_extras * $nocturnasExtraDominicales) : $time->nocturnas_extras * $nocturnasExtra;
            } else if ($empleado->modalidad == "hogar") {
                $extras = 2.8;
                $moneda = "Dolares";

                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->diurnas_ordinarias * $extras) + ($time->diurnas_extras * $extras) : $time->diurnas_extras * $extras;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->nocturnas_ordinarias * $extras) + ($time->nocturnas_extras * $extras) : $time->nocturnas_extras * $extras;
            } else if ($empleado->modalidad == "oficina" && $empleado->sede == "venezuela") {
                $extras = 2;
                $moneda = "Dolares";


                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->diurnas_ordinarias * $extras) + ($time->diurnas_extras * $extras) : $time->diurnas_extras * $extras;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday" || $time->festivo == "si") ? ($time->nocturnas_ordinarias * $extras) + ($time->nocturnas_extras * $extras) : $time->nocturnas_extras * $extras;
            }


            foreach ($time as $key => $value) {
                $resultado[$i][$key] = $value;
            }

            $resultado[$i]["moneda"] = $moneda;
            $i++;
        }



        return $resultado;
    }

    public static function getAgentPayments($from = null, $to = null, $param = null)
    {
        if (!is_null($param)) {
            $clientes = Clientes::getByFullName($param);
        } else {
            $clientes = Clientes::all();
        }

        $resultado = [];
        $i = 0;

        foreach ($clientes as $cliente) {

            $tipo = strtolower($cliente->tipo);
            if ($tipo === "nuevo") {

                $diurnasExtra = 6.5;
                $nocturnasExtra = 7;
                $diurnasExtraDominicales = 7.5;
                $nocturnasExtraDominicales = 8;
            } else if ($tipo === "viejo") {

                $diurnasExtra = 6;
                $nocturnasExtra = 6.5;
                $diurnasExtraDominicales = 7;
                $nocturnasExtraDominicales = 7.5;
            }

            $resultado[$i]["cliente"] = $cliente->nombre . " " . $cliente->apellido;
            $resultado[$i]["diurnas"] = 0;
            $resultado[$i]["diurnas_domingo"] = 0;
            $resultado[$i]["nocturnas"] = 0;
            $resultado[$i]["nocturnas_domingo"] = 0;
            $resultado[$i]["diurnas_monto"] = 0;
            $resultado[$i]["nocturnas_monto"] = 0;

            $query = "SELECT * FROM " . static::$tabla . " WHERE cliente = '" . $resultado[$i]["cliente"] . "' AND NOT cliente = 'administrativo'";

            if (!is_null($from) && !is_null($to)) {
                $query .= " AND ((inicio between '$from 00:00:00' and '$to 23:59:59') OR";
                $query .= " (final between '$from 00:00:00' and '$to 23:59:59'))";
            }

            $times = self::consultarSQL($query);

            if (count($times) <= 0) continue;
            foreach ($times as $time) {

                $inicio = date_timestamp_get(new DateTime($time->inicio));
                $resultado[$i]["diurnas"] +=  (getdate($inicio)["weekday"] != "Sunday") ? $time->diurnas_extras : 0;
                $resultado[$i]["nocturnas"] +=  (getdate($inicio)["weekday"] != "Sunday") ? $time->nocturnas_extras : 0;
                $resultado[$i]["diurnas_domingo"] +=  (getdate($inicio)["weekday"] == "Sunday") ? $time->diurnas_extras : 0;
                $resultado[$i]["diurnas_ordinarias_domingo"] +=  (getdate($inicio)["weekday"] == "Sunday") ? $time->diurnas_ordinarias : 0;
                $resultado[$i]["nocturnas_domingo"] +=  (getdate($inicio)["weekday"] == "Sunday") ? $time->nocturnas_extras : 0;
                $resultado[$i]["nocturnas_ordinarias_domingo"] +=  (getdate($inicio)["weekday"] == "Sunday") ? $time->nocturnas_ordinarias : 0;
                $resultado[$i]["diurnas_monto"] += (getdate($inicio)["weekday"] == "Sunday") ? $time->diurnas_extras * $diurnasExtraDominicales : $time->diurnas_extras * $diurnasExtra;
                $resultado[$i]["nocturnas_monto"] += (getdate($inicio)["weekday"] == "Sunday") ? $time->nocturnas_extras * $nocturnasExtraDominicales : $time->nocturnas_extras * $nocturnasExtra;
                $resultado[$i]["diurnas_monto"] += (getdate($inicio)["weekday"] == "Sunday") ? $time->diurnas_ordinarias * $diurnasExtraDominicales : 0;

                $resultado[$i]["logistica"] += ($time->cena === "si") ? 5 : 0;
                $resultado[$i]["logistica"] += ($time->taxi === "si") ? 5 : 0;
            }

            $i++;
        }

        return $resultado;
    }
}
