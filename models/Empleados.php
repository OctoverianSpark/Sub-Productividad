<?php



namespace Models;



class Empleados extends RH{


    protected static $columnasDB = ["id","nombre","apellido","tipo_documento","documento","sede","modalidad","cargo","salario"];

    protected static $tabla = "empleados";


    public $id,$nombre,$apellido,$tipo_documento,$documento,$sede,$modalidad,$cargo,$salario;

    public function __construct($args=[]){


        $this->id = $args["id"]?? null;

        $this->nombre = $args["nombre"] ?? "";
        $this->apellido = $args["apellido"] ?? "";
        $this->tipo_documento = $args["tipo_documento"] ?? "";
        $this->documento = $args["documento"] ?? "";
        $this->sede = $args["sede"] ?? "";
        $this->modalidad = $args["modalidad"] ?? "";
        $this->cargo = $args["cargo"] ?? "";
        $this->salario = $args["salario"] ?? null;


    }


    public function validar(){


        if(!$this->nombre){
            self::$errores[] = "El nombre es obligatorio";
        }
        if(!$this->apellido){
            self::$errores[] = "El apellido es obligatorio";
        }
        if(!$this->documento){
            self::$errores[] = "El documento es obligatorio";
        }
        if(!$this->tipo_documento){
            self::$errores[] = "El tipo de documento es obligatorio";
        }
        if(!$this->cargo){
            self::$errores[] = "El cargo es obligatorio";
        }
        if($this->salario<=0){
            self::$errores[] = "El salario es obligatorio";
        }


        return self::$errores;
        
        


    }

    
    public static function getByFullName($name){

        $query = "SELECT * FROM " . static::$tabla . " WHERE CONCAT(nombre,' ',apellido) = '$name'";


        $resultado = self::consultarSQL($query);
        return array_shift ( $resultado );




    }

}

?>