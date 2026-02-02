<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

require_once "helpers.php";
require_once "cone.php";

header("Content-Type: application/json");

/* aluno logado */
$id_login = ensureLoggedIn('user');

/* valida sala */
if (!isset($_GET['sala'])) {
    echo json_encode([]);
    exit;
}

$id_sala = (int) $_GET['sala'];

/* busca id_user */
$stmtUser = $cone->prepare("
    SELECT id_user 
    FROM tb_user 
    WHERE id_login = ?
");
$stmtUser->bind_param("i", $id_login);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();

$id_user = $user['id_user'] ?? 0;
if (!$id_user) {
    echo json_encode([]);
    exit;
}

/*
✅ LÓGICA IDÊNTICA AO listar_alunos.php
*/
$sql = "
SELECT 
    p.id_prof,
    p.nome,
    p.online,
    MAX(m.data_envio) AS ultima_msg,
    SUM(
        CASE 
            WHEN m.lida = 0 
             AND m.tipo_remetente = 'prof'
             AND m.id_user = ?
            THEN 1 ELSE 0
        END
    ) AS nao_lidas
FROM sala_profs sp
JOIN tb_professor p 
    ON p.id_prof = sp.id_prof
LEFT JOIN tb_mensagens m 
    ON m.id_prof = p.id_prof
   AND m.sala_id = sp.id_sala
WHERE sp.id_sala = ?
GROUP BY p.id_prof
ORDER BY ultima_msg DESC
";

$stmt = $cone->prepare($sql);
$stmt->bind_param("ii", $id_user, $id_sala);
$stmt->execute();

$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
