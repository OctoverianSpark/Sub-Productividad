<?php

namespace Controllers;

use Exception;
use Models\ActiveDirectory;
use MVC\Router;
use Google_Client;
use Google_Service_Oauth2;
use Models\Users;

class Login
{
    // variables para los errores
    private const ERROR_MISSING_CREDENTIALS = 1;
    private const ERROR_AUTH_FAILED = 2;
    private const ERROR_INVALID_DOMAIN = 3;
    private const ERROR_USER_NOT_FOUND = 4;

    // Usuarios de dominio validos: letras, numeros, punto, guion y guion bajo
    private const PATRON_USUARIO = '/^[A-Za-z0-9._-]{1,64}$/';

    public static function login(Router $router)
    {
        $errorMessage = null;

        if (isset($_SESSION["login_error"])) {
            $errorMessage = self::getErrorMessage((int) $_SESSION["login_error"]);
            unset($_SESSION["login_error"]);
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $credentials = $_POST["login"] ?? [];

            if (!is_array($credentials) || !self::validateLoginInput($credentials)) {
                self::redirectWithError(self::ERROR_MISSING_CREDENTIALS);
            }

            try {
                $ad = new ActiveDirectory($credentials);

                if (!$ad->auth()) {
                    self::redirectWithError(self::ERROR_AUTH_FAILED);
                }

                $user = Users::findUser(null, $credentials["user"]);

                if (!$user) {
                    self::redirectWithError(self::ERROR_USER_NOT_FOUND);
                }

                $adData = $ad->consultData();

                self::createUserSession($credentials["user"], $adData, $user->mode, "user");
                redirigir("/");
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                self::redirectWithError(self::ERROR_AUTH_FAILED);
            }
        }

        $router->render("pages/login", [
            "errorMessage" => $errorMessage
        ]);
    }

    private static function validateLoginInput(array $input): bool
    {
        return is_string($input["user"] ?? null)
            && is_string($input["password"] ?? null)
            && preg_match(self::PATRON_USUARIO, $input["user"])
            && $input["password"] !== "";
    }

    private static function createUserSession(string $username, $adData, string $mode, string $logType): void
    {
        session_regenerate_id(true);
        $_SESSION["login"] = true;
        $_SESSION["log_type"] = $logType;
        $_SESSION["username"] = $username;
        $_SESSION["name"] = strtoupper($adData["displayname"] ?? $username);
        $_SESSION["mode"] = strtolower($mode);
        $_SESSION["last_activity"] = time();
    }

    private static function getErrorMessage(int $errorCode)
    {
        return match ($errorCode) {
            self::ERROR_MISSING_CREDENTIALS => "Completar los campos.",
            self::ERROR_AUTH_FAILED => "Usuario o contraseña incorrectos",
            self::ERROR_USER_NOT_FOUND => "El usuario no tiene acceso a la aplicacion.",
            self::ERROR_INVALID_DOMAIN => "El usuario no pertenece al dominio.",
            default => "Error.",
        };
    }

    private static function redirectWithError(int $errorCode): never
    {
        iniciarSesion();
        $_SESSION["login_error"] = $errorCode;
        redirigir("/login");
    }

    public static function logout(): void
    {
        iniciarSesion();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => $params["samesite"] ?? "Lax",
            ]);
        }
        session_destroy();
        redirigir("/login");
    }

    public static function redirect(): void
    {
        try {
            $client = self::createGoogleClient();

            if (isset($_GET["code"])) {
                self::handleGoogleCallback($client);
            }

            // "state" aleatorio para evitar que un tercero inicie sesion a nombre de la victima (login CSRF)
            $_SESSION["oauth_state"] = bin2hex(random_bytes(16));
            $client->setState($_SESSION["oauth_state"]);
            redirigir($client->createAuthUrl());
        } catch (Exception $e) {
            error_log("Google OAuth error: " . $e->getMessage());
            self::redirectWithError(self::ERROR_AUTH_FAILED);
        }
    }

    private static function createGoogleClient()
    {
        // La URL sale de .env; no se confia en el encabezado Host que envia el navegador
        $redirectUri = rtrim($_ENV["APP_URL"], "/") . "/redirect";

        $client = new Google_Client();
        $client->setClientId($_ENV["GOOGLE_CLIENT_ID"] ?? "");
        $client->setClientSecret($_ENV["GOOGLE_CLIENT_SECRET"] ?? "");
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        return $client;
    }

    private static function handleGoogleCallback(Google_Client $client): never
    {
        $esperado = $_SESSION["oauth_state"] ?? "";
        unset($_SESSION["oauth_state"]);

        if (!$esperado || !hash_equals($esperado, (string) ($_GET["state"] ?? ""))) {
            throw new Exception("State de OAuth invalido");
        }

        $token = $client->fetchAccessTokenWithAuthCode((string) $_GET["code"]);

        if (isset($token["error"])) {
            throw new Exception("Error obteniendo token: " . $token["error"]);
        }
        $client->setAccessToken($token["access_token"]);

        $google_account_info = (new Google_Service_Oauth2($client))->userinfo->get();

        if (!$google_account_info->verifiedEmail) {
            self::redirectWithError(self::ERROR_AUTH_FAILED);
        }

        $email = $google_account_info->email;
        $user = Users::findUser($email);

        if (!$user) {
            self::redirectWithError(self::ERROR_USER_NOT_FOUND);
        }

        self::createGoogleUserSession($google_account_info->name ?? $email, $email, $google_account_info->picture ?? "", $user->mode);
        redirigir("/");
    }

    private static function createGoogleUserSession(string $name, string $email, string $picture, string $mode): void
    {
        session_regenerate_id(true);
        $_SESSION["login"] = true;
        $_SESSION["log_type"] = "email";
        $_SESSION["name"] = $name;
        $_SESSION["email"] = $email;
        $_SESSION["picture"] = $picture;
        $_SESSION["mode"] = strtolower($mode);
        $_SESSION["last_activity"] = time();
    }
}
