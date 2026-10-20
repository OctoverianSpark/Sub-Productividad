<?php

namespace Controllers;

use Models\Clientes;
use Models\Empleados;
use Models\Logs;
use Models\Paginacion;
use Models\Time;
use MVC\Router;

class Times
{
    public static function crear(Router $router)
    {
        $empleados = Empleados::all();
        $clientes = Clientes::all();
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $horas = [];

            // Primero se validan todas las entradas; si alguna falla no se guarda ninguna
            foreach ((array) ($_POST["horas"] ?? []) as $indice => $datos) {
                $time = Time::desdeFormulario((array) $datos);
                $time->auditar = Time::estadoAuditoriaPara(modoActual());

                foreach ($time->validar() as $error) {
                    $errores[] = "Entrada " . ((int) $indice + 1) . " - " . $error;
                }
                $horas[] = $time;
            }

            if (!$horas) {
                $errores[] = "No se recibieron horas para registrar";
            }

            if (empty($errores)) {
                foreach ($horas as $time) {
                    $time->guardar();
                    Logs::registrar("Hora Cargada", "El usuario " . $_SESSION["name"] . " cargo las horas de " . $time->empleado . " en la fecha " . $time->inicio);
                }
                redirigir("/horas/ver?resultado=1");
            }
        }

        $router->render("pages/horas/crear", [
            "empleados" => $empleados,
            "clientes" => $clientes,
            "errores" => $errores
        ]);
    }

    public static function ver(Router $router)
    {
        $page = $_GET["page"] ?? 1;
        $perPage = $_GET["per_page"] ?? null;
        $table = ($_GET["table"] ?? "") ?: "empleados";
        $column = $_GET["column"] ?? null;
        $param = $_GET["param"] ?? null;
        $from = $_GET["from"] ?? null;
        $to = $_GET["to"] ?? null;

        $horas = [];
        $paginacion = null;
        $pagination_links = null;

        if ($table === "empleados") {
            $result = Time::getPaymentsPaginated($page, $perPage, $column, $param, $from, $to);
            $horas = $result["data"];
            $paginacion = $result["pagination"];

            $urlFilters = compact("table", "column", "param", "from", "to");
            $pagination_links = Paginacion::buildPaginationLinks("/horas/ver", $paginacion, $urlFilters);
        } else if ($table === "clientes") {
            $horas = Time::getAgentPayments($from, $to, $param);
        } else if ($table === "auditar" && puede("auditar")) {
            $paginacion = Time::allPaginated($page, $perPage, "si", ["column" => $column, "param" => $param]);
            $horas = $paginacion["data"];

            $urlFilters = compact("table", "column", "param");
            $pagination_links = Paginacion::buildPaginationLinks("/horas/ver", $paginacion, $urlFilters);
        } else {
            redirigir("/");
        }

        $router->render("pages/horas/ver", [
            "horas" => $horas,
            "paginacion" => $paginacion,
            "pagination_links" => $pagination_links,
            "table" => $table,
        ]);
    }

    public static function hora(Router $router)
    {
        $id = validarID();

        $hora = Time::find($id);
        if (!$hora) {
            redirigir("/horas/ver");
        }

        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // Se parte de los datos actuales y se sobreescriben solo los campos enviados.
            // Las casillas (cena, taxi, festivo) se toman solo del formulario: si no vienen, es "no".
            $actual = $hora->atributos();
            unset($actual["cena"], $actual["taxi"], $actual["festivo"]);

            $horas = Time::desdeFormulario(array_merge($actual, (array) ($_POST["horas"] ?? [])));
            $horas->id = $id;
            $horas->auditar = Time::estadoAuditoriaPara(modoActual());

            $errores = $horas->validar();

            if (empty($errores)) {
                $horas->guardar();
                Logs::registrar("Hora Actualizada", "El usuario " . $_SESSION["name"] . " hizo una actualizacion de las horas de " . $horas->empleado . " en la fecha " . $horas->inicio);

                $table = $_GET["table"] ?? null;
                redirigir("/horas/ver?" . http_build_query(array_filter(["table" => $table, "resultado" => 2])));
            }
        }

        $router->render("pages/horas/hora", [
            "hora" => $hora,
            "empleados" => Empleados::all(),
            "clientes" => Clientes::all(),
            "errores" => $errores,
        ]);
    }

    public static function cliente(Router $router)
    {
        $nombre = trim((string) ($_GET["name"] ?? ""));

        $horas = $nombre !== ""
            ? Time::getClientHours($nombre, $_GET["from"] ?? null, $_GET["to"] ?? null)
            : [];

        $router->render("pages/horas/cliente", [
            "horas" => $horas,
            "nombre" => $nombre,
        ]);
    }

    public static function aproove()
    {
        $id = validarID();
        $time = Time::find($id);

        if ($time) {
            Time::aprooveAudition($id);
            Logs::registrar("Auditoria Aprobada", "El usuario " . $_SESSION["name"] . " aprobo las horas de " . $time->empleado . " en la fecha " . $time->inicio);
        }

        redirigir("/horas/ver?table=auditar");
    }

    public static function eliminar()
    {
        $id = validarID();
        $time = Time::find($id);

        if ($time) {
            $time->eliminar();
            Logs::registrar("Hora Eliminada", "El usuario " . $_SESSION["name"] . " ha eliminado las horas de " . $time->empleado . " en la fecha " . $time->inicio);
        }

        redirigir("/horas/ver?resultado=3");
    }
}
