<?php


ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../cone.php";

$id_login = ensureLoggedIn('prof');

$id_user = intval($_POST['id_user'] ?? 0);
$sala_id = intval($_POST['sala'] ?? 0);

if(!$id_user || !$sala_id || !isset($_FILES['audio'])){
    http_response_code(400);
    exit("dados invalidos");
}

$maxBytes = 10 * 1024 * 1024;
if ($_FILES['audio']['size'] <= 0 || $_FILES['audio']['size'] > $maxBytes) {
    http_response_code(400);
    exit("audio invalido");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['audio']['tmp_name']);
$allowedMimes = ['audio/webm', 'video/webm', 'application/octet-stream'];
if (!in_array($mime, $allowedMimes, true)) {
    http_response_code(400);
    exit("tipo de audio invalido");
}

// busca id_prof real
$stmt = $cone->prepare("SELECT id_prof FROM tb_professor WHERE id_login = ?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$prof = $stmt->get_result()->fetch_assoc();

if(!$prof){
    http_response_code(403);
    exit("professor invalido");
}

$id_prof = $prof['id_prof'];

// pasta
$dir = __DIR__ . "/";
if(!is_dir($dir)) mkdir($dir, 0777, true);

// nome arquivo
$nome = "audio_" . time() . "_" . random_int(100, 999) . ".webm";
$caminho = $dir . $nome;

if(!move_uploaded_file($_FILES['audio']['tmp_name'], $caminho)){
    http_response_code(500);
    exit("erro upload");
}

// salva no banco
$stmt = $cone->prepare("
    INSERT INTO tb_mensagens
    (id_prof, id_user, sala_id, mensagem, tipo, tipo_remetente, data_envio, lida)
    VALUES (?, ?, ?, ?, 'audio', 'prof', NOW(), 0)
");

$stmt->bind_param("iiis", $id_prof, $id_user, $sala_id, $nome);
$stmt->execute();

echo "OK";
