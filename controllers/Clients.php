<?php

namespace Controllers;

use MVC\Router;
use Models\ClienteEmpleado;
use Models\Clientes;
use Models\Empleados;
use Models\Logs;

class Clients
{
    public static function index(Router $router)
    {
        $router->render("settings/clientes/index", [
            "clientes" => Clientes::filter($_GET["column"] ?? null, $_GET["param"] ?? null),
            "columnas" => Clientes::getColumns(),
            "relaciones" => ClienteEmpleado::agrupadasPorCliente(),
        ]);
    }

    public static function crear(Router $router)
    {
        $cliente = new Clientes();
        $relaciones = [];
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = (array) ($_POST["clientes"] ?? []);
            unset($datos["id"]);
            $cliente = new Clientes($datos);
            [$relaciones, $erroresRelaciones] = ClienteEmpleado::validarFilas((array) ($_POST["relaciones"] ?? []));
            $errores = array_merge($cliente->validar(), $erroresRelaciones);

            if (empty($errores)) {
                $cliente->guardar();
                ClienteEmpleado::reemplazarDeCliente((int) $cliente->id, $relaciones);
                Logs::registrar("cliente creado", "El usuario " . $_SESSION["name"] . " ha creado al cliente " . $cliente->nombreCompleto() . self::resumenRelaciones($relaciones));
                redirigir("/settings/clientes?resultado=1");
            }
        }

        $router->render("settings/clientes/crear", self::datosFormulario($cliente, $relaciones, $errores));
    }

    public static function actualizar(Router $router)
    {
        $id = validarID();
        $cliente = Clientes::find($id);
        if (!$cliente) {
            redirigir("/settings/clientes");
        }
        $relaciones = ClienteEmpleado::deCliente((int) $id);
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = (array) ($_POST["clientes"] ?? []);
            $datos["id"] = $id;
            $cliente = new Clientes($datos);
            [$relaciones, $erroresRelaciones] = ClienteEmpleado::validarFilas((array) ($_POST["relaciones"] ?? []));
            $errores = array_merge($cliente->validar(), $erroresRelaciones);

            if (empty($errores)) {
                $cliente->guardar();
                ClienteEmpleado::reemplazarDeCliente((int) $id, $relaciones);
                Logs::registrar("cliente actualizado", "El usuario " . $_SESSION["name"] . " ha actualizado al cliente " . $cliente->nombreCompleto() . self::resumenRelaciones($relaciones));
                redirigir("/settings/clientes?resultado=2");
            }
        }

        $router->render("settings/clientes/actualizar", self::datosFormulario($cliente, $relaciones, $errores));
    }

    public static function eliminar()
    {
        $id = validarID();
        $cliente = Clientes::find($id);

        if ($cliente) {
            ClienteEmpleado::eliminarDeCliente((int) $cliente->id);
            $cliente->eliminar();
            Logs::registrar("cliente eliminado", "El usuario " . $_SESSION["name"] . " ha eliminado al cliente " . $cliente->nombreCompleto());
        }

        redirigir("/settings/clientes?resultado=3");
    }

    private static function datosFormulario(Clientes $cliente, array $relaciones, array $errores): array
    {
        return [
            "cliente" => $cliente,
            "relaciones" => $relaciones,
            "empleados" => Empleados::all(),
            "tipos" => ClienteEmpleado::tiposDisponibles(),
            "tipoDefecto" => ClienteEmpleado::tipoPorDefecto(),
            "errores" => $errores,
        ];
    }

    // Texto para el log: " (empleados: valeria perez = nuevo, jose diaz = viejo)"
    private static function resumenRelaciones(array $relaciones): string
    {
        if (!$relaciones) return " (sin empleados asignados)";
        $nombres = [];
        foreach (Empleados::all() as $empleado) {
            $nombres[(int) $empleado->id] = $empleado->nombreCompleto();
        }
        $partes = [];
        foreach ($relaciones as $empleadoId => $tipo) {
            $partes[] = ($nombres[$empleadoId] ?? "#$empleadoId") . " = $tipo";
        }
        return " (empleados: " . implode(", ", $partes) . ")";
    }
}
