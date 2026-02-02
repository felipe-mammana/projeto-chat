<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_login = ensureLoggedIn('user');

/* 1. Busca ID do aluno */
$stmt = $cone->prepare("SELECT id_user FROM tb_user WHERE id_login = ?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$id_user = $res['id_user'] ?? 0;

/* 2. Pega parâmetros do GET */
$id_sala = (int)($_GET['sala'] ?? 0);
$id_prof = (int)($_GET['prof'] ?? 0); // Captura o professor selecionado
$lastId  = (int)($_GET['last_id'] ?? 0);

if(!$id_user || !$id_sala) exit(json_encode([]));

/* 3. Busca mensagens: Privadas (Aluno <-> Professor selecionado) + Gerais da Sala */
$sql = "
SELECT id_msg, mensagem, tipo, tipo_remetente, data_envio
FROM tb_mensagens
WHERE sala_id = ?
  AND (
        (
          tipo IN ('privada', 'audio')
          AND id_user = ?
          AND id_prof = ?
        )
        OR tipo = 'geral'
      )
  AND id_msg > ?
ORDER BY id_msg ASC
";


$stmt = $cone->prepare($sql);
// Agora passamos o id_prof no bind_param
$stmt->bind_param("iiii", $id_sala, $id_user, $id_prof, $lastId);
$stmt->execute();

$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);