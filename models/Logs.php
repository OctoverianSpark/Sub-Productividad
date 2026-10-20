<?php

namespace Models;

class Logs extends BaseModel
{
    protected static $tabla = "logs";
    protected static $columnasDB = ["id", "titulo", "contenido", "fecha"];

    public $titulo;
    public $contenido;
    public $fecha;

    public function __construct($args = [])
    {
        $this->id = null;
        $this->fecha = date("Y-m-d H:i:s");
        $this->titulo = $args["titulo"] ?? "";
        $this->contenido = $args["contenido"] ?? "";
    }

    // Registra un evento a nombre del usuario en sesion
    public static function registrar(string $titulo, string $contenido): void
    {
        (new static(["titulo" => $titulo, "contenido" => $contenido]))->guardar();
    }
}
