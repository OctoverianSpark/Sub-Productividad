<?php

namespace Models;

use Adldap\Adldap;
use Exception;

class ActiveDirectory
{
    protected $ad;
    protected $user;
    protected $password;
    protected $errores = [];

    public function __construct($args = [])
    {
        // Credenciales tomadas de .env (ver .env.example). Nunca escribirlas en el codigo.
        $config = [
            "account_suffix" => $_ENV["AD_ACCOUNT_SUFFIX"],
            "domain_controllers" => array_map("trim", explode(",", $_ENV["AD_DOMAIN_CONTROLLERS"])),
            "base_dn" => $_ENV["AD_BASE_DN"],
            "admin_username" => $_ENV["AD_ADMIN_USERNAME"],
            "admin_password" => $_ENV["AD_ADMIN_PASSWORD"],
        ];

        $this->ad = new Adldap($config);
        $this->user = $args["user"] ?? "";
        $this->password = $args["password"] ?? "";
    }

    // Autentica al usuario en Active Directory
    public function auth()
    {
        try {
            return $this->ad->authenticate($this->user, $this->password, true);
        } catch (Exception $err) {
            $this->errores[] = $err->getMessage();
            return false;
        }
    }

    //Consulta los datos del usuario autenticado en Active Directory.
    public function consultData()
    {
        $data = $this->ad->search()->where("cn", "=", $this->user)->get();
        return $data ? $data[0] : null;
    }

    //Retorna los erorres resgistrados
    public function getErrores()
    {
        return $this->errores;
    }
}
