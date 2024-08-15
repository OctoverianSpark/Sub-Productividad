<?php

namespace Controllers;



use MVC\Router;
use Models\Clientes;

class Clients{




    
    public static function index(Router $router){

        $clientes = Clientes::filter($_GET["column"],$_GET["param"]);
        $columnas = Clientes::getColumns();
        $router->render("settings/clientes/index",[
            "clientes"=>$clientes,
            "columnas"=>$columnas
        ]);

    }

    public static function crear(Router $router){

        if($_SERVER["REQUEST_METHOD"] === "POST"){


            $cliente = new Clientes($_POST["clientes"]);



            $errores = $cliente->validar();



            if(empty($errores)){



                $cliente->guardar();



                header("Location: /settings/clientes?resultado=1");

            }


        }
        


        $router->render("settings/clientes/crear",[
            "cliente"=>$cliente,
            "errores"=>$errores
        ]);

    }
    public static function actualizar(Router $router){

        $id = validarID();
        $cliente = Clientes::find($id);

        if($_SERVER["REQUEST_METHOD"] === "POST"){

            $_POST["clientes"]["id"]=$id;

            $cliente = new Clientes($_POST["clientes"]);



            $errores = $cliente->validar();



            if(empty($errores)){



                $cliente->guardar();



                header("Location: /settings/clientes?resultado=2");

            }


        }
        


        $router->render("settings/clientes/actualizar",[
            "cliente"=>$cliente,
            "errores"=>$errores
        ]);

    }

    
    public static function eliminar(){


        $id = validarID();

        $args["id"] = $id;

        $cliente = new Clientes($args);


        $cliente->eliminar();

        header("Location: /settings/clientes?resultado=3");



    }

}







?>