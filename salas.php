<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');

ensureLoggedIn();

$tipo = $_SESSION['tipo'];
$id_login = $_SESSION['id_login'];

/* ==========================
   PEGA ID_PROF DO LOGADO (SE FOR PROF)
========================== */
$id_prof_logado = null;
if ($tipo === 'prof') {
    $stmt = $cone->prepare("
        SELECT id_prof 
        FROM tb_professor 
        WHERE id_login = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_login);
    $stmt->execute();
    $id_prof_logado = $stmt->get_result()->fetch_assoc()['id_prof'] ?? null;
}

/* ==========================
   CRIAR SALA (PROF)
========================== */
if ($tipo === 'prof' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nome'])) {

    $cone->begin_transaction();

    $nomeSala = trim($_POST['nome']);

    // ---- CRIA A SALA
    $stmt = $cone->prepare("
        INSERT INTO salas (nome, criado_por) 
        VALUES (?, ?)
    ");
    $stmt->bind_param("si", $nomeSala, $id_login);
    $stmt->execute();
    $sala_id = $stmt->insert_id;

    // ---- INSERE O PROFESSOR CRIADOR
    $stmt = $cone->prepare("
        INSERT INTO sala_profs (id_sala, id_prof)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $sala_id, $id_prof_logado);
    $stmt->execute();

    // ---- ALUNOS
    if (!empty($_POST['users'])) {
        $stmt = $cone->prepare("
            INSERT INTO sala_users (id_sala, id_user) 
            VALUES (?, ?)
        ");

        foreach ($_POST['users'] as $u) {
            $stmt->bind_param("ii", $sala_id, $u);
            $stmt->execute();
        }
    }

    // ---- OUTROS PROFESSORES
    if (!empty($_POST['profs'])) {
        $stmt = $cone->prepare("
            INSERT IGNORE INTO sala_profs (id_sala, id_prof)
            VALUES (?, ?)
        ");

        foreach ($_POST['profs'] as $p) {
            $stmt->bind_param("ii", $sala_id, $p);
            $stmt->execute();
        }
    }

    if (empty($_POST['users']) && empty($_POST['profs'])) {
        $cone->rollback();
        die("Selecione ao menos um participante.");
    }

    $cone->commit();
    header("Location: salas.php");
    exit;
}

/* ==========================
   NOME DO USUÁRIO
========================== */
if ($tipo === 'prof') {
    $stmt = $cone->prepare("
        SELECT nome 
        FROM tb_professor 
        WHERE id_login = ?
        LIMIT 1
    ");
} else {
    $stmt = $cone->prepare("
        SELECT nome 
        FROM tb_user 
        WHERE id_login = ?
        LIMIT 1
    ");
}
$stmt->bind_param("i", $id_login);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

/* ==========================
   DADOS PARA SELECT
========================== */
$alunos = $cone->query("
    SELECT id_user, nome 
    FROM tb_user 
    ORDER BY nome
");

$professores = $cone->query("
    SELECT 
        p.id_prof,
        p.nome
    FROM tb_professor p
    ORDER BY p.nome
");

/* ==========================
   BUSCAR SALAS
========================== */
if ($tipo === 'prof') {
    $stmt = $cone->prepare("
        SELECT DISTINCT s.*
        FROM salas s
        LEFT JOIN sala_profs sp ON sp.id_sala = s.id
        WHERE s.criado_por = ?
           OR sp.id_prof = ?
        ORDER BY s.id DESC
    ");
    $stmt->bind_param("ii", $id_login, $id_prof_logado);
    $stmt->execute();
    $salas = $stmt->get_result();
} else {
    $stmt = $cone->prepare("
        SELECT DISTINCT s.*
        FROM salas s
        JOIN sala_users su ON su.id_sala = s.id
        JOIN tb_user u ON u.id_user = su.id_user
        WHERE u.id_login = ?
          AND s.ativa = 1
    ");
    $stmt->bind_param("i", $id_login);
    $stmt->execute();
    $salas = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Salas de Treinamento</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #075E54;
    --primary-light: #128C7E;
    --accent: #25D366;
    --bg-soft: #f0f2f5;
    --shadow: 0 10px 30px rgba(0,0,0,0.08);
}

* { box-sizing: border-box; font-family: 'Inter', sans-serif; transition: all 0.3s ease; }
body { margin: 0; background: var(--bg-soft); color: #333; height: 100vh; overflow: hidden; }

.app { display: flex; height: 100vh; }

/* SIDEBAR */
.sidebar { 
    width: 260px; 
    background: linear-gradient(180deg, var(--primary) 0%, #054c44 100%);
    color: #fff; padding: 40px 20px; display: flex; flex-direction: column;
    flex-shrink: 0;
}
.sidebar.closed { width: 80px; padding: 40px 15px; }
.sidebar.closed .logo span:last-child, .sidebar.closed nav a span:last-child { display: none; }

.logo { font-size: 1.5rem; font-weight: 800; margin-bottom: 40px; display: flex; align-items: center; gap: 15px; cursor: pointer; white-space: nowrap; }

.sidebar nav a { 
            text-decoration: none; color: rgba(255,255,255,0.7);
            padding: 14px 15px; margin-bottom: 8px; border-radius: 12px; display: flex; align-items: center; gap: 12px;
        }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: #fff; }
/* CONTENT */
.content { flex: 1; padding: 40px; overflow-y: auto; }
.topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.badge { background: #fff; color: var(--primary); padding: 6px 15px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; box-shadow: var(--shadow); }

/* LAYOUT GRID */
.layout { display: grid; grid-template-columns: 420px 1fr; gap: 40px; align-items: start; }

/* FILTROS */
.filtro-salas { 
    display: flex; gap: 8px; background: #fff; padding: 6px; 
    border-radius: 12px; width: fit-content; margin-bottom: 25px; box-shadow: var(--shadow);
}
.filter-btn { 
    border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer;
    background: transparent; color: #666; font-weight: 600; font-size: 0.85rem;
}
.filter-btn.active { background: var(--primary); color: white; }

/* FORM CARD */
.card { background: white; border-radius: 24px; padding: 30px; box-shadow: var(--shadow); }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 0.75rem; font-weight: 700; color: #888; margin-bottom: 8px; text-transform: uppercase; }
input[name="nome"] { 
    width: 100%; padding: 15px; border-radius: 12px; border: 2px solid #eee; font-size: 1rem; outline: none;
}
input[name="nome"]:focus { border-color: var(--primary); }

/* PARTICIPANTES */
.participantes-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px; }
.participantes-col { background: #f8fafc; border: 1px solid #eef2f6; border-radius: 15px; padding: 15px; height: 200px; overflow-y: auto; }
.group-title { font-size: 0.7rem; font-weight: 800; color: var(--primary); display: block; margin-bottom: 10px; text-transform: uppercase; }
.option { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; cursor: pointer; font-size: 0.85rem; }
.option input { accent-color: var(--primary); width: 16px; height: 16px; }

/* SALA CARDS */
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.sala-card { border-left: 6px solid var(--accent); min-height: 180px; display: flex; flex-direction: column; justify-content: space-between; }
.sala-card.desativada { border-left-color: #cbd5e1; opacity: 0.7; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
.status-ativa { background: var(--accent); }
.status-inativa { background: #94a3b8; }

.enter { 
    background: var(--primary); color: white; text-decoration: none; text-align: center;
    padding: 14px; border-radius: 14px; font-weight: 600; font-size: 0.9rem; margin-top: 15px;
}
.enter:hover { background: var(--primary-light); }

.btn-criar {
    width: 100%; background: var(--primary); color: white; border: none;
    padding: 16px; border-radius: 14px; font-weight: 700; cursor: pointer; margin-top: 20px;
}

@media(max-width: 1100px) { .layout { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="logo" onclick="toggleSidebar()">
            <span>🎓</span> <span>Treinamento</span>
        </div>
        <nav>
            <a class="active"><span>📂</span> <span>Salas</span></a>
            <?php if ($tipo === 'prof'): ?>
            <a href = "admin_usuarios.php"><span >👤</span> <span>Usuarios</span></a>
             <?php endif; ?>
            <a><span>📊</span> <span>Relatórios</span></a>
            <a href="logout.php" style="margin-top: auto; color: #f87171;">
                <span>🚪</span> <span>Sair</span>
            </a>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
    <div>
        <h1>Salas</h1>
        <p style="margin: 4px 0 0; color:#666; font-weight:500;">
            Olá, <?= htmlspecialchars($usuario['nome']) ?>
        </p>
    </div>

    <span class="badge">
        <?= $tipo === 'prof' ? 'Modo Professor' : 'Modo Aluno' ?>
    </span>
</header>


        <div class="layout">
            <?php if ($tipo === 'prof'): ?>
            <aside>
                <div class="filtro-salas">
                    <button class="filter-btn active" onclick="filtrarSalas('todas', this)">Todas</button>
                    <button class="filter-btn" onclick="filtrarSalas('ativas', this)">Ativas</button>
                    <button class="filter-btn" onclick="filtrarSalas('desativadas', this)">Inativas</button>
                </div>

                <section class="card">
                    <h3>Criar nova sala</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Identificação da Sala</label>
                            <input name="nome" placeholder="Digite o nome da sala..." required>
                        </div>

                        <div class="form-group">
                            <label>Participantes</label>
                            <div class="participantes-grid">
                                <div class="participantes-col">
                                    <span class="group-title">Professores</span>
                                    <?php $professores->data_seek(0); while($p = $professores->fetch_assoc()): ?>
                                        <label class="option">
                                            <input type="checkbox" name="profs[]" value="<?= $p['id_prof'] ?>">
                                            <span><?= htmlspecialchars($p['nome']) ?></span>
                                        </label>
                                    <?php endwhile; ?>
                                </div>
                                <div class="participantes-col">
                                    <span class="group-title">Alunos</span>
                                    <?php $alunos->data_seek(0); while($a = $alunos->fetch_assoc()): ?>
                                        <label class="option">
                                            <input type="checkbox" name="users[]" value="<?= $a['id_user'] ?>">
                                            <span><?= htmlspecialchars($a['nome']) ?></span>
                                        </label>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn-criar">Criar Ambiente</button>
                    </form>
                </section>
            </aside>
            <?php endif; ?>

            <section class="grid">
                <?php while($s = $salas->fetch_assoc()): ?>
                <div class="card sala-card <?= $s['ativa'] ? '' : 'desativada' ?>">
                    <div>
                        <strong><?= htmlspecialchars($s['nome']) ?></strong>
                        <div style="font-size: 0.8rem; color: #888; margin-top: 5px;">
                            <span class="status-dot <?= $s['ativa'] ? 'status-ativa' : 'status-inativa' ?>"></span>
                            <?= $s['ativa'] ? 'Disponível' : 'Arquivada' ?>
                        </div>
                    </div>
                    
                    <div class="actions" style="display: flex; flex-direction: column; gap: 8px;">
                        <a class="enter" href="<?= $tipo === 'prof' ? 'tela_prof.php' : 'chat_user.php' ?>?sala=<?= $s['id'] ?>">Entrar na Sala</a>
                        <?php if ($tipo === 'prof'): ?>
                        <form method="POST" action="toggle_sala.php">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" style="width: 100%; border: 1px solid #eee; background: none; padding: 8px; border-radius: 10px; cursor: pointer; font-size: 0.75rem; color: #999;">
                                <?= $s['ativa'] ? 'Desativar' : 'Ativar' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </section>
        </div>
    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('closed');
}

function filtrarSalas(tipo, botao) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    botao.classList.add('active');

    document.querySelectorAll('.sala-card').forEach(card => {
        const text = card.innerText.toLowerCase();
        if (tipo === 'todas') card.style.display = 'flex';
        else if (tipo === 'ativas' && text.includes('disponível')) card.style.display = 'flex';
        else if (tipo === 'desativadas' && text.includes('arquivada')) card.style.display = 'flex';
        else card.style.display = 'none';
    });
}

setInterval(() => {
    fetch("ping_online.php", { method: "POST" });
}, 15000); // a cada 15 segundos
</script>
</body>
</html>