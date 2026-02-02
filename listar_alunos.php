<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');
require_once "cone.php";

header("Content-Type: application/json");

// valida sala
if (!isset($_GET['sala'])) {
    echo json_encode([]);
    exit;
}

$id_sala = (int) $_GET['sala'];

$sql = "
    SELECT 
    u.id_user,
    u.nome,
    u.online,
    MAX(m.data_envio) AS ultima_msg,
    SUM(
        CASE 
            WHEN m.lida = 0 AND m.tipo_remetente = 'user' 
            THEN 1 ELSE 0 
        END
    ) AS nao_lidas
FROM sala_users su
JOIN tb_user u ON u.id_user = su.id_user
LEFT JOIN tb_mensagens m 
    ON m.id_user = u.id_user 
   AND m.sala_id = su.id_sala
WHERE su.id_sala = ?
GROUP BY u.id_user
ORDER BY ultima_msg DESC


";
$stmt = $cone->prepare($sql);
$stmt->bind_param("i", $id_sala);
$stmt->execute();
$result = $stmt->get_result();

$alunos = [];

while ($row = $result->fetch_assoc()) {
    $alunos[] = $row;
}

echo json_encode($alunos);
