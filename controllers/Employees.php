<?php

namespace Controllers;

use Models\Empleados;
use MVC\Router;



class Employees{

    
    public static function index(Router $router){

        $empleados = Empleados::filter($_GET["column"],$_GET["param"]);
        $columnas = Empleados::getColumns();

        $router->render("settings/empleados/index",[
            "empleados"=>$empleados,
            "columnas"=>$columnas
        ]);

    }

    public static function crear(Router $router){

            

        $errores= [];
        if($_SERVER["REQUEST_METHOD"] === "POST"){   

            $empleados = new Empleados($_POST["empleados"]);


            $errores = $empleados->validar();


            if(empty($errores)){





                $empleados->guardar();

                header("Location: /settings/empleados?resultado=1");

            }else{
            }
    
    
    
        }
        $router->render("settings/empleados/crear",[
            "empleados"=>$empleados,
            "errores"=>$errores
        ]);

    }
    public static function actualizar(Router $router){


        $id = validarID();

        $empleados = Empleados::find($id);

        $errores= [];
        if($_SERVER["REQUEST_METHOD"] === "POST"){   

            $_POST["empleados"]["id"] = $id;

            $empleados = new Empleados($_POST["empleados"]);


            $errores = $empleados->validar();


            if(empty($errores)){





                $empleados->guardar();

                header("Location: /settings/empleados?resultado=2");

            }else{
            }
    
    
    
        }
        $router->render("settings/empleados/actualizar",[
            "empleados"=>$empleados,
            "errores"=>$errores
        ]);

    }

    
    public static function eliminar(){


        $id = validarID();

        $args["id"] = $id;

        $empleados = new Empleados($args);


        $empleados->eliminar();

        header("Location: /settings/empleados?resultado=3");



    }

}