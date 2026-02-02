<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
ensureLoggedIn();

if ($_SESSION['tipo'] !== 'prof') {
    die("Acesso negado.");
}

$id_login = intval($_POST['id_login']);
$nome  = trim($_POST['nome']);
$email = trim($_POST['email']);
$tipo  = $_POST['tipo'];

if (!$id_login || !$nome || !$email || !in_array($tipo, ['user', 'prof'])) {
    die("Dados inválidos.");
}

$cone->begin_transaction();

try {
    // Tipo atual
    $stmt = $cone->prepare("SELECT tipo FROM tb_login WHERE id_login = ?");
    $stmt->bind_param("i", $id_login);
    $stmt->execute();
    $atual = $stmt->get_result()->fetch_assoc()['tipo'];

    // Atualiza login
    $stmt = $cone->prepare("
        UPDATE tb_login SET email = ?, tipo = ?
        WHERE id_login = ?
    ");
    $stmt->bind_param("ssi", $email, $tipo, $id_login);
    $stmt->execute();

    // Se mudou tipo → mover registro
    if ($atual !== $tipo) {
        if ($atual === 'user') {
            $cone->query("DELETE FROM tb_user WHERE id_login = $id_login");
            $stmt = $cone->prepare("
                INSERT INTO tb_professor (id_login, nome, online)
                VALUES (?, ?, 0)
            ");
        } else {
            $cone->query("DELETE FROM tb_professor WHERE id_login = $id_login");
            $stmt = $cone->prepare("
                INSERT INTO tb_user (id_login, nome, online)
                VALUES (?, ?, 0)
            ");
        }
        $stmt->bind_param("is", $id_login, $nome);
        $stmt->execute();
    } else {
        // Apenas atualiza nome
        if ($tipo === 'user') {
            $stmt = $cone->prepare("UPDATE tb_user SET nome = ? WHERE id_login = ?");
        } else {
            $stmt = $cone->prepare("UPDATE tb_professor SET nome = ? WHERE id_login = ?");
        }
        $stmt->bind_param("si", $nome, $id_login);
        $stmt->execute();
    }

    $cone->commit();
    header("Location: admin_usuarios.php");

} catch (Exception $e) {
    $cone->rollback();
    die("Erro ao editar usuário: " . $e->getMessage());
}
