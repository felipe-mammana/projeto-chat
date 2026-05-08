<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dd = getenv('DB_NAME') ?: 'treinamento';
$port = (int)(getenv('DB_PORT') ?: 3306);

$cone = mysqli_connect($host, $user, $password, $dd, $port);

if (!$cone) {
    http_response_code(500);
    exit("Erro de conexao com o banco de dados.");
}

mysqli_set_charset($cone, 'utf8mb4');
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo');
mysqli_query($cone, "SET time_zone = '-03:00'");
?>
