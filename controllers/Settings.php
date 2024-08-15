<?php

namespace Controllers;

use Models\Empleados;
use Models\Clientes;
use MVC\Router;


class Settings{
    
    public static function index(Router $router){

        $clientes = count(Clientes::all());
        $empleados = count(Empleados::all());
        $total=$empleados + $clientes;

        $router->render("settings/index",[
            "clientes"=>$clientes,
            "empleados"=>$empleados,
            "total"=>$total

        ]
        );
    }







}

?>