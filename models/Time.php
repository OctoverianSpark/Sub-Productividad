<?php 



namespace Models;

use DateTime;
use Models\Empleados;



class Time{

    protected static $db;

    protected static $tabla= "horas_extras";

    protected static $columnasDB = ["id","empleado","cliente","inicio","almuerzo","final","diurnas_ordinarias","nocturnas_ordinarias","diurnas_extras","nocturnas_extras","cena","taxi", "comentarios"];

    public static $errores = [];

    public $id,$empleado,$cliente,$inicio,$almuerzo,$final,$diurnas_ordinarias,$nocturnas_ordinarias,$diurnas_extras,$nocturnas_extras,$cena,$taxi,$comentarios;



    public function __construct($args=[]){
        $this->id = $args["id"] ?? null;
        $this->empleado = strtolower($args["empleado"]) ?? "";
        $this->cliente = strtolower($args["cliente"]) ?? "";
        $this->inicio = $args["inicio"] ?? "";
        $this->almuerzo = $args["almuerzo"] ?? "";
        $this->final = $args["final"] ?? "";
        $this->diurnas_ordinarias = $args["diurnas_ordinarias"] ?? 0;
        $this->nocturnas_ordinarias = $args["nocturnas_ordinarias"] ?? 0;
        $this->diurnas_extras = $args["diurnas_extras"] ?? 0;
        $this->nocturnas_extras = $args["nocturnas_extras"] ?? 0;
        $this->cena = $args["cena"] ?? "no";
        $this->taxi = $args["taxi"] ?? "no";
        $this->comentarios = strtolower($args["comentarios"]) ?? "";
    }



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

