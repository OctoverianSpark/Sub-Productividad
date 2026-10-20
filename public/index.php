<?php
require_once __DIR__ . "/../includes/app.php";

use Controllers\Clients;
use Controllers\Employees;
use Controllers\Login;
use Controllers\Pages;
use Controllers\Settings;
use Controllers\Times;
use Controllers\Users;
use MVC\Router;

$router = new Router;

// El tercer parametro es el permiso requerido (ver "roles.permisos" en config/parametros.json)

/* Paginas */
$router->get("/", [Pages::class, "index"]);
$router->get("/export", [Pages::class, "export"], "exportar_horas");
$router->post("/aproove", [Times::class, "aproove"], "auditar");

/* Horas Extras */
$router->get("/horas/ver", [Times::class, "ver"], "gestionar_horas");
$router->get("/horas/ver/hora", [Times::class, "hora"], "gestionar_horas");
$router->post("/horas/ver/hora", [Times::class, "hora"], "gestionar_horas");
$router->post("/horas/ver/hora/eliminar", [Times::class, "eliminar"], "eliminar_horas");
$router->get("/horas/ver/cliente", [Times::class, "cliente"], "gestionar_horas");

$router->get("/horas/registrar", [Times::class, "crear"], "gestionar_horas");
$router->post("/horas/registrar", [Times::class, "crear"], "gestionar_horas");

/* Settings */
$router->get("/settings", [Settings::class, "index"], "configuracion");

/* Empleados */
$router->get("/settings/empleados", [Employees::class, "index"], "configuracion");
$router->get("/settings/empleados/crear", [Employees::class, "crear"], "configuracion");
$router->post("/settings/empleados/crear", [Employees::class, "crear"], "configuracion");
$router->get("/settings/empleados/actualizar", [Employees::class, "actualizar"], "configuracion");
$router->post("/settings/empleados/actualizar", [Employees::class, "actualizar"], "configuracion");
$router->post("/settings/empleados/eliminar", [Employees::class, "eliminar"], "configuracion");

/* Clientes */
$router->get("/settings/clientes", [Clients::class, "index"], "configuracion");
$router->get("/settings/clientes/crear", [Clients::class, "crear"], "configuracion");
$router->post("/settings/clientes/crear", [Clients::class, "crear"], "configuracion");
$router->get("/settings/clientes/actualizar", [Clients::class, "actualizar"], "configuracion");
$router->post("/settings/clientes/actualizar", [Clients::class, "actualizar"], "configuracion");
$router->post("/settings/clientes/eliminar", [Clients::class, "eliminar"], "configuracion");

/* Usuarios */
$router->get("/settings/usuarios", [Users::class, "index"], "usuarios");
$router->get("/settings/usuarios/crear", [Users::class, "crear"], "usuarios");
$router->post("/settings/usuarios/crear", [Users::class, "crear"], "usuarios");
$router->get("/settings/usuarios/actualizar", [Users::class, "actualizar"], "usuarios");
$router->post("/settings/usuarios/actualizar", [Users::class, "actualizar"], "usuarios");
$router->post("/settings/usuarios/eliminar", [Users::class, "eliminar"], "usuarios");

/* Login */
$router->get("/login", [Login::class, "login"]);
$router->post("/login", [Login::class, "login"]);
$router->get("/redirect", [Login::class, "redirect"]);
$router->get("/logout", [Login::class, "logout"]);

$router->comprobarRutas();
