<?php 

    namespace Controllers;


    use Models\ActiveDirectory;
    use MVC\Router;
    
    
    use Google_Client;
    use Google_Service_Oauth2;

    class Login{
        public static function login(Router $router){
            
            if($_SERVER["REQUEST_METHOD"] === "POST"){
    
                $ad = new ActiveDirectory($_POST["login"]);
    
                $auth = $ad->auth();
    
                if (!$_POST["login"]["user"] || !$_POST["login"]["password"]) {
                    header("Location: /login?error=1");
                }
                else{
    
                    $adData = $ad->consultData();
                    
                    if($auth){
                        $_SESSION["login"]=true;
                        $_SESSION["log_type"] = "user";
                        $_SESSION["username"] = $_POST["login"]["user"];
                        $_SESSION["name"] = strtoupper($adData["displayname"]) ;
                        header("Location: /");
                    }else{
                        header("Location: /login?error=2");
                        
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
    
            // init configuration 
            $clientID = '955799568045-esgpav3v7gqvhu57os14at72g11na401.apps.googleusercontent.com';
            $clientSecret = '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';
            $redirectUri = 'http://'. $_SERVER["HTTP_HOST"] .'/redirect';
            // create Client Request to access Google API 
            $client = new Google_Client();
            $client->setClientId($clientID);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirectUri);
            $client->addScope("email");
            $client->addScope("profile");
            // authenticate code from Google OAuth Flow 
            if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token['access_token']);
            
            // get profile info 
            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            $email =  $google_account_info->email;
            $name =  $google_account_info->name;
            $picture = $google_account_info->picture;
            
    
            if(is_null($_GET["hd"]) || !$_GET["hd"] === "asistentevirtualsas.com" ){
                header("Location : /login?error");
            }
            session_start();
    
    
            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;
            $_SESSION["picture"] = $picture;
            $_SESSION["log_type"] = "email";
            $_SESSION["login"] = true;
            
    
            header("Location: /");
    
            // now you can use this profile info to create account in your website and make user logged in. 
            } else {
                header("Location: " . $client->createAuthUrl());
            }
    
    
        }


    }





?>