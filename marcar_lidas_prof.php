<?php
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_user = intval($_POST['id_user'] ?? 0);
$id_sala = intval($_POST['sala'] ?? 0);

if (!$id_user || !$id_sala) {
    http_response_code(400);
    exit;
}

$stmt = $cone->prepare("
    UPDATE tb_mensagens
    SET lida = 1
    WHERE id_user = ?
      AND sala_id = ?
      AND tipo_remetente = 'user'
");
$stmt->bind_param("ii", $id_user, $id_sala);
$stmt->execute();

echo json_encode(['ok' => true]);
