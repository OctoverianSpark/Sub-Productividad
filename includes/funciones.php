<?php

use MVC\Config;

// Escapa texto para imprimirlo de forma segura en HTML (contenido y atributos)
function s($html): string
{
    return htmlspecialchars((string) ($html ?? ""), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Redirige y detiene la ejecucion (sin exit el codigo seguia corriendo)
function redirigir(string $url): never
{
    header("Location: $url");
    exit;
}

function mostrarNotificacion($resultado): string
{
    return match ((int) $resultado) {
        1 => "Los Datos se han registrado correctamente",
        2 => "Los datos fueron actualizados correctamente",
        3 => "Los datos fueron eliminados correctamente",
        4 => "<i class='bx bxs-message-alt-check'></i> Exportacion guardada !!!",
        default => "",
    };
}

// Lee el id de POST (acciones) o GET (formularios); si no es un entero valido redirige
function validarID(): int
{
    $id = filter_var($_POST["id"] ?? $_GET["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

    if (!$id) {
        redirigir("/");
    }
    return $id;
}

function estaLogueado(): bool
{
    return !empty($_SESSION["login"]);
}

// ======
// SESION
// ======

function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $seguro = filter_var($_ENV["SESSION_SECURE"] ?? true, FILTER_VALIDATE_BOOLEAN);

    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $seguro,
        "httponly" => true,
        // Lax permite que la cookie llegue en el regreso desde Google OAuth
        "samesite" => "Lax",
    ]);
    session_start();
}

// =====
// ROLES
// =====

function modoActual(): string
{
    return strtolower($_SESSION["mode"] ?? "");
}

// Indica si el usuario en sesion tiene un permiso definido en config/parametros.json
function puede(string $permiso): bool
{
    if (!estaLogueado()) return false;

    $roles = Config::get("roles.permisos.$permiso", []);
    return in_array("*", $roles, true) || in_array(modoActual(), $roles, true);
}

// ====
// CSRF
// ====

function csrfToken(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . s(csrfToken()) . '">';
}

function csrfValido(): bool
{
    $enviado = $_POST["csrf_token"] ?? "";
    return is_string($enviado) && !empty($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $enviado);
}

// =========
// UTILIDADES
// =========

/**
 * Convierte "2025-01-31", "2025-01-31T08:00" o "2025-01-31 08:00:00" a "Y-m-d H:i:s".
 * Si solo viene la fecha, usa el inicio o el final del dia. Devuelve null si no es valida.
 */
function normalizarFecha(?string $valor, bool $finDelDia = false): ?string
{
    if (!$valor) return null;

    $valor = str_replace("T", " ", trim($valor));

    foreach (["Y-m-d H:i:s", "Y-m-d H:i"] as $formato) {
        $fecha = DateTime::createFromFormat("!$formato", $valor);
        if ($fecha && $fecha->format($formato) === $valor) {
            return $fecha->format("Y-m-d H:i:s");
        }
    }

    $fecha = DateTime::createFromFormat("!Y-m-d", $valor);
    if ($fecha && $fecha->format("Y-m-d") === $valor) {
        return $fecha->format("Y-m-d") . ($finDelDia ? " 23:59:59" : " 00:00:00");
    }
    return null;
}
