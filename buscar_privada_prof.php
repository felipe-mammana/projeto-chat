<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_login = ensureLoggedIn('prof');

// Busca o ID interno do professor
$stmt = $cone->prepare("SELECT id_prof FROM tb_professor WHERE id_login = ?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$prof = $stmt->get_result()->fetch_assoc();

$id_prof = (int)($prof['id_prof'] ?? 0);
if (!$id_prof) {
    echo json_encode([]);
    exit;
}

$id_user = (int)($_GET['id_user'] ?? 0);
$id_sala = (int)($_GET['sala'] ?? 0);
$lastId  = (int)($_GET['last_id'] ?? 0);

if (!$id_sala) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT 
    id_msg,
    mensagem,
    tipo,
    tipo_remetente,
    data_envio,
    id_user,
    id_prof
FROM tb_mensagens
WHERE sala_id = ?
  AND id_msg > ?
  AND (
        (
            (tipo = 'privada' OR tipo = 'audio')
            AND id_prof = ?
            AND id_user = ?
        )
        OR tipo = 'geral'
      )
ORDER BY id_msg ASC
";


$stmt = $cone->prepare($sql);
$stmt->bind_param("iiii", $id_sala, $lastId, $id_prof, $id_user);
$stmt->execute();

$result = $stmt->get_result();
$mensagens = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($mensagens);