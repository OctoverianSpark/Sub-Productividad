<?php

namespace Models;

use MVC\Config;

class Users extends BaseModel
{
    protected static $tabla = "users";
    protected static $columnasDB = ["id", "user", "email", "mode"];
    protected static $omitirVaciosAlActualizar = true;
    public $id, $user, $email, $mode;

    public function __construct($args = [])
    {
        $this->id = $args["id"] ?? null;
        $this->user = $args["user"] ?? null;
        $this->email = $args["email"] ?? null;
        $this->mode = $args["mode"] ?? null;
    }

    public static function all(): array
    {
        return self::consultarSQL("SELECT * FROM " . static::$tabla . " ORDER BY mode ASC");
    }

    public function validar(): array
    {
        $this->errores = [];

        if (!trim((string) $this->user) && !trim((string) $this->email)) {
            $this->errores[] = "Debes indicar el usuario de dominio o el correo";
        }
        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = "El correo no es valido";
        }
        $roles = array_keys(Config::get("roles.disponibles", []));
        if (!in_array(strtolower((string) $this->mode), $roles, true)) {
            $this->errores[] = "El permiso seleccionado no es valido";
        }
        return $this->errores;
    }

    public static function findUser(?string $mail = null, ?string $user = null): ?static
    {
        if ($mail) {
            $resultado = self::consultarSQL("SELECT * FROM " . self::$tabla . " WHERE email = ? LIMIT 1", [$mail]);
        } elseif ($user) {
            $resultado = self::consultarSQL("SELECT * FROM " . self::$tabla . " WHERE user = ? LIMIT 1", [$user]);
        } else {
            return null;
        }
        return $resultado[0] ?? null;
    }
}