    public static function findByDate($range1,$range2){
        
        $query = "SELECT * FROM " . static::$tabla . " WHERE ";
        $query.= "(DATE(inicio) BETWEEN '$range1' AND '$range2') OR";
        $query.= "(DATE(final) BETWEEN '$range1' AND '$range2')";
        $resultado = self::consultarSQL($query);
        return $resultado;



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
                $objeto->$key = strtoupper($value);
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
        $query = "SELECT * FROM " . static::$tabla ;

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

        $resultado = self::$db->query($query);
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
 



    public function eliminar(){
        $query = "DELETE FROM " . static::$tabla . " WHERE id = $this->id";
        self::$db->query($query);
    }


    public static function filter($column,$param){


        if($column && $param){
            $query = "SELECT * FROM " . static::$tabla . " WHERE $column LIKE '%$param%'";
            $resultado = self::consultarSQL($query);

        }else{
            $resultado = static::all();
        }

        return $resultado;
}


    public function validar(){


        if(!$this->inicio){
            static::$errores[]="Inicio: No puede quedar vacio";
        }
        if(!$this->final){
            static::$errores[] = "Final: No puede quedar vacio";
        }
        if($this->diurnas_extras < 0){
            static::$errores[] = "Diurnas Extras: Valor Negativo no valido";
        }
        if($this->nocturnas_extras < 0){
            static::$errores[] = "Nocturnas Extras: Valor Negativo no valido";

        }
        if($this->diurnas_ordinarias < 0){
            static::$errores[] = "Diurnas Ordinarias: Valor Negativo no valido";

        }
        if($this->nocturnas_ordinarias < 0){
            static::$errores[] = "Nocturnas Ordinarias: Valor Negativo no valido";
        }
        if($this->almuerzo == ""){
            static::$errores[]= "Almuerzo: Coloca un valor en el almuerzo";
        }
        

        return static::$errores;


    }


    public static function getClientHours($name,$from=null,$to=null){

        $query = "SELECT * FROM " . self::$tabla . " WHERE cliente = '$name'";

        if($from && $to){
            $query .= "AND (inicio BETWEEN '$from' AND '$to') OR";
            $query .= "(final BETWEEN '$from' AND '$to')";
            $query .= "AND NOT cliente = 'administrativo'";
        }

        $resultado = self::consultarSQL($query);

        return $resultado;


    }


    public static function getPayments($column = null,$param = null,$from=null,$to=null){

        if($from && $to){
            $times = static::findByDate($from,$to);
        }else{

            $times = static::filter($column,$param);
        }
        

    

        $resultado = [];
        $i = 0;
        foreach($times as $time){

            $inicio = date_timestamp_get(new DateTime($time->inicio));
            
            $empleado = Empleados::getByFullName(strtolower($time->empleado));

            $salarioHora = $empleado->salario / (8 * 30)  ;

            if($empleado->modalidad == "oficina" && $empleado->sede == "colombia"){
                

                $diurnasExtra = round($salarioHora + ($salarioHora * .25));
                $nocturnasExtra = round($salarioHora + ($salarioHora * .75));
                $horaDominical = round($salarioHora + ($salarioHora * .75));
                $diurnasExtraDominicales = round($salarioHora + $salarioHora);
                $nocturnasExtraDominicales = round($salarioHora + ($salarioHora * 1.5));
                $moneda = "COP";
                
                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday")? ($time->diurnas_ordinarias * $horaDominical) + ($time->diurnas_extras * $diurnasExtraDominicales):$time->diurnas_extras * $diurnasExtra;
                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday")? ($time->nocturnas_ordinarias * $horaDominical) + ($time->nocturnas_extras *$nocturnasExtraDominicales):$time->nocturnas_extras * $nocturnasExtra;

    

            }else if($empleado->modalidad =="hogar"){
                $extras = 2.5;
                $moneda = "Dolares";
                
                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday")? ($time->diurnas_ordinarias * $extras) + ($time->diurnas_extras * $extras):$time->diurnas_extras * $extras;

                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday")? ($time->nocturnas_ordinarias * $extras) + ($time->nocturnas_extras * $extras): $time->nocturnas_extras * $extras;

            }else if($empleado->modalidad == "oficina" && $empleado->sede ="venezuela"){

                
                $extras = 2;
                $moneda = "Dolares";

                
                $resultado[$i]["diurnas_monto"] = (getdate($inicio)["weekday"] == "Sunday")? ($time->diurnas_ordinarias * $extras) + ($time->diurnas_extras * $extras):$time->diurnas_extras * $extras;

                $resultado[$i]["nocturnas_monto"] = (getdate($inicio)["weekday"] == "Sunday")? ($time->nocturnas_ordinarias * $extras) + ($time->nocturnas_extras * $extras): $time->nocturnas_extras * $extras;
    


            }
            

            foreach($time as $key=>$value){

                $resultado[$i][$key] = $value;



            }
            

            
            $resultado[$i]["moneda"] = $moneda;

            $i++;

        }
    
        return $resultado;









    }
    public static function getAgentPayments($from=null,$to=null,$param = null){


        if(!is_null($param)){
            $clientes = Clientes::getByFullName($param);
        }else{
            $clientes = Clientes::all();

        }


        $resultado = [];
        $i = 0;
        foreach($clientes as $cliente){


            

            


            $diurnasExtra = 5.5;
            $nocturnasExtra =6;
            $diurnasExtraDominicales = 6.5;
            $nocturnasExtraDominicales = 7;

            $resultado[$i]["cliente"] = $cliente->nombre ." " . $cliente->apellido;
            $resultado[$i]["diurnas"] = 0;
            $resultado[$i]["diurnas_domingo"] = 0;
            $resultado[$i]["nocturnas"] = 0;
            $resultado[$i]["nocturnas_domingo"] = 0;
            $resultado[$i]["diurnas_monto"] = 0;
            $resultado[$i]["nocturnas_monto"] = 0;

            
            $query = "SELECT * FROM ".static::$tabla . " WHERE cliente = '" .$resultado[$i]["cliente"]. "' AND NOT cliente = 'administrativo'";
            


            if(!is_null($from) && !is_null($to)){
                $query.= " AND ((inicio between '$from' and '$to') OR";
                $query.= " (final between '$from' and '$to'))";
            }
            
            $times = self::consultarSQL($query);
            
            

            if(count($times)<=0) continue;
            foreach($times as $time){

                
                $inicio = date_timestamp_get(new DateTime($time->inicio));
                $resultado[$i]["diurnas"] +=  (getdate($inicio)["weekday"] != "Sunday")? $time->diurnas_extras : 0;
                $resultado[$i]["nocturnas"] +=  (getdate($inicio)["weekday"] != "Sunday")? $time->nocturnas_extras : 0;
                $resultado[$i]["diurnas_domingo"] +=  (getdate($inicio)["weekday"] == "Sunday")? $time->diurnas_extras : 0;
                $resultado[$i]["nocturnas_domingo"] +=  (getdate($inicio)["weekday"] == "Sunday")? $time->nocturnas_extras : 0;
                $resultado[$i]["diurnas_monto"] += (getdate($inicio)["weekday"] == "Sunday")? $time->diurnas_extras * $diurnasExtraDominicales:$time->diurnas_extras * $diurnasExtra;
                $resultado[$i]["nocturnas_monto"] += (getdate($inicio)["weekday"] == "Sunday")? $time->nocturnas_extras * $nocturnasExtraDominicales:$time->nocturnas_extras * $nocturnasExtra;

                $resultado[$i]["logistica"] += ($time->cena==="SI")?5 :0;
                $resultado[$i]["logistica"] += ($time->taxi==="SI")?5 :0;
                

            }



            $i++;


        }
        
       
        

        return $resultado;









    }






}









































?>