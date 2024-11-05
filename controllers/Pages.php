<?php 

namespace Controllers;

use MVC\Router;
use Models\Empleados;
use Models\Logs;
use Models\Time;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Pages{

    public static function index( Router $router ){

        $logs = Logs::all();
        $router->render("pages/index",[
            "logs" => $logs
        ]);


    }


    public static function export(){

        $tabla = $_GET["table"];
        

        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        if($tabla === "empleados" && $_GET["from"] && $_GET["to"]){

            $from = str_replace("T"," ",$_GET["from"]);
            $to = str_replace("T"," ",$_GET["to"]);
            $filename = "reporte". strtotime($from) ."-". strtotime($to) . ".xlsx";  


            $horas = Time::getPayments(null,null,$from,$to);
            

            $i = 2;
            $j = 2;

            $spreadsheet->createSheet(1)->setTitle("AVSAS");
            $spreadsheet->createSheet(2)->setTitle("AVCA");
            $spreadsheet->removeSheetByIndex(0);

            

            $spreadsheet->getSheetByName("AVSAS")->fromArray(["EMPLEADO","CLIENTE","MODALIDAD","INICIO DE JORNADA","ALMUERZO","FINAL DE JORNADA","DIURNAS ORDINARIAS","DIURNAS EXTRAS","NOCTURNAS ORDINARIAS","NOCTURNAS EXTRAS","CENA","TAXI","MONTO EXTRAS DIURNAS","MONTO EXTRAS NOCTURNAS","TOTAL A PAGAR"]);
            $spreadsheet->getSheetByName("AVCA")->fromArray(["EMPLEADO","CLIENTE","MODALIDAD","INICIO DE JORNADA","ALMUERZO","FINAL DE JORNADA","DIURNAS ORDINARIAS","DIURNAS EXTRAS","NOCTURNAS ORDINARIAS","NOCTURNAS EXTRAS","CENA","TAXI","MONTO EXTRAS DIURNAS","MONTO EXTRAS NOCTURNAS","TOTAL A PAGAR"]);

            $spreadsheet->setActiveSheetIndexByName("AVSAS");
            foreach ($horas as $hora) {

                $empleado = Empleados::getByFullName($hora["empleado"]);

                if($empleado->sede === "colombia"){

                    $spreadsheet->setActiveSheetIndexByName("AVSAS");

                    $spreadsheet->getActiveSheet()->fromArray( [strtoupper($hora["empleado"]),
                                                                strtoupper($hora["cliente"]),
                                                                strtoupper($empleado->modalidad),
                                                                strtoupper($hora["inicio"]),
                                                                strtoupper($hora["almuerzo"]),
                                                                strtoupper($hora["final"]),
                                                                strtoupper($hora["diurnas_ordinarias"]),
                                                                strtoupper($hora["diurnas_extras"]),
                                                                strtoupper($hora["nocturnas_ordinarias"]),
                                                                strtoupper($hora["nocturnas_extras"]),
                                                                strtoupper($hora["cena"]),
                                                                strtoupper($hora["taxi"]),
                                                                strtoupper($hora["diurnas_monto"]),
                                                                strtoupper($hora["nocturnas_monto"]),
                                                                floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"]))
                ],null,"A$i" );
                $i++;

                }else if($empleado->sede === "venezuela"){
                    
                    $spreadsheet->setActiveSheetIndexByName("AVCA");
                    $spreadsheet->getActiveSheet()->fromArray( [strtoupper($hora["empleado"]),
                                                                strtoupper($hora["cliente"]),
                                                                strtoupper($empleado->modalidad),
                                                                strtoupper($hora["inicio"]),
                                                                strtoupper($hora["almuerzo"]),
                                                                strtoupper($hora["final"]),
                                                                strtoupper($hora["diurnas_ordinarias"]),
                                                                strtoupper($hora["diurnas_extras"]),
                                                                strtoupper($hora["nocturnas_ordinarias"]),
                                                                strtoupper($hora["nocturnas_extras"]),
                                                                strtoupper($hora["cena"]),
                                                                strtoupper($hora["taxi"]),
                                                                strtoupper($hora["diurnas_monto"]),
                                                                strtoupper($hora["nocturnas_monto"]),
                                                                floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"]))
                ],null,"A$j" );
                
                $j++;

                }







            }
            


        }else if($tabla === "clientes" && $_GET["from"] && $_GET["to"]){

            
            $from = str_replace("T"," ",$_GET["from"]);
            $to = str_replace("T"," ",$_GET["to"]);
            $filename = "reporte". strtotime($from) ."-". strtotime($to) . ".xlsx"; 


            $horas = Time::getAgentPayments($from,$to);
            
            $i = 2;

            $spreadsheet->createSheet(1)->setTitle("Clientes");



            
            $spreadsheet->removeSheetByIndex(0);

            $spreadsheet->getActiveSheet()->fromArray([ 
                                                        "CLIENTE",
                                                        "HORAS EXTRAS DIURNAS",
                                                        "HORAS EXTRAS NOCTURNAS",
                                                        "MONTO EXTRAS DIURNAS",
                                                        "MONTO EXTRAS NOCTURNAS",
                                                        "MONTO LOGISTICA",
                                                        "SUBTOTAL"
                                                    ],"A1");


            foreach($horas as $hora){

                        
                        
                    $spreadsheet->getActiveSheet()->fromArray([ 
                    $hora["cliente"],
                    $hora["diurnas"],
                    $hora["nocturnas"],
                    $hora["diurnas_monto"],
                    $hora["nocturnas_monto"],
                    $hora["logistica"],
                    intval(s($hora["diurnas_monto"])) + intval(s($hora["nocturnas_monto"])) + intval(s($hora["logistica"]))
                   
                ], null,"A$i");
                $i++;





            }



        }else if($tabla === "empleados"){

            $filename = $tabla . ".xlsx";

            $empleados = Empleados::all();

            $spreadsheet->createSheet(1)->setTitle("empleados");


            $spreadsheet->removeSheetByIndex(0);



            $spreadsheet->getActiveSheet()->fromArray([
                                                        "NOMBRE",
                                                        "APELLIDO",
                                                        "TIPO DE DOCUMENTO",
                                                        "DOCUMENTO",
                                                        "SEDE",
                                                        "CARGO",
                                                        "SALARIO"
                                                    ]);

            $i=2;

            foreach ($empleados as $empleado) {
                

                $spreadsheet->getActiveSheet()->fromArray([
                                                strtoupper($empleado->nombre),
                                                strtoupper($empleado->apellido),
                                                strtoupper($empleado->tipo_documento),
                                                strtoupper($empleado->documento),
                                                strtoupper($empleado->sede),
                                                strtoupper($empleado->cargo),
                                                strtoupper($empleado->salario),]
                                            ,null,
                                            "A$i");


                    $i++;



            }
        }

        


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($filename).'"');
        $writer->save('php://output');

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        header("Location /?resultado=4");


    }


    

}





?>