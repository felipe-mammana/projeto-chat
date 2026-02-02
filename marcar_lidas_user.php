<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_login = ensureLoggedIn('user');

$stmt = $cone->prepare("SELECT id_user FROM tb_user WHERE id_login = ?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$id_user = $stmt->get_result()->fetch_assoc()['id_user'];

$id_sala = intval($_POST['sala'] ?? 0);
$id_prof = intval($_POST['id_prof'] ?? 0);

if($id_sala > 0 && $id_prof > 0){
    $stmt = $cone->prepare("
        UPDATE tb_mensagens 
        SET lida = 1 
        WHERE sala_id = ? 
          AND id_prof = ? 
          AND id_user = ? 
          AND tipo_remetente = 'prof'
          AND lida = 0
    ");
    $stmt->bind_param("iii", $id_sala, $id_prof, $id_user);
    $stmt->execute();
}
echo json_encode(['ok' => true]);