<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
ensureLoggedIn();

$id_login = $_SESSION['id_login'];
$tipo     = $_SESSION['tipo'];

$now = date('Y-m-d H:i:s');

if ($tipo === 'user') {
    $sql = "UPDATE tb_user SET last_ping = NOW() WHERE id_login = ?";
} else {
    $sql = "UPDATE tb_professor SET last_ping = NOW() WHERE id_login = ?";
}

$stmt = $cone->prepare($sql);
$stmt->bind_param("i", $id_login);
$stmt->execute();


echo json_encode(["status" => "ok"]);

