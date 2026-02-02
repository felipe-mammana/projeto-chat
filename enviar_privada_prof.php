<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_login = ensureLoggedIn('prof');

/* ID do professor */
$stmt = $cone->prepare("SELECT id_prof FROM tb_professor WHERE id_login=?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$prof = $stmt->get_result()->fetch_assoc();
$id_prof = $prof['id_prof'] ?? 0;
if(!$id_prof) exit('erro');

/* Parâmetros */
$id_user = (int)($_POST['id_user'] ?? 0);
$id_sala = (int)($_POST['sala'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

if(!$id_user || !$id_sala || !$mensagem) exit('erro');

/* Inserir mensagem privada */
$stmt = $cone->prepare("
INSERT INTO tb_mensagens 
(id_user, id_prof, sala_id, mensagem, tipo, tipo_remetente, data_envio) 
VALUES (?, ?, ?, ?, 'privada', 'prof', NOW())
");
$stmt->bind_param("iiis", $id_user, $id_prof, $id_sala, $mensagem);
$stmt->execute();

echo "ok";
