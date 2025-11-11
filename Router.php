<?php

namespace MVC;


class Router
{

    private $rutas = [
        "GET" => [],
        "POST" => [],
    ];

    public function get($url, $fn)
    {
        $this->rutas["GET"][$url] = $fn;
    }
    public function post($url, $fn)
    {
        $this->rutas["POST"][$url] = $fn;
    }


    public function comprobarRutas()
    {
        session_start();
        $auth = $_SESSION["login"] ?? null;
        $modo = strtoupper($_SESSION["mode"]) ?? null;

        $rutasPublicas = [
            "/login",
            "/logout",
            "/redirect",
        ];

        $rutasGOD = [
            "/settings/usuarios",
            "/settings/usuarios/crear",
            "/settings/usuarios/actualizar",
            "/settings/usuarios/eliminar"
        ];

        $urlActual = $_SERVER["PATH_INFO"] ?? "/";
        $metodo = $_SERVER["REQUEST_METHOD"];

        // RUTAS PUBLICAS
        if (!in_array($urlActual, $rutasPublicas) && !$auth) {
            header("Location: /login");
            exit;
        }

        // RUTAS GOD
        if (in_array($urlActual, $rutasGOD) && $modo !== "GOD") {
            header("Location: /");
            exit;
        }

        $fn = $this->rutas[$metodo][$urlActual] ?? null;

        if ($fn) {
            call_user_func($fn, $this);
        } else {
            echo "Pagina no Encontrada";
        }
    }

    public function render($view, $datos = [])
    {
        foreach ($datos as $key => $value) {
            $$key = $value;
        }

        $viewPath = __DIR__ . "/views/$view.php";
        if (!file_exists($viewPath)) {
            echo "La vista $view no existe.";
            return;
        }

        ob_start();
        include $viewPath;
        $contenido = ob_get_clean();
        include __DIR__ . "/views/layout.php";
    }
}
