<?php 

require "funciones.php";
require "config/databases.php";
require __DIR__ . "/../vendor/autoload.php";

use Models\BaseModel;
use Models\Logs;
use Models\Time;
use Models\Users;
use Dotenv\Dotenv;

date_default_timezone_set("America/Bogota");
setlocale(LC_ALL,"es_CO.Unicode","esp");
header('Content-Type: text/html; charset=UTF-8');

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();


$subpDB = conectarDB("subp");
Time::setDb($subpDB);
Logs::setDb($subpDB);
Users::setDb($subpDB);
BaseModel::setDB($subpDB);





?>