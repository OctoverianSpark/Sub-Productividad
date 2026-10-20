<?php

namespace Controllers;

use Models\Logs;
use MVC\Router;
use Models\Users as DB_Users;

class Users
{
    public static function index(Router $router)
    {
        $router->render("settings/usuarios/index", [
            "usuarios" => DB_Users::all()
        ]);
    }

    public static function crear(Router $router)
    {
        $user = new DB_Users();
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $user = new DB_Users(self::datosFormulario());
            $errores = $user->validar();

            if (empty($errores)) {
                $user->guardar();
                Logs::registrar("Usuario Admitido", "El usuario " . $_SESSION["name"] . " ha registrado al usuario " . $user->email . " para su trabajo autorizado en la aplicacion de sub productividad");
                redirigir("/settings/usuarios?resultado=1");
            }
        }

        $router->render("settings/usuarios/crear", [
            "user" => $user,
            "errores" => $errores
        ]);
    }

    public static function actualizar(Router $router)
    {
        $id = validarID();
        $user = DB_Users::find($id);
        if (!$user) {
            redirigir("/settings/usuarios");
        }
        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $datos = self::datosFormulario();
            $datos["id"] = $id;
            $user = new DB_Users($datos);
            $errores = $user->validar();

            if (empty($errores)) {
                $user->guardar();
                Logs::registrar("Usuario Actualizado", "El usuario " . $_SESSION["name"] . " ha actualizado al usuario " . $user->email . " para su trabajo autorizado en la aplicacion de sub productividad");
                redirigir("/settings/usuarios?resultado=2");
            }
        }

        $router->render("settings/usuarios/actualizar", [
            "user" => $user,
            "errores" => $errores
        ]);
    }

    public static function eliminar()
    {
        $id = validarID();
        $user = DB_Users::find($id);

        if ($user) {
            $user->eliminar();
            Logs::registrar("USUARIO ELIMINADO", "EL USUARIO " . $_SESSION["name"] . " HA ELIMINADO AL USUARIO " . $user->email . " DE LOS PERMISOS DE LA APLICACION DE SUB PRODUCTIVIDAD");
        }

        redirigir("/settings/usuarios?resultado=3");
    }

    // Solo los campos editables (nunca el id ni campos extra que vengan en el POST)
    private static function datosFormulario(): array
    {
        return [
            "user" => trim((string) ($_POST["user"] ?? "")),
            "email" => trim((string) ($_POST["email"] ?? "")),
            "mode" => strtolower(trim((string) ($_POST["mode"] ?? ""))),
        ];
    }
}
