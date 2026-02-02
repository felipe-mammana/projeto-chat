<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
ensureLoggedIn();

if ($_SESSION['tipo'] !== 'prof') {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_usuarios.php");
    exit;
}

$nome  = trim($_POST['nome']);
$email = trim($_POST['email']);
$senha = trim($_POST['senha']);
$tipo  = $_POST['tipo'];

if (!$nome || !$email || !$senha || !in_array($tipo, ['user', 'prof'])) {
    die("Dados inválidos.");
}

$cone->begin_transaction();

try {
    // 1️⃣ LOGIN
    $stmt = $cone->prepare("
        INSERT INTO tb_login (email, senha, tipo, ativo)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->bind_param("sss", $email, $senha, $tipo);
    $stmt->execute();

    $id_login = $stmt->insert_id;

    // 2️⃣ PERFIL
    if ($tipo === 'user') {
        $stmt = $cone->prepare("
            INSERT INTO tb_user (id_login, nome, online)
            VALUES (?, ?, 0)
        ");
    } else {
        $stmt = $cone->prepare("
            INSERT INTO tb_professor (id_login, nome, online)
            VALUES (?, ?, 0)
        ");
    }

    $stmt->bind_param("is", $id_login, $nome);
    $stmt->execute();

    $cone->commit();
    header("Location: admin_usuarios.php");

} catch (Exception $e) {
    $cone->rollback();
    die("Erro ao criar usuário: " . $e->getMessage());
}
