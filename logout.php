<?php
require_once "cone.php";
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (isset($_SESSION['id_login'], $_SESSION['tipo'])) {
    if ($_SESSION['tipo'] === 'user') {
        $stmt = $cone->prepare("UPDATE tb_user SET online = 0 WHERE id_login = ?");
    } else {
        $stmt = $cone->prepare("UPDATE tb_professor SET online = 0 WHERE id_login = ?");
    }

    $stmt->bind_param("i", $_SESSION['id_login']);
    $stmt->execute();
}

session_destroy();
header("Location: login.php");
exit;
