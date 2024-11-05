<?php


namespace Models;




class RH{


    protected static $db;

    protected static $tabla= "";

    protected static $columnasDB = [];

    public static $errores = [];


    public static function setDb($db){
        self::$db = $db;
    }
    public static function getColumns(){

        $columnas = [];

        foreach(static::$columnasDB as $columna){
            if($columna === "id") continue;
            $columnas[]=$columna;
        }
        

        return $columnas;


    }


    public static function get($limit){
        $query = "SELECT * FROM " . static::$tabla . " LIMIT ". $limit;

        $resultado = self::consultarSQL($query);

        return $resultado;
    }

    public static function consultarSQL($query){

        //Consultar
        $resultado = self::$db->query($query);


        //Iterar
        $array = [];
        while ($registro = $resultado->fetch_assoc()) {

            $array[] = static::crearObjeto($registro);

        }

        //Liberar
        $resultado->free();

        //Retornar
        return $array;



    }

    
    public static function find($id){
        $query = "SELECT * FROM " . static::$tabla ." WHERE id = $id";

        $resultado = self::consultarSQL($query);

        return array_shift($resultado);
    }

    protected static function crearObjeto($registro){
        $objeto = new static;
        

        foreach ($registro as $key => $value) {
            if(property_exists( $objeto, $key ) ){
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }


    public function guardar(){

        if (!$this->id) {
            $this->crear();
            
        }else{
            $this->actualizar();
        }

        return true;


    }
    public static function all(){
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY nombre ASC";
        $resultado = self::consultarSQL($query);

        return $resultado;
    }


    public function crear(){

        //Sanitizar
        $atributos = $this->sanitizarAtributos();
        
 

        //Insercion
        $query = "INSERT INTO ". static::$tabla ." ("  ;
        $query .= join(", ",array_keys($atributos));
        $query .= ")VALUES ('";
        $query .= strtolower(join("' , '",array_values($atributos)));
        $query.= "')";

        $resultado = self::$db->query($query);

        return $resultado;
    }
    public function actualizar(){
        
        $atributos = $this->sanitizarAtributos();

        $valores = [];

        foreach($atributos as $key=>$value){
            if($atributos[$key] === "" || $atributos[$key] === null) continue;
            if($key === "creado") continue;
            $valores[] = "$key='$value'";
        }

        $query = "UPDATE ". static::$tabla." SET "  ;
        $query.= strtolower(join(",",$valores));
        $query.= " WHERE id = '". self::$db->escape_string($this->id) . "'";
        $query.= " LIMIT 1";

        $resultado = self::$db->query($query);
        return $resultado;


    }




    public function eliminar(){
        $query = "DELETE FROM " . static::$tabla . " WHERE id = $this->id";

        self::$db->query($query);
    }


    public static function filter($column,$param){


        if($column && $param){
            $query = "SELECT * FROM " . static::$tabla . " WHERE $column LIKE '%$param%' ORDER BY nombre ASC";
            $resultado = self::consultarSQL($query);

        }else{
            $resultado = static::all();
        }

        return $resultado;



    }
    public function atributos(){
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === "id") continue;
            $atributos[$columna] = $this->$columna;
            # code...
        }
        return $atributos;
    }

    public function sanitizarAtributos(){
        $atributos = $this->atributos();


        $sanitizado= [];

        foreach($atributos as $key => $value){
            
            $sanitizado[$key] = self::$db->escape_string($value);
          

        }
        return $sanitizado;
    }

    

}




?>