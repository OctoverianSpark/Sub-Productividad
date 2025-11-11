<?php



namespace Controllers;

use Google\Service\CloudDebugger\Resource\Debugger;
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

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $errores = [];
            foreach ($_POST["horas"] as $key=>$hora) {
                $hora["inicio"] = str_replace("T", " ", $hora["inicio"]);
                $hora["final"] = str_replace("T", " ", $hora["final"]);

                $times = new Time($hora);

                $logData = [
                    "titulo" => "Hora Cargada",
                    "contenido" => "El usuario " . $_SESSION["name"] . " cargo las horas de " . $times->empleado . " en la fecha " . $times->inicio
                ];
                $log = new Logs($logData);
                $errores = $times->validar();

                $times->guardar();
                $log->guardar();


            }
            header("Location: /horas/ver?resultado=1");

        }
        $router->render("pages/horas/crear", [
            "empleados" => $empleados,
            "clientes" => $clientes,
            "errores" => $errores
        ]);
    }


    public static function ver(Router $router)
    {
        $page = (int)($_GET["page"] ?? 1);
        $perPage = (int)($_GET["per_page"] ?? 20);

        if ($page < 1) $page = 1;

        $allowedPerPage = [20, 50, 100];

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 20;
        }

        $paginacion = null;
        $pagination_links = null;
        $filtros = [];
        if ($_GET["table"] == "empleados" || !$_GET["table"]) {

            if (!empty($_GET["column"]) && !empty($_GET["param"])) {
                $filtros["column"] = $_GET["column"];
                $filtros["param"] = $_GET["param"];
            }

            if (!empty($_GET["from"]) || !empty($_GET["to"])) {
                $filtros["from"] = $_GET["from"];
                $filtros["to"] = $_GET["to"];
            }

            $result = Time::getPaymentsPaginated($page, $perPage, $_GET["column"], $_GET["param"], $_GET["from"], $_GET["to"]);

            $horas = $result["data"];
            $paginacion = $result["pagination"];

            $urlFilters = [];
            if (!empty($_GET["table"])) $urlFilters["table"] = $_GET["table"];
            if (!empty($_GET["column"])) $urlFilters["column"] = $_GET["column"];
            if (!empty($_GET["param"])) $urlFilters["param"] = $_GET["param"];
            if (!empty($_GET["from"])) $urlFilters["from"] = $_GET["from"];
            if (!empty($_GET["to"])) $urlFilters["to"] = $_GET["to"];
            $urlFilters["per_page"] = $perPage;

            $pagination_links = Paginacion::buildPaginationLinks("/horas/ver", $paginacion, $urlFilters);
        } else if ($_GET["table"] == "clientes") {
            $horas = Time::getAgentPayments($_GET["from"] . " 08:00:00", str_replace("T", " ", $_GET["to"]), $_GET["param"]);
        } else if ($_GET["table"] == "auditar" && in_array(strtoupper($_SESSION["mode"]), ["GOD", "AUDITER"])) {
            $horas = Time::all("si");

            if (!empty($_GET["column"]) && !empty($_GET["param"])) {
                $filtros["column"] = $_GET["column"];
                $filtros["param"] = $_GET["param"];
            }

            $result = Time::allPaginated($page, $perPage, "si", $filtros);
            $horas = $result['data'];
            $paginacion = $result;

            $urlFilters = [];
            $urlFilters["table"] = "auditar";
            if (!empty($_GET["column"])) $urlFilters["column"] = $_GET["column"];
            if (!empty($_GET["param"])) $urlFilters["param"] = $_GET["param"];
            $urlFilters["per_page"] = $perPage;

            $pagination_links = Paginacion::buildPaginationLinks('/horas/ver', $paginacion, $urlFilters);
        } else {
            header("Location:/");
        }
        $router->render("pages/horas/ver", [
            "horas" => $horas,
            "paginacion" => $paginacion,
            "pagination_links" => $pagination_links,
            "filtros" => $_GET,
        ]);
    }


    public static function hora(Router $router)
    {


        $id = validarID();

        $hora = Time::find($id);
        $empleados = Empleados::all();
        $clientes = Clientes::all();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $_POST["horas"]["id"] = $id;

            $horas = new Time($_POST["horas"]);


            $logData = [
                "titulo" => "Hora Actualizada",
                "contenido" => "El usuario " . $_SESSION["name"] . " hizo una actualizacion de las horas de " . $horas->empleado . " en la fecha " . $horas->inicio
            ];

            $log = new Logs($logData);


            $horas->guardar();

            $log->guardar();
            header("Location:/horas/ver?resultado=2");
        }

        $router->render("pages/horas/hora", [
            "hora" => $hora,
            "empleados" => $empleados,
            "clientes" => $clientes

        ]);
    }
    public static function cliente(Router $router)
    {

        $horas = Time::getClientHours($_GET["name"], ($_GET["from"]) ? str_replace("T", " ", $_GET["from"]) : null, str_replace("T", " ", $_GET["to"]));

        $router->render("pages/horas/cliente", [
            "horas" => $horas
        ]);
    }

    public static function aproove()
    {

        $id = validarID();

        Time::aprooveAudition($id);

        header("Location: /horas/ver?table=auditar");
    }

    public static function eliminar()
    {
        $id = validarID();



        $hora = new Time($args = ["id" => $id]);

        $time = Time::find($id);

        $logData = [
            "titulo" => "Hora Eliminada",
            "contenido" => "El usuario " . $_SESSION["name"] . " ha eliminado las horas de " . $time->empleado . " en la fecha " . $time->inicio
        ];

        $hora->eliminar();

        $log = new Logs($logData);

        $log->guardar();

        header("Location: /horas/ver?resultado=3");
    }
}
