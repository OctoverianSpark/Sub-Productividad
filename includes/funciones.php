<?php

use Models\Tecnicos;

define("CARPETA_IMAGENES",$_SERVER["DOCUMENT_ROOT"]."/referencias");
define("CARPETA_SRC",$_SERVER["DOCUMENT_ROOT"]."/blog");

function debuguear($dump){
    
    echo '<pre>';
    var_dump($dump);
    echo '</pre>';
    exit;
    

}


function s($html){
    $s = htmlspecialchars($html);
    return $s;
}


function mostrarNotificacion($resultado){
    $mensaje = "";
    switch ($resultado) {
        case 1:
            $mensaje = "Los Datos se han registrado correctamente";
            break;
        case 2:
            $mensaje = "Los datos fueron actualizados correctamente";
            break;
        case 3:
            $mensaje = "Los datos fueron eliminados correctamente";
            break;
        case 4:
            $mensaje = "<i class='bx bxs-message-alt-check'></i> Exportacion guardada !!!";
        default:
            
            break;
    }
    return $mensaje;
    
}
function validarID(){
    $id = $_GET["id"];

    $id = filter_var($id,FILTER_VALIDATE_INT);

    if(!$id){
        header("Location: /");
    }else{
        return $id;
    }
}



function estaLogueado(){
    if(is_null($_SESSION["login"])){
        
        return false;
    }else{
        return true;
    }

}
