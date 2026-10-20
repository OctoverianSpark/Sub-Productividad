<?php

function conectarDB(string $schema): mysqli
{
    // Cualquier error de MySQL lanza una excepcion en vez de fallar en silencio
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $db = new mysqli(
        $_ENV["DB_HOST"],
        $_ENV["DB_USER"],
        $_ENV["DB_PASS"],
        $schema,
        (int) $_ENV["DB_PORT"]
    );
    $db->set_charset("utf8mb4");

    return $db;
}
