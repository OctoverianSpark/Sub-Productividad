<?php



namespace Controllers;

use DateTime;
use Models\Clientes;
use Models\Empleados;
use Models\Time;
use MVC\Router;





class Times{



    public static function crear(Router $router){

        $empleados= Empleados::all();
        $clientes= Clientes::all();


        if($_SERVER["REQUEST_METHOD"] === "POST"){

            
            $_POST["horas"]["inicio"]=str_replace("T"," ",$_POST["horas"]["inicio"]);
            $_POST["horas"]["final"]=str_replace("T"," ",$_POST["horas"]["final"]);

            
            $times = new Time($_POST["horas"]);


            $errores = $times->validar();

            if(empty($errores)){




                $times->guardar();



                header("Location: /horas/ver");
                

            }
        }

        $router->render("pages/horas/crear",[
            "empleados"=>$empleados,
            "clientes"=>$clientes,
            "errores"=>$errores
        ]);
    }


    public static function ver(Router $router){

        if($_GET["table"] == "empleados" || !$_GET["table"]){
            $horas = Time::getPayments($_GET["column"],$_GET["param"],str_replace("T"," ",$_GET["from"]),str_replace("T"," ",$_GET["to"]));

        }else if($_GET["table"] == "clientes"){
            $horas = Time::getAgentPayments(($_GET["from"])?str_replace("T"," ",$_GET["from"]): null,str_replace("T"," ",$_GET["to"]),$_GET["param"]);
        }else{
            header("Location:/");
        }

        $router->render("pages/horas/ver",[
            "horas"=>$horas
        ]);
    }


    public static function hora(Router $router){

        
        $id = validarID();

        $hora = Time::find($id);
        $empleados = Empleados::all();
        $clientes = Clientes::all();

        if($_SERVER["REQUEST_METHOD"] === "POST"){
            
            $_POST["horas"]["id"] = $id;

            $horas = new Time($_POST["horas"]);



            $horas->actualizar();

            sleep(2);
            header("Location : /horas/ver/hora?id=$id");

        }

        $router->render("pages/horas/hora",[
            "hora"=>$hora,
            "empleados"=>$empleados,
            "clientes"=>$clientes
            
        ]);
    }
    public static function cliente(Router $router){
       
        $horas = Time::getClientHours($_GET["name"],($_GET["from"])?str_replace("T"," ",$_GET["from"]): null,str_replace("T"," ",$_GET["to"]));

        $router->render("pages/horas/cliente",[
            "horas"=>$horas
        ]);
    }





}






















?>