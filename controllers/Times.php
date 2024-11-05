<?php



namespace Controllers;

use DateTime;
use Models\Clientes;
use Models\Empleados;
use Models\Logs;
use Models\Time;
use MVC\Router;





class Times{


    public static function crear(Router $router){

        $empleados= Empleados::all();
        $clientes= Clientes::all();


        if($_SERVER["REQUEST_METHOD"] === "POST"){

            $errores = [];

            foreach($_POST["horas"] as $hora){
                $hora["inicio"] = str_replace("T"," ",$hora["inicio"]);
                $hora["final"] = str_replace("T"," ",$hora["inicio"]);
    
                
                $times = new Time($hora);

                $logData = [
                    "titulo"=>"Hora Cargada",
                    "contenido"=>"El usuario " . $_SESSION["name"] . " cargo las horas de " . $times->empleado . " en la fecha " . $times->inicio
                ];

                $log = new Logs($logData);

                $errores = $times->validar();
    
                if(empty($errores)){
    
    
    
    
                    $times->guardar();
                    
                    $log->guardar();
    
                    header("Location: /horas/ver?resultado=1");
                    
    
                }

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
            $horas = Time::getPayments($_GET["column"],$_GET["param"],$_GET["from"],$_GET["to"]);

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

            
            $logData = [
                "titulo"=>"Hora Actualizada",
                "contenido"=>"El usuario " . $_SESSION["name"] . " hizo una actualizacion de las horas de " . $horas->empleado . " en la fecha " . $horas->inicio
            ];

            $log = new Logs($logData);


            $horas->guardar();

            $log->guardar();
            header("Location:/horas/ver?resultado=2");

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


    public static function eliminar(){
        $id = validarID();

    

        $hora = new Time($args = ["id" => $id]);

        
        $hora->eliminar();
        
        $logData = [
            "titulo"=>"Hora Eliminada",
            "contenido"=>"El usuario " . $_SESSION["name"] . " ha eliminado las horas de " . $times->empleado . " en la fecha " . $times->inicio
        ];

        $log = new Logs($logData);

        $log->guardar();

        header("Location: /horas/ver?resultado=3");

    }




}






















?>