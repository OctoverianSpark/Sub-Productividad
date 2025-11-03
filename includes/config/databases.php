
<?php



function conectarDB($schema){
        $db = new mysqli(
            $_ENV["DB_HOST"] ,
            $_ENV["DB_USER"] ,
            $_ENV["DB_PASS"] ,
            $schema,
            $_ENV["DB_PORT"]
        );
        return $db;

    }


   

?>