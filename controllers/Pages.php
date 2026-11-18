<?php

namespace Controllers;

use Exception;
use InvalidArgumentException;
use Models\CalculadoraPagos;
use Models\ClienteEmpleado;
use Models\Clientes;
use MVC\Router;
use Models\Empleados;
use Models\Logs;
use Models\Paginacion;
use Models\Time;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Pages
{
    public static function index(Router $router)
    {
        $filtros = [
            'column' => $_GET['column'] ?? '',
            'param' => $_GET['param'] ?? ''
        ];

        $paginacion = null;
        $pagination_links = null;

        if (puede("ver_logs")) {
            $paginacion = Logs::paginarConFiltros($_GET['page'] ?? 1, $_GET['per_page'] ?? null, $filtros);
            $pagination_links = Paginacion::buildPaginationLinks("/", $paginacion, $filtros);
        }

        $router->render("pages/index", [
            'logs' => $paginacion['data'] ?? [],
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
            error_log("Error en exportacion: " . $e->getMessage());
            http_response_code(400);
            echo "No se pudo generar la exportacion.";
        }
    }
}


class ExportHandler
{
    private const EMPLEADO_HEADERS = ["EMPLEADO", "CLIENTE", "MODALIDAD", "INICIO DE JORNADA", "ALMUERZO", "FINAL DE JORNADA", "DIURNAS", "NOCTURNAS", "DIURNAS DOMINICALES", "NOCTURNAS DOMINICALES", "CENA", "TAXI", "MONTO DIURNAS", "MONTO NOCTURNAS", "MONTO DIURNAS DOMINICALES", "MONTO NOCTURNAS DOMINICALES", "TOTAL A PAGAR", "COMENTARIOS"];
    private const CLIENTE_HEADERS = ['CLIENTE', 'HORAS EXTRAS DIURNAS', 'HORAS EXTRAS DIURNAS DOMINICALES', 'HORAS NOCTURNAS', 'HORAS NOCTURNAS DOMINICALES', 'MONTO EXTRAS DIURNAS', 'MONTO EXTRAS NOCTURNAS', 'MONTO LOGISTICA', 'SUBTOTAL'];
    private const EMPLEADO_INFO_HEADERS = ['NOMBRE', 'APELLIDO', 'TIPO DE DOCUMENTO', 'DOCUMENTO', 'SEDE', 'CARGO', 'SALARIO'];
    private const CLIENTE = ['NOMBRE', 'APELLIDO', 'EMPLEADO', 'TIPO'];

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
        $table = $_GET["table"] ?? "";
        $column = $_GET["column"] ?? null;
        $param = $_GET["param"] ?? null;
        $from = $this->getDateParameter("from");
        $to = $this->getDateParameter("to", true);

        switch ($table) {
            case "empleados":
                if ($from && $to) {
                    $this->exportEmpleados($column, $param, $from, $to);
                } else {
                    // Datos maestros (incluye salarios): solo roles de configuracion
                    $this->exigirPermiso("exportar_maestros");
                    $this->exportEmpleadosInfo();
                }
                break;
            case "clientes":
                $this->exportClientes($from, $to);
                break;
            case "clientes_info":
                $this->exigirPermiso("exportar_maestros");
                $this->exportClientesInfo();
                break;
            default:
                throw new InvalidArgumentException("Tabla no valida: " . $table);
        }
        $this->outputFile();
    }

    private function exigirPermiso(string $permiso): void
    {
        if (!puede($permiso)) {
            redirigir("/");
        }
    }

    private function getDateParameter(string $param, bool $finDelDia = false): ?string
    {
        return normalizarFecha($_GET[$param] ?? null, $finDelDia);
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
        return (strtolower($empleado->sede) === "colombia") ? "AVSAS" : "AVCA";
    }

    private function exportEmpleados(?string $column, ?string $param, string $from, string $to)
    {
        $filename = "reporte_" . strtotime($from) . "-" . strtotime($to) . ".xlsx";
        $horas = Time::getPayments($column, $param, $from, $to);
        $empleados = Empleados::indexadosPorNombre();
        $this->hojaEmpleados();
        $hojas = ["AVSAS" => 2, "AVCA" => 2, "OPS" => 2];

        foreach ($horas as $hora) {
            $empleado = $empleados[mb_strtolower(trim($hora["empleado"]), "UTF-8")] ?? null;
            if (!$empleado) {
                error_log("Empleado no Encontrado: " . $hora["empleado"]);
                continue;
            }
            $sheetName = $this->getSheetNameForEmployee($empleado);
            $row = $hojas[$sheetName]++;

            $this->addEmpleadoRow($sheetName, $hora, $empleado, $row);
        }
        $this->setFilename($filename);
    }

    private function exportClientes(?string $from, ?string $to)
    {
        $filename = "reporte_clientes_" . strtotime((string) $from) . "-" . strtotime((string) $to) . ".xlsx";
        $horas = Time::getAgentPayments($from, $to);

        $this->hojaClientes();
        foreach ($horas as $index => $hora) {
            $this->addClienteRow($hora, $index + 2);
        }
        $this->setFilename($filename);
    }

    private function exportEmpleadosInfo()
    {
        $empleados = Empleados::all();

        $this->createEmpleadoInfoSheet();

        foreach ($empleados as $index => $empleado) {
            $this->addEmployeeInfoRow($empleado, $index + 2);
        }
        $this->setFilename("empleados.xlsx");
    }

    private function exportClientesInfo()
    {
        $clientes = Clientes::all();
        $relaciones = ClienteEmpleado::agrupadasPorCliente();

        $this->createClienteInfoSheet();

        // Una fila por relacion cliente-empleado; el cliente sin empleados sale con una fila vacia
        $row = 2;
        foreach ($clientes as $cliente) {
            foreach ($relaciones[(int) $cliente->id] ?? [["empleado" => "", "tipo" => ""]] as $relacion) {
                $this->addClienteInfoRow($cliente, $relacion, $row++);
            }
        }
        $this->setFilename("clientesInfo.xlsx");
    }

    private function hojaEmpleados()
    {
        $this->spreadsheet->removeSheetByIndex(0);

        foreach (['AVSAS', 'AVCA', 'OPS'] as $sheetName) {
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

    private function createClienteInfoSheet()
    {
        $this->spreadsheet->removeSheetByIndex(0);
        $sheet = $this->spreadsheet->createSheet()->setTitle("Clientes");
        $sheet->fromArray(self::CLIENTE, null, "A1");
    }

    private function addEmpleadoRow($sheetName, $hora, $empleado, $row)
    {
        $this->spreadsheet->setActiveSheetIndexByName($sheetName);

        $categorias = CalculadoraPagos::CATEGORIAS_EMPLEADO;

        $this->escribirFila(array_merge(
            [
                strtoupper($hora["empleado"]),
                strtoupper($hora["cliente"]),
                strtoupper($empleado->modalidad),
                $hora["inicio"],
                (float) $hora["almuerzo"],
                $hora["final"],
            ],
            array_map(fn($c) => (float) $hora[$c . "_horas"], $categorias),
            [strtoupper($hora["cena"]), strtoupper($hora["taxi"])],
            array_map(fn($c) => (float) $hora[$c . "_monto"], $categorias),
            [(float) $hora["total"], $hora["comentarios"]],
        ), $row);
    }

    private function addClienteRow($hora, $row)
    {
        $this->escribirFila([
            strtoupper($hora["cliente"]),
            (float) $hora["diurnas"],
            (float) $hora["diurnas_domingo"],
            (float) $hora["nocturnas"],
            (float) $hora["nocturnas_domingo"],
            (float) $hora["diurnas_monto"],
            (float) $hora["nocturnas_monto"],
            (float) $hora["logistica"],
            (float) $hora["diurnas_monto"] + (float) $hora["nocturnas_monto"] + (float) $hora["logistica"],
        ], $row);
    }

    private function addEmployeeInfoRow($empleado, $row)
    {
        $this->escribirFila([
            strtoupper($empleado->nombre),
            strtoupper($empleado->apellido),
            strtoupper($empleado->tipo_documento),
            strtoupper($empleado->documento),
            strtoupper($empleado->sede),
            strtoupper($empleado->cargo),
            (float) $empleado->salario,
        ], $row);
    }

    private function addClienteInfoRow($cliente, array $relacion, $row)
    {
        $this->escribirFila([
            strtoupper($cliente->nombre),
            strtoupper($cliente->apellido),
            strtoupper($relacion["empleado"]),
            strtoupper($relacion["tipo"]),
        ], $row);
    }

    /**
     * Escribe una fila forzando el texto como texto: un valor como "=HYPERLINK(...)"
     * escrito por un usuario no se convierte en formula al abrir el Excel.
     */
    private function escribirFila(array $rowData, int $row): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        foreach (array_values($rowData) as $i => $valor) {
            $celda = Coordinate::stringFromColumnIndex($i + 1) . $row;

            if (is_int($valor) || is_float($valor)) {
                $sheet->setCellValue($celda, $valor);
            } else {
                $sheet->setCellValueExplicit($celda, (string) ($valor ?? ""), DataType::TYPE_STRING);
            }
        }
    }

    private function outputFile()
    {
        if (ob_get_length()) ob_end_clean();

        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . rawurlencode($this->filename) . '"');
            header('Cache-Control: max-age=0');
            $this->writer->save('php://output');
            $this->spreadsheet->disconnectWorksheets();
            unset($this->spreadsheet);
        }

        exit;
    }
}
