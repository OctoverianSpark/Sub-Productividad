<?php

namespace Controllers;

use Exception;
use InvalidArgumentException;
use MVC\Router;
use Models\Empleados;
use Models\Logs;
use Models\Paginacion;
use Models\Time;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Pages
{
    public static function index(Router $router)
    {
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;

        $filtros = [
            'column' => $_GET['column'] ?? '',
            'param' => $_GET['param'] ?? ''
        ];


        $paginacion = Logs::paginarConFiltros($page, $perPage, $filtros);

        $baseUrl = "/";
        $pagination_links = Paginacion::buildPaginationLinks($baseUrl, $paginacion, $filtros);

        $router->render("pages/index", [
            'logs' => $paginacion['data'],
            'paginacion' => $paginacion,
            'pagination_links' => $pagination_links,
            'filtros' => $filtros
        ]);
    }
    public static function export()
    {
        try {
            $exportHandler = new ExportHandler();
            $exportHandler->handleExport();
        } catch (Exception $e) {
            error_log("Error en exportacion:" . $e->getMessage());
        }
    }
}


class ExportHandler
{
    private const EMPLEADO_HEADERS = ["EMPLEADO", "CLIENTE", "MODALIDAD", "INICIO DE JORNADA", "ALMUERZO", "FINAL DE JORNADA", "DIURNAS ORDINARIAS", "DIURNAS EXTRAS", "NOCTURNAS ORDINARIAS", "NOCTURNAS EXTRAS", "CENA", "TAXI", "MONTO EXTRAS DIURNAS", "MONTO EXTRAS NOCTURNAS", "TOTAL A PAGAR", "COMENTARIOS"];
    private const CLIENTE_HEADERS = ['CLIENTE', 'HORAS EXTRAS DIURNAS', 'HORAS EXTRAS DIURNAS DOMINICALES', 'HORAS ORDINARIAS DIURNAS DOMINICALES', 'HORAS EXTRAS NOCTURNAS', 'HORAS EXTRAS NOCTURNAS DOMINICALES', 'MONTO EXTRAS DIURNAS', 'MONTO EXTRAS NOCTURNAS', 'MONTO LOGISTICA', 'SUBTOTAL'];
    private const EMPLEADO_INFO_HEADERS = ['NOMBRE', 'APELLIDO', 'TIPO DE DOCUMENTO', 'DOCUMENTO', 'SEDE', 'CARGO', 'SALARIO'];

