<?php

namespace MVC;

class Router
{
    private $rutas = [
        "GET" => [],
        "POST" => [],
    ];

    // Rutas accesibles sin sesion
    private const RUTAS_PUBLICAS = ["/login", "/logout", "/redirect"];

    // $permiso: clave de "roles.permisos" en config/parametros.json (null = cualquier usuario logueado)
    public function get(string $url, callable $fn, ?string $permiso = null): void
    {
        $this->rutas["GET"][$url] = ["fn" => $fn, "permiso" => $permiso];
    }

    public function post(string $url, callable $fn, ?string $permiso = null): void
    {
        $this->rutas["POST"][$url] = ["fn" => $fn, "permiso" => $permiso];
    }

    public function comprobarRutas(): void
    {
        iniciarSesion();

        $urlActual = $_SERVER["PATH_INFO"] ?? "/";
        $metodo = $_SERVER["REQUEST_METHOD"];
        $esPublica = in_array($urlActual, self::RUTAS_PUBLICAS, true);

        if (!$esPublica) {
            if (!estaLogueado()) {
                redirigir("/login");
            }
            $this->controlarInactividad();
        }

        if ($metodo === "POST" && !csrfValido()) {
            http_response_code(403);
            echo "La sesion del formulario expiro. Vuelve a la pagina anterior, recargala e intenta de nuevo.";
            return;
        }

        $ruta = $this->rutas[$metodo][$urlActual] ?? null;

        if (!$ruta) {
            http_response_code(404);
            echo "Pagina no Encontrada";
            return;
        }

        if ($ruta["permiso"] && !puede($ruta["permiso"])) {
            redirigir("/");
        }

        call_user_func($ruta["fn"], $this);
    }

    private function controlarInactividad(): void
    {
        $limite = (int) Config::get("app.sesion_inactividad_minutos", 120) * 60;
        $ultima = $_SESSION["last_activity"] ?? 0;

        if (time() - $ultima > $limite) {
            $_SESSION = [];
            session_destroy();
            redirigir("/login");
        }
        $_SESSION["last_activity"] = time();
    }

    public function render(string $view, array $datos = []): void
    {
        extract($datos, EXTR_SKIP);

        $viewPath = __DIR__ . "/views/$view.php";
        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo "La vista no existe.";
            return;
        }

        ob_start();
        include $viewPath;
        $contenido = ob_get_clean();
        include __DIR__ . "/views/layout.php";
    }
}
