<?php

namespace Controllers;

use MVC\Router;
use Models\Clientes;
use Models\Logs;

class Clients
{
    public static function index(Router $router)
    {
        $router->render("settings/clientes/index", [
            "clientes" => Clientes::filter($_GET["column"] ?? null, $_GET["param"] ?? null),
            "columnas" => Clientes::getColumns(),
        ]);
    }

    public static function crear(Router $router)
    {
        $cliente = new Clientes();
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = (array) ($_POST["clientes"] ?? []);
            unset($datos["id"]);
            $cliente = new Clientes($datos);
            $errores = $cliente->validar();

            if (empty($errores)) {
                $cliente->guardar();
                Logs::registrar("cliente creado", "El usuario " . $_SESSION["name"] . " ha creado al cliente " . $cliente->nombreCompleto());
                redirigir("/settings/clientes?resultado=1");
            }
        }

        $router->render("settings/clientes/crear", [
            "cliente" => $cliente,
            "errores" => $errores
        ]);
    }

    public static function actualizar(Router $router)
    {
        $id = validarID();
        $cliente = Clientes::find($id);
        if (!$cliente) {
            redirigir("/settings/clientes");
        }
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = (array) ($_POST["clientes"] ?? []);
            $datos["id"] = $id;
            $cliente = new Clientes($datos);
            $errores = $cliente->validar();

            if (empty($errores)) {
                $cliente->guardar();
                Logs::registrar("cliente actualizado", "El usuario " . $_SESSION["name"] . " ha actualizado al cliente " . $cliente->nombreCompleto());
                redirigir("/settings/clientes?resultado=2");
            }
        }

        $router->render("settings/clientes/actualizar", [
            "cliente" => $cliente,
            "errores" => $errores
        ]);
    }

    public static function eliminar()
    {
        $id = validarID();
        $cliente = Clientes::find($id);

        if ($cliente) {
            $cliente->eliminar();
            Logs::registrar("cliente eliminado", "El usuario " . $_SESSION["name"] . " ha eliminado al cliente " . $cliente->nombreCompleto());
        }

        redirigir("/settings/clientes?resultado=3");
    }
}
