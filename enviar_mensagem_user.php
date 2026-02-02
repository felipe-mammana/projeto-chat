<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');
require_once "helpers.php";
require_once "cone.php";

// ... (mantenha os inis e requires)

$id_login = ensureLoggedIn('user');

// Busca ID do usuário
$stmt = $cone->prepare("SELECT id_user FROM tb_user WHERE id_login = ?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$id_user = $stmt->get_result()->fetch_assoc()['id_user'] ?? 0;

if(!$id_user) exit;

// PEGA OS DADOS DO POST (Enviados pelo seu JS)
$id_sala = (int)($_POST['sala'] ?? 0);
$id_prof = (int)($_POST['id_prof'] ?? 0); // <-- MUDANÇA AQUI
$mensagem = trim($_POST['mensagem'] ?? '');

if(!$id_sala || !$id_prof || $mensagem === '') exit("Dados incompletos");

/* Logica da última mensagem geral da sala */
$stmt = $cone->prepare("SELECT data_envio FROM tb_mensagens WHERE tipo='geral' AND sala_id = ? ORDER BY data_envio DESC LIMIT 1");
$stmt->bind_param("i", $id_sala);
$stmt->execute();
$geral = $stmt->get_result()->fetch_assoc();
$tempo = $geral ? (time() - strtotime($geral['data_envio'])) : null;

/* INSERIR MENSAGEM - Agora vinculada ao PROFESSOR CORRETO */
$sql = "INSERT INTO tb_mensagens 
        (id_prof, id_user, sala_id, mensagem, tipo, tipo_remetente, data_envio, tempo_resposta)
        VALUES (?, ?, ?, ?, 'privada', 'user', NOW(), ?)";

$stmt = $cone->prepare($sql);
$stmt->bind_param("iiisi", $id_prof, $id_user, $id_sala, $mensagem, $tempo);
$stmt->execute();

echo "ok";