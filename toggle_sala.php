<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
ensureLoggedIn('prof');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = (int) $_POST['id'];

    $stmt = $cone->prepare("
        UPDATE salas
        SET ativa = IF(ativa = 1, 0, 1)
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: salas.php");
exit;