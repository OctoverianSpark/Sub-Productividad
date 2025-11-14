<?php

namespace Controllers;



use MVC\Router;
use Models\Clientes;
use Models\Logs;

class Clients
{





    public static function index(Router $router)
    {

        $clientes = Clientes::filter($_GET["column"], $_GET["param"]);
        $columnas = Clientes::getColumns();
        $router->render("settings/clientes/index", [
            "clientes" => $clientes,
            "columnas" => $columnas
        ]);
    }

    public static function crear(Router $router)
    {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $cliente = new Clientes($_POST["clientes"]);
            $errores = $cliente->validar();

            if (empty($errores)) {
                $cliente->guardar();
                header("Location: /settings/clientes?resultado=1");
            }
        }

        $logData = [
            "titulo" => "cliente creado",
            "contenido" => "El usuario " . $_SESSION["name"] . " ha creado al cliente " . $cliente->nombre . " " . $cliente->apellido . "",
        ];

        $log = new Logs($logData);
        $log->guardar();



        $router->render("settings/clientes/crear", [
            "cliente" => $cliente,
            "errores" => $errores
        ]);
    }
    public static function actualizar(Router $router)
    {

        $id = validarID();
        $cliente = Clientes::find($id);

        $logData = [
            "titulo" => "cliente actualizado",
            "contenido" => "El usuario " . $_SESSION["name"] . " ha actulizado al cliente " . $cliente->nombre . " " . $cliente->apellido . "",
        ];
        $log = new Logs($logData);

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $_POST["clientes"]["id"] = $id;

            $cliente = new Clientes($_POST["clientes"]);

            $errores = $cliente->validar();



            if (empty($errores)) {
                $log->guardar();
                $cliente->guardar();
                header("Location: /settings/clientes?resultado=2");
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

        $logData = [
            "titulo" => "cliente eliminado",
            "contenido" => "El usuario " . $_SESSION["name"] . " ha eliminado al cliente " . $cliente->nombre . " " . $cliente->apellido . "",
        ];

        $log = new Logs($logData);
        $log->guardar();


        $cliente->eliminar();

        header("Location: /settings/clientes?resultado=3");
    }
}
