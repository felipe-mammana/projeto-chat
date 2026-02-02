<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../cone.php";

$id_login = ensureLoggedIn('user');

$id_prof = intval($_POST['id_prof'] ?? 0);
$sala_id = intval($_POST['sala'] ?? 0);

if (!$id_prof || !$sala_id || !isset($_FILES['audio'])) {
    http_response_code(400);
    exit("dados invalidos");
}

/* BUSCA ID REAL DO USER */
$stmt = $cone->prepare("SELECT id_user FROM tb_user WHERE id_login = ?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(403);
    exit("user invalido");
}

$id_user = $user['id_user'];

/* PASTA (a própria audios/) */
$dir = __DIR__ . "/";

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

/* NOME DO ARQUIVO */
$nome = "audio_" . time() . "_" . rand(100,999) . ".webm";
$caminho = $dir . $nome;

/* MOVE O ARQUIVO */
if (!move_uploaded_file($_FILES['audio']['tmp_name'], $caminho)) {
    http_response_code(500);
    exit("erro upload");
}

/* SALVA NO BANCO */
$stmt = $cone->prepare("
    INSERT INTO tb_mensagens
    (id_prof, id_user, sala_id, mensagem, tipo, tipo_remetente, data_envio, lida)
    VALUES (?, ?, ?, ?, 'audio', 'user', NOW(), 0)
");

$stmt->bind_param("iiis", $id_prof, $id_user, $sala_id, $nome);
$stmt->execute();

echo "OK";
