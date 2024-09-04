<?php 


namespace Models;
use Adldap\Adldap;




class ActiveDirectory{


    protected static $ad;

    public $user;
    public $password;

    protected static $errores = [];
    public function __construct($args = []){
        $config = [
            
            

            'account_suffix' => "@asistentevirtualsas.com",

            'domain_controllers' => array("AV-SRV-DC1.asistentevirtualsas.com"),

            'base_dn' => 'dc=AV-SRV-DC1 dc=AsistenteVirtualSAS,dc=com',

            'admin_username' => 'Administrador',

            'admin_password' => 'Venezu22366@',
        ];

        self::$ad = new Adldap($config);
        $this->user = $args["user"];
        $this->password = $args["password"];

    }

    public function auth(){

        $auth = self::$ad->authenticate($this->user,$this->password,true);
        if($auth){
            return true;
        }
        return false;

    }


    public function consultData(){

        $data = self::$ad->search()->where("cn","=",$this->user)->get();
        if(!$data){
            return;
        }else{
            return array_shift($data);
        }


    }



}





?>