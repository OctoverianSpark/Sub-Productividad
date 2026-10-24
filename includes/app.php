<?php

require __DIR__ . "/../vendor/autoload.php";
require "funciones.php";
require "config/databases.php";

use Dotenv\Dotenv;
use Models\BaseModel;
use MVC\Config;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();
$dotenv->required([
    "APP_URL",
    "DB_HOST", "DB_USER", "DB_PORT",
    "AD_ACCOUNT_SUFFIX", "AD_DOMAIN_CONTROLLERS", "AD_BASE_DN", "AD_ADMIN_USERNAME", "AD_ADMIN_PASSWORD",
])->notEmpty();
$dotenv->required("DB_PASS");

// APP_URL debe ser solo el dominio con protocolo, ej: https://subproductividad.asistentevirtualsas.com
if (!preg_match('#^https?://[^/\s]+/?$#', $_ENV["APP_URL"])) {
    throw new RuntimeException("APP_URL en .env debe ser solo el dominio con http:// o https:// (sin /redirect ni otras rutas)");
}

date_default_timezone_set(Config::get("app.zona_horaria", "America/Bogota"));
setlocale(LC_ALL, "es_CO.UTF-8", "es_CO", "esp");
header("Content-Type: text/html; charset=UTF-8");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");

// Los errores se registran en el log del servidor; al usuario no se le muestran detalles internos
ini_set("display_errors", "0");
set_exception_handler(function (Throwable $e) {
    error_log($e);
    if (!headers_sent()) http_response_code(500);
    echo "Ocurrio un error inesperado. Intenta de nuevo o contacta al administrador.";
});

BaseModel::setDB(conectarDB($_ENV["DB_NAME"] ?? "subp"));
