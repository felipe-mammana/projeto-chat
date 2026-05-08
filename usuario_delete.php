<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
ensureLoggedIn();

if ($_SESSION['tipo'] !== 'prof') {
    die("Acesso negado.");
}

$id_login = intval($_POST['id_login']);
if (!$id_login) {
    die("ID invalido.");
}

$cone->begin_transaction();

try {
    $stmt = $cone->prepare("SELECT tipo FROM tb_login WHERE id_login = ?");
    $stmt->bind_param("i", $id_login);
    $stmt->execute();
    $tipo = $stmt->get_result()->fetch_assoc()['tipo'];

    if ($tipo === 'user') {
        $stmt = $cone->prepare("DELETE FROM tb_user WHERE id_login = ?");
    } else {
        $stmt = $cone->prepare("DELETE FROM tb_professor WHERE id_login = ?");
    }
    $stmt->bind_param("i", $id_login);
    $stmt->execute();

    $stmt = $cone->prepare("DELETE FROM tb_login WHERE id_login = ?");
    $stmt->bind_param("i", $id_login);
    $stmt->execute();

    $cone->commit();
    header("Location: admin_usuarios.php");
} catch (Exception $e) {
    $cone->rollback();
    die("Erro ao excluir usuario: " . $e->getMessage());
}
