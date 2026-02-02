<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once "cone.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['email']) && !empty($_POST['senha'])) {

        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $sql = "SELECT id_login, email, senha, tipo FROM tb_login WHERE email = ?";

        $stmt = $cone->prepare($sql);
        if (!$stmt) {
            die("Erro no SQL: " . $cone->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $login = $stmt->get_result()->fetch_assoc();

        if ($login && $senha === $login['senha']) {

            $_SESSION['id_login'] = $login['id_login'];
            $_SESSION['tipo']     = $login['tipo'];

            /* =========================
               MARCAR USUÁRIO COMO ONLINE
            ========================== */
            if ($login['tipo'] === 'user') {
                $up = $cone->prepare("UPDATE tb_user SET online = 1 WHERE id_login = ?");
            } else {
                $up = $cone->prepare("UPDATE tb_professor SET online = 1 WHERE id_login = ?");
            }

            if (!$up) {
                die("Erro ao atualizar online: " . $cone->error);
            }

            $up->bind_param("i", $login['id_login']);
            $up->execute();

            header("Location: salas.php");
            exit;

        } else {
            $erro = "Email ou senha inválidos";
        }

    } else {
        $erro = "Preencha todos os campos";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; }
body {
  margin: 0;
  height: 100vh;
  background: linear-gradient(135deg, #0e5e5e, #168686);
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Poppins', sans-serif;
}
.login-box {
  background: #fff;
  width: 360px;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.25);
}
.login-box h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #0e5e5e;
}
.input-group { margin-bottom: 20px; }
.input-group label {
  display: block;
  font-size: 14px;
  margin-bottom: 6px;
  color: #444;
}
.input-group input {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #ccc;
}
button {
  width: 100%;
  padding: 14px;
  background: #168686;
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
}
.erro {
  background: #ffe5e5;
  color: #b30000;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 15px;
  text-align: center;
}
</style>
</head>
<body>

<div class="login-box">
  <h2>Acesso ao Sistema</h2>

  <?php if ($erro): ?>
    <div class="erro"><?= $erro ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="input-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="input-group">
      <label>Senha</label>
      <input type="password" name="senha" required>
    </div>
    <button type="submit">Entrar</button>
  </form>
</div>

</body>
</html>
