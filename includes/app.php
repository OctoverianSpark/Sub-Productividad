<?php 

require "funciones.php";
require "config/databases.php";
require __DIR__ . "/../vendor/autoload.php";

use Models\DESC;
use Models\Logs;
use Models\RH;
use Models\Time;
use Models\Users;

date_default_timezone_set("America/Bogota");
setlocale(LC_ALL,"es_CO.Unicode","esp");
header('Content-Type: text/html; charset=UTF-8');



$subpDB = conectarDB("subp");

RH::setDb($subpDB);
DESC::setDb($subpDB);
Time::setDb($subpDB);
Logs::setDb($subpDB);
Users::setDb($subpDB);





?>