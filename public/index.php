<?php
require_once  __DIR__."/../includes/app.php";

use Controllers\Clients;
use Controllers\Employees;
use MVC\Router;

use Controllers\Login;
use Controllers\Pages;
use Controllers\Settings;
use Controllers\Times;

$router = new Router;


/* Paginas */
$router->get("/",[Pages::class,"index"]);
$router->get("/export",[Pages::class,"export"]);



/* Horas Extras */
$router->get("/horas/ver",[Times::class,"ver"]);


$router->get("/horas/ver/hora",[Times::class,"hora"]);
$router->post("/horas/ver/hora",[Times::class,"hora"]);
$router->get("/horas/ver/hora/eliminar",[Times::class,"eliminar"]);
$router->get("/horas/ver/cliente",[Times::class,"cliente"]);

$router->get("/horas/registrar",[Times::class,"crear"]);
$router->post("/horas/registrar",[Times::class,"crear"]);


/* Settings */
$router->get("/settings",[Settings::class,"index"]);

/* Empleados */
$router->get("/settings/empleados",[Employees::class,"index"]);
$router->post("/settings/empleados",[Employees::class,"index"]);

$router->get("/settings/empleados/crear",[Employees::class,"crear"]);
$router->post("/settings/empleados/crear",[Employees::class,"crear"]);

$router->get("/settings/empleados/actualizar",[Employees::class,"actualizar"]);
$router->post("/settings/empleados/actualizar",[Employees::class,"actualizar"]);

$router->get("/settings/empleados/eliminar",[Employees::class,"eliminar"]);
$router->post("/settings/empleados/eliminar",[Employees::class,"eliminar"]);


/* Clientes */
$router->get("/settings/clientes",[Clients::class,"index"]);
$router->post("/settings/clientes",[Clients::class,"index"]);

$router->get("/settings/clientes/crear",[Clients::class,"crear"]);
$router->post("/settings/clientes/crear",[Clients::class,"crear"]);

$router->get("/settings/clientes/actualizar",[Clients::class,"actualizar"]);
$router->post("/settings/clientes/actualizar",[Clients::class,"actualizar"]);

$router->get("/settings/clientes/eliminar",[Clients::class,"eliminar"]);
$router->post("/settings/clientes/eliminar",[Clients::class,"eliminar"]);



$router->get("/login",[Login::class,"login"]);
$router->post("/login",[Login::class,"login"]);
$router->get("/redirect",[Login::class,"redirect"]);
$router->get("/logout",[Login::class,"logout"]);















$router->comprobarRutas()


?>