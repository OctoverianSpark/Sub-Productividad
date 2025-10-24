<?php

namespace Controllers;

use Models\Empleados;
use Models\Paginacion;
use MVC\Router;



class Employees
{

    public static function index(Router $router)
    {

        $page = $_GET["page"] ?? 1;
        $perPage = $_GET["per_page"] ?? 20;

        $filtros = [
            "column" => $_GET["column"] ?? "",
            "param" => $_GET["param"] ?? "",
        ];

        $paginacion = Empleados::paginarConFiltros($page, $perPage, $filtros);
        $empleados = $paginacion["data"];
        $columnas = Empleados::getColumns();

        $baseUrl = "/settings/empleados";
        $paginationLinks = Paginacion::buildPaginationLinks($baseUrl, $paginacion, $filtros);

        $router->render("settings/empleados/index", [
            "empleados" => $empleados,
            "columnas" => $columnas,
            "paginacion" => $paginacion,
            "pagination_links" => $paginationLinks,
            "filtros" => $filtros

        ]);
    }
    

    public static function crear(Router $router)
    {
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $empleados = new Empleados($_POST["empleados"]);
            $errores = $empleados->validar();

            if (empty($errores)) {
                $empleados->guardar();
                header("Location: /settings/empleados?resultado=1");
            }
        }

        $router->render("settings/empleados/crear", [
            "empleados" => $empleados,
            "errores" => $errores
        ]);
    }

    public static function actualizar(Router $router)
    {
        $id = validarID();
        $empleados = Empleados::find($id);
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $_POST["empleados"]["id"] = $id;
            $empleados = new Empleados($_POST["empleados"]);
            $errores = $empleados->validar();

            if (empty($errores)) {
                $empleados->guardar();
                header("Location: /settings/empleados?resultado=2");
            }
        }

        $router->render("settings/empleados/actualizar", [
            "empleados" => $empleados,
            "errores" => $errores
        ]);
    }


    public static function eliminar()
    {
        $id = validarID();
        $args["id"] = $id;
        $empleados = new Empleados($args);

        $empleados->eliminar();
        header("Location: /settings/empleados?resultado=3");
    }
}
