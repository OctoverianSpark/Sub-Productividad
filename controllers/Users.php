<?php 




namespace Controllers;

use Models\Logs;
use MVC\Router;
use Models\Users as DB_Users;
class Users{




    public static function index(Router $router){

        $usuarios = DB_Users::all();
        
        $router->render("settings/usuarios/index",[
            "usuarios"=>$usuarios
        ]);
        

    }


    public static function crear(Router $router){

        
        if($_SERVER['REQUEST_METHOD'] === "POST"){


            $user = new DB_Users($_POST);


           
            $logData = [
                "titulo"=>"Usuario Admitido",
                "contenido"=>"El usuario " . $_SESSION["name"] . " ha registrado al usuario " . $user->email . " para su trabajo autorizado en la aplicacion de sub productividad" 
            ];

            $log = new Logs($logData);


            $user->guardar();

            $log->guardar();

            header("Location: /settings/usuarios?resultado=1");

        }


        $router->render("settings/usuarios/crear",[
            "user"=>$user
        ]);

    }
    public static function actualizar(Router $router){


        $id = validarID();

        $user = DB_Users::find($id);
        $logData = [
            "titulo"=>"Usuario Actualizado",
            "contenido"=>"El usuario " . $_SESSION["name"] . " ha actualizado al usuario " . $user->email . " para su trabajo autorizado en la aplicacion de sub productividad" 
        ];

        if($_SERVER["REQUEST_METHOD"]==="POST"){

            $_POST["id"] = $id;

            $user = new DB_Users($_POST);


            $user->guardar();
            $log = new Logs($logData);


            $log->guardar();

            header("Location: /settings/usuarios?resultado=2");


        }
        


        $router->render("settings/usuarios/actualizar",[
            "user"=>$user
        ]);

    }



    public static function eliminar(){


        $userData = DB_Users::find($_GET["id"]);
        $user = new DB_Users($_GET);



        $logData = [
            "titulo"=>"USUARIO ELIMINADO",
            "contenido"=>"EL USUARIO ". $_SESSION["name"] . " HA ELIMINADO AL USUARIO " . $userData->email . " DE LOS PERMISOS DE LA APLICACION DE SUB PRODUCTIVIDAD"
        ];

        $user->eliminar();

        $log = new Logs($logData);

        $log->guardar();


        header("Location: /settings/usuarios?resultado=3");



    }






}




?>