    private $spreadsheet;
    private $writer;
    private $filename;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->spreadsheet);
    }

    public function handleExport()
    {
        $table = $_GET["table"];
        $from = $this->getDateParameter("from");
        $to = $this->getDateParameter("to");

        switch ($table) {
            case "empleados":
                if ($from && $to) {
                    $this->exportEmpleados($from, $to);
                } else {
                    $this->exportEmpleadosInfo();
                }
                break;
            case "clientes":
                $this->exportClientes($from, $to);
                break;
            default:
                throw new InvalidArgumentException("Tabla no valida:" . $table);
        }
        $this->outputFile();
    }


    private function getDateParameter($param)
    {
        return isset($_GET[$param]) ? str_replace("T", "", $_GET[$param]) : null;
    }
    private function setFilename($filename)
    {
        $this->filename = $filename;
    }
    private function getSheetNameForEmployee($empleado)
    {
        if (strtolower($empleado->modalidad) === "hogar") {
            return "OPS";
        }
        return ($empleado->sede === "colombia") ? "AVSAS" : "AVCA";
    }



    private function exportEmpleados($from, $to)
    {
        $filename = "reporte_" . strtotime($from) . "-" . strtotime($to) . ".xlsx";
        $horas = Time::getPayments(null, null, $from, $to);

        $this->hojaEmpleados();
        $hojas = ["AVSAS" => 2, "AVCA" => 2, "OPS" => 2];

        foreach ($horas as $hora) {
            $empleado = Empleados::getByFullName($hora["empleado"]);
            if (!$empleado) {
                error_log("Empleado no Encontrado:" . $hora["empleado"]);
                continue;
            }
            $sheetName = $this->getSheetNameForEmployee($empleado);
            $row = $hojas[$sheetName]++;

            $this->addEmpleadoRow($sheetName, $hora, $empleado, $row);
        }
        $this->setFilename($filename);
    }
    private function exportClientes($from, $to)
    {
        $filename = "reporte_clientes_" . strtotime($from) . "-" . strtotime($to) . ".xlsx";
        $horas = Time::getAgentPayments($from, $to);

        $this->hojaClientes();
        foreach ($horas as $index => $hora) {
            $this->addClienteRow($hora, $index + 2);
        }
        $this->setFilename($filename);
    }

    public function  exportEmpleadosInfo()
    {
        $filename = "empleados.xlsx";
        $empleados = Empleados::all();

        $this->createEmpleadoInfoSheet();

        foreach ($empleados as $index => $empleado) {
            $this->addEmployeeInfoRow($empleado, $index + 2);
        }
        $this->setFilename($filename);
    }


    private function hojaEmpleados()
    {
        $this->spreadsheet->removeSheetByIndex(0);

        $sheets = ['AVSAS', 'AVCA', 'OPS'];
        foreach ($sheets as $sheetName) {
            $sheet = $this->spreadsheet->createSheet()->setTitle($sheetName);
            $sheet->fromArray(self::EMPLEADO_HEADERS);
        }

        $this->spreadsheet->setActiveSheetIndexByName('AVSAS');
    }
    private function hojaClientes()
    {
        $this->spreadsheet->removeSheetByIndex(0);
        $sheet = $this->spreadsheet->createSheet()->setTitle("Clientes");
        $sheet->fromArray(self::CLIENTE_HEADERS, null, "A1");
    }
    private function createEmpleadoInfoSheet()
    {
        $this->spreadsheet->removeSheetByIndex(0);
        $sheet = $this->spreadsheet->createSheet()->setTitle("empleados");
        $sheet->fromArray(self::EMPLEADO_INFO_HEADERS);
    }


    private function addEmpleadoRow($sheetName, $hora, $empleado, $row)
    {
        $this->spreadsheet->setActiveSheetIndexByName($sheetName);

        $rowData = [
            strtoupper($hora["empleado"]),
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
            floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"])),
            $hora["comentarios"]
        ];

        $this->spreadsheet->getActiveSheet()->fromArray($rowData, null, "A$row");
    }
    private function addClienteRow($hora, $row)
    {
        $rowData = [
            $hora["cliente"],
            $hora["diurnas"],
            $hora["diurnas_domingo"],
            $hora["diurnas_ordinarias_domingo"],
            $hora["nocturnas"],
            $hora["nocturnas_domingo"],
            $hora["diurnas_monto"],
            $hora["nocturnas_monto"],
            $hora["logistica"],
            floatval(s($hora["diurnas_monto"])) + floatval(s($hora["nocturnas_monto"])) + floatval(s($hora["logistica"]))

        ];

        $this->spreadsheet->getActiveSheet()->fromArray($rowData, null, "A$row");
    }
    private function addEmployeeInfoRow($empleado, $row)
    {
        $rowData = [
            strtoupper($empleado->nombre),
            strtoupper($empleado->apellido),
            strtoupper($empleado->tipo_documento),
            strtoupper($empleado->documento),
            strtoupper($empleado->sede),
            strtoupper($empleado->cargo),
            strtoupper($empleado->salario)
        ];

        $this->spreadsheet->getActiveSheet()->fromArray($rowData, null, "A$row");
    }




    private function outputFile()
    {
        if (ob_get_length()) ob_end_clean();

        if (!headers_sent()) {

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . urlencode($this->filename) . '"');
            header('Cache-Control: max-age=0');
            $this->writer->save('php://output');
            $this->spreadsheet->disconnectWorksheets();
            unset($this->spreadsheet);
        }

        exit;
    }
}
