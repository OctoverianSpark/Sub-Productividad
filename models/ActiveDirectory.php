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
        $config = [
            'account_suffix' => "@asistentevirtualsas.com",
            'domain_controllers' => array("AV-SRV-DC1.asistentevirtualsas.com"),
            'base_dn' => 'dc=AsistenteVirtualSAS,dc=com',
            'admin_username' => 'Administrador',
            'admin_password' => 'Venezu22366@',
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
