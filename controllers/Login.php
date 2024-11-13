<?php 

    namespace Controllers;


    use Models\ActiveDirectory;
    use MVC\Router;
    
    
    use Google_Client;
    use Google_Service_Oauth2;
    use Models\Users;

    class Login{
        public static function login(Router $router){
            
            if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                $ad = new ActiveDirectory($_POST["login"]);
    
                $auth = $ad->auth();
    
                $user= Users::findUser(null,$_POST["login"]["user"]);

                if (!$_POST["login"]["user"] || !$_POST["login"]["password"]) {
                    header("Location: /login?error=1");
                }
                else{
                    if($user){
    
                        $adData = $ad->consultData();
                        if($auth){
                            $_SESSION["login"]=true;
                            $_SESSION["log_type"] = "user";
                            $_SESSION["username"] = $_POST["login"]["user"];
                            $_SESSION["name"] = strtoupper($adData["displayname"]) ;
                            $_SESSION["mode"] = $user->mode;
                            $_SESSION["department"] = $user->department;
                            header("Location: /");
                        }else{
                            header("Location: /login?error=2");
                            
                        }
                        
                    }
    
                }
    
    
    
    
            }
            $router->render("pages/login");
        }
    
    
        public static function logout(){
    
            session_start();
    
            $_SESSION = [];
    
            header("Location: /login");
    
    
        }
    
        public static function redirect(){
            
                    $clientID = '955799568045-esgpav3v7gqvhu57os14at72g11na401.apps.googleusercontent.com';
                $clientSecret = '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';
                $redirectUri = 'https://' . $_SERVER["HTTP_HOST"] . '/redirect';

                // Crear solicitud de cliente para acceder a la API de Google
                $client = new Google_Client();
                $client->setClientId($clientID);
                $client->setClientSecret($clientSecret);
                $client->setRedirectUri($redirectUri);
                $client->addScope("email");
                $client->addScope("profile");

                // Autenticar el código del flujo OAuth de Google
                if (isset($_GET['code'])) {
                    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                    $client->setAccessToken($token['access_token']);

                    // Obtener información del perfil
                    $google_oauth = new Google_Service_Oauth2($client);
                    $google_account_info = $google_oauth->userinfo->get();
                    $email =  $google_account_info->email;
                    $name =  $google_account_info->name;
                    $picture = $google_account_info->picture;
                    
                    // Validar el dominio del correo electrónico
                    if (!isset($_GET["hd"]) || $_GET["hd"] != "asistentevirtualsas.com") {
                        header("Location: /login?error");
                        exit();
                    }

                    session_start();

                    $user = Users::findUser($email);

                    if ($user) {
                        $_SESSION["name"] = $name;
                        $_SESSION["email"] = $email;
                        $_SESSION["picture"] = $picture;
                        $_SESSION["log_type"] = "email";
                        $_SESSION["login"] = true;
                        $_SESSION["mode"] = $user->mode;

                        header("Location: /");
                        exit();
                    } else {
                        // Manejar el caso cuando el usuario no existe en la base de datos
                        header("Location: /login");
                        exit();
                    }
                } else {
                    header("Location: " . $client->createAuthUrl());
                    exit();
                }

            }
        
        
}





?>