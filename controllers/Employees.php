<?php

namespace Controllers;

use Models\Empleados;
use Models\Logs;
use Models\Paginacion;
use MVC\Router;

class Employees
{
    public static function index(Router $router)
    {
        $filtros = [
            "column" => $_GET["column"] ?? "",
            "param" => $_GET["param"] ?? "",
        ];

        $paginacion = Empleados::paginarConFiltros($_GET["page"] ?? 1, $_GET["per_page"] ?? null, $filtros);

        $router->render("settings/empleados/index", [
            "empleados" => $paginacion["data"],
            "columnas" => Empleados::getColumns(),
            "paginacion" => $paginacion,
            "pagination_links" => Paginacion::buildPaginationLinks("/settings/empleados", $paginacion, $filtros),
            "filtros" => $filtros
        ]);
    }

    public static function crear(Router $router)
    {
        $empleados = new Empleados();
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = (array) ($_POST["empleados"] ?? []);
            unset($datos["id"]);
            $empleados = new Empleados($datos);
            $errores = $empleados->validar();

            if (empty($errores)) {
                $empleados->guardar();
                Logs::registrar("Empleado Creado", "El usuario " . $_SESSION["name"] . " ha agregado al empleado " . $empleados->nombreCompleto());
                redirigir("/settings/empleados?resultado=1");
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
        if (!$empleados) {
            redirigir("/settings/empleados");
        }
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = (array) ($_POST["empleados"] ?? []);
            $datos["id"] = $id;
            $empleados = new Empleados($datos);
            $errores = $empleados->validar();

            if (empty($errores)) {
                $empleados->guardar();
                Logs::registrar("Empleado actualizado", "El usuario " . $_SESSION["name"] . " ha actualizado al empleado " . $empleados->nombreCompleto());
                redirigir("/settings/empleados?resultado=2");
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
        $empleados = Empleados::find($id);

        if ($empleados) {
            $empleados->eliminar();
            Logs::registrar("Empleado eliminado", "El usuario " . $_SESSION["name"] . " ha eliminado al empleado " . $empleados->nombreCompleto());
        }

        redirigir("/settings/empleados?resultado=3");
    }
}
