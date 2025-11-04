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

    public static function login(Router $router)
    {

        if (isset($_SESSION["login_error"])) {
            $errorMessage = self::getErrorMessage($_SESSION["login_error"]);
            unset($_SESSION["login_error"]);
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!self::validateLoginInput($_POST["login"] ?? " ")) {
                self::redirectWithError(self::ERROR_MISSING_CREDENTIALS);
                return;
            }
            $credentials = $_POST["login"];

            try {
                $ad = new ActiveDirectory($_POST["login"]);
                $isAuthenticated = $ad->auth();


                if (!$isAuthenticated) {
                    self::redirectWithError(self::ERROR_AUTH_FAILED);
                    return;
                }

                $user = Users::findUser(null, $credentials["user"]);

                if (!$user) {
                    self::redirectWithError(self::ERROR_USER_NOT_FOUND);
                    return;
                }

                $adData = $ad->consultData();

                self::createUserSession($credentials["user"], $adData, $user->mode, "user");

                header("Location: /");
                exit();
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
        return !empty($input["user"]) && !empty($input["password"]);
    }

    private static function createUserSession(string $username, array $adData, string $mode, string $logType): void
    {
        session_regenerate_id(true);
        $_SESSION["login"] = true;
        $_SESSION["log_type"] = $logType;
        $_SESSION["username"] = $username;
        $_SESSION["name"] = strtoupper($adData["displayname"] ?? "");
        $_SESSION["mode"] = $mode;
        $_SESSION["last_activity"] = time();
    }

    private static function getErrorMessage(int $errorCode)
    {
        return match ($errorCode) {
            self::ERROR_MISSING_CREDENTIALS => "Completar los campos.",
            self::ERROR_AUTH_FAILED => "Usuario o contraseña incorrectos",
            self::ERROR_USER_NOT_FOUND => "El usuario no existe.",
            self::ERROR_INVALID_DOMAIN => "El usuario no pertenece al dominio.",
            default => "Error.",
        };
    }


    private static function redirectWithError(int $errorCode): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION["login_error"] = $errorCode;

        header("Location:/login");
        exit();
    }



    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
            session_destroy();
            header("Location: /login");
            exit();
        }
    }

    public static function redirect(): void
    {
        try {
            $client = self::createGoogleClient();
            if (isset($_GET["code"])) {
                self::handleGoogleCallback($client);
            } else {
                header("Location:" . $client->createAuthUrl());
                exit();
            }
        } catch (Exception $e) {
            error_log("Google OAuth error: " . $e->getMessage());
            header("Location: /login?error=" . self::ERROR_AUTH_FAILED);
            exit();
        }
    }

    private static function createGoogleClient()
    {
        $redirectUri = "https://" . $_SERVER["HTTP_HOST"] . "/redirect";
        $clientId = $_ENV["GOOGLE_CLIENT_ID"] ?? "";
        $clientSecret = $_ENV["GOOGLE_CLIENT_SECRET"] ?? "";


        $client = new Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        return $client;
    }

    private static function handleGoogleCallback(Google_Client $client): void
    {
        $token = $client->fetchAccessTokenWithAuthCode($_GET["code"]);

        if (isset($token["error"])) {
            throw new Exception("Error obteniendo token" . $token["error"]);
        }
        $client->setAccessToken($token["access_token"]);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $picture = $google_account_info->picture;


        $user = Users::findUser($email);

        if (!$user) {
            self::redirectWithError(self::ERROR_USER_NOT_FOUND);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        self::createGoogleUserSession($name, $email, $picture, $user->mode);

        header("Location: /");
        exit();
    }

    private static function createGoogleUserSession(string $name, string $email, string $picture, string $mode): void
    {
        session_regenerate_id(true);
        $_SESSION["login"] =  true;
        $_SESSION["log_type"] = "email";
        $_SESSION["name"] = $name;
        $_SESSION["email"] = $email;
        $_SESSION["picture"] = $picture;
        $_SESSION["mode"] = $mode;
        $_SESSION["last_activity"] = time();
    }
}