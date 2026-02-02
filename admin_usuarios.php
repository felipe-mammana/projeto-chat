<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
ensureLoggedIn();

// Verifica se é professor (ou se você tiver um nível 'admin')
if ($_SESSION['tipo'] !== 'prof') {
    die("Acesso negado. Apenas administradores podem acessar esta página.");
}

/* ==========================
   BUSCA CONSOLIDADA (SQL)
   Unindo Login + Perfil (User ou Prof)
========================== */
$sql = "
    SELECT 
    l.id_login, 
    l.email, 
    l.tipo, 
    l.ativo, 
    l.criado_em,

    CASE 
        WHEN l.tipo = 'user' THEN u.nome
        WHEN l.tipo = 'prof' THEN p.nome
        ELSE '—'
    END AS nome_completo,

    CASE 
        WHEN l.tipo = 'user'
             AND u.last_ping IS NOT NULL
             AND u.last_ping >= (NOW() - INTERVAL 30 SECOND)
        THEN 1

        WHEN l.tipo = 'prof'
             AND p.last_ping IS NOT NULL
             AND p.last_ping >= (NOW() - INTERVAL 30 SECOND)
        THEN 1

        ELSE 0
    END AS is_online

FROM tb_login l
LEFT JOIN tb_user u ON l.id_login = u.id_login
LEFT JOIN tb_professor p ON l.id_login = p.id_login
ORDER BY l.id_login DESC
";
$usuarios = $cone->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Usuários</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #075E54;
            --primary-light: #128C7E;
            --bg-soft: #f0f2f5;
            --shadow: 0 10px 30px rgba(0,0,0,0.05);
            --danger: #ef4444;
            --success: #22c55e;
        }

        * { box-sizing: border-box; font-family: 'Inter', sans-serif; transition: all 0.2s ease; }
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
        .sidebar.closed span:not(.icon) { display: none; }

        .logo { font-size: 1.5rem; font-weight: 800; margin-bottom: 40px; display: flex; align-items: center; gap: 15px; cursor: pointer; }

        .sidebar nav a { 
            text-decoration: none; color: rgba(255,255,255,0.7);
            padding: 14px 15px; margin-bottom: 8px; border-radius: 12px; display: flex; align-items: center; gap: 12px;
        }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.15); color: #fff; }

        /* CONTENT */
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* FILTROS */
        .filtro-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .filtro-salas { 
            display: flex; gap: 8px; background: #fff; padding: 6px; 
            border-radius: 12px; box-shadow: var(--shadow);
        }
        .filter-btn { 
            border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer;
            background: transparent; color: #666; font-weight: 600; font-size: 0.85rem;
        }
        .filter-btn.active { background: var(--primary); color: white; }

        /* TABELA */
        .card-table { background: white; border-radius: 24px; box-shadow: var(--shadow); overflow: hidden; border: 1px solid rgba(0,0,0,0.03); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 18px 25px; font-size: 0.75rem; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }
        td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        tr:hover { background: #fcfdfe; }

        /* BADGES E STATUS */
        .badge { padding: 5px 12px; border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
        .badge-prof { background: #e0f2fe; color: #0369a1; }
        .badge-user { background: #f0fdf4; color: #15803d; }
        
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .status-online { background: var(--success); box-shadow: 0 0 8px var(--success); }
        .status-offline { background: #cbd5e1; }
        .btn-cancel{
            border: none; background: #f1f5f9; padding: 8px 12px; border-radius: 8px; 
            cursor: pointer; font-size: 0.08rem; font-weight: 600; color: #475569;
        }
        .btn-action { 
            border: none; background: #f1f5f9; padding: 8px 12px; border-radius: 8px; 
            cursor: pointer; font-size: 0.8rem; font-weight: 600; color: #475569;
        }
        .btn-toggle { background: #fee2e2; color: var(--danger); }
        .btn-toggle.active { background: #f0fdf4; color: var(--success); }

        .btn-novo {
            background: var(--primary); color: white; border: none; padding: 12px 24px;
            border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(7, 94, 84, 0.2);
        }
        .modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.modal.show { display: flex; }

.modal-box {
    background: #fff;
    padding: 30px;
    border-radius: 20px;
    width: 380px;
    box-shadow: var(--shadow);
}

.modal-box h2 { margin-top: 0; }

.modal-box input, 
.modal-box select {
    width: 100%;
    padding: 12px;
    margin-bottom: 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-confirm {
    background: var(--primary);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
}

.btn-danger {
    background: var(--danger);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
}

.modal-box.danger h2 { color: var(--danger); }

    </style>
</head>
<body>

<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="logo" onclick="toggleSidebar()">
            <span class="icon">🎓</span> <span>Painel Admin</span>
        </div>
        <nav>
            <a href="salas.php"> <span class="icon">📂</span> <span>Salas</span> </a>
            <a class="active"> <span class="icon">👥</span> <span>Usuários</span> </a>
            <a> <span class="icon">📊</span> <span>Relatórios</span> </a>
            <a href="logout.php" style="margin-top: auto; color: #f87171;">
                <span class="icon">🚪</span> <span>Sair</span>
            </a>
        </nav>
    </aside>

    <main class="content">
        <header class="topbar">
            <h1>Gestão de Membros</h1>
            <button class="btn-novo" onclick="abrirModal('modalAdicionar')">
            + Adicionar Usuário
            </button>

        </header>

        <div class="filtro-container">
            <div class="filtro-salas">
                <button class="filter-btn active" onclick="filtrarTabela('todos', this)">Todos</button>
                <button class="filter-btn" onclick="filtrarTabela('prof', this)">Professores</button>
                <button class="filter-btn" onclick="filtrarTabela('user', this)">Alunos</button>
            </div>
            <input type="text" id="buscaInput" onkeyup="buscarNome()" placeholder="Pesquisar por nome ou email..." 
                   style="padding: 10px 20px; border-radius: 12px; border: 1px solid #ddd; width: 300px; outline: none;">
        </div>

        <section class="card-table">
            <table>
                <thead>
                    <tr>
                        <th>Nome / Email</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaCorpo">
                    <?php while($row = $usuarios->fetch_assoc()): ?>
                    <tr class="user-row" data-tipo="<?= $row['tipo'] ?>">
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="status-dot <?= $row['is_online'] ? 'status-online' : 'status-offline' ?>" 
                                     title="<?= $row['is_online'] ? 'Online agora' : 'Offline' ?>"></div>
                                <div>
                                    <strong style="display: block;"><?= htmlspecialchars($row['nome_completo']) ?></strong>
                                    <small style="color: #64748b;"><?= htmlspecialchars($row['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $row['tipo'] == 'prof' ? 'badge-prof' : 'badge-user' ?>">
                                <?= $row['tipo'] == 'prof' ? 'PROFESSOR' : 'ALUNO' ?>
                            </span>
                        </td>
                        <td>
                            <span style="color: <?= $row['ativo'] ? 'var(--success)' : 'var(--danger)' ?>; font-weight: 600; font-size: 0.8rem;">
                                <?= $row['ativo'] ? '● Ativo' : '● Bloqueado' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-action"
                                onclick="editarUsuario(
                                <?= $row['id_login'] ?>,
                                '<?= addslashes($row['nome_completo']) ?>',
                                '<?= addslashes($row['email']) ?>',
                                '<?= $row['tipo'] ?>'
                                    )">
                                Editar
                                </button>

<button class="btn-action btn-toggle"
    onclick="excluirUsuario(<?= $row['id_login'] ?>)">
    Excluir
</button>

                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
<div class="modal" id="modalAdicionar">
    <div class="modal-box">
        <h2>Novo Usuário</h2>

        <form method="POST" action="usuario_add.php">
            <input type="text" name="nome" placeholder="Nome completo" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>

            <select name="tipo" required>
                <option value="">Tipo</option>
                <option value="user">Aluno</option>
                <option value="prof">Professor</option>
            </select>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="fecharModal('modalAdicionar')">Cancelar</button>
                <button type="submit" class="btn-confirm">Salvar</button>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="modalEditar">
    <div class="modal-box">
        <h2>Editar Usuário</h2>

        <form method="POST" action="usuario_edit.php">
            <input type="hidden" name="id_login" id="edit_id">

            <input type="text" name="nome" id="edit_nome" required>
            <input type="email" name="email" id="edit_email" required>

            <select name="tipo" id="edit_tipo">
                <option value="user">Aluno</option>
                <option value="prof">Professor</option>
            </select>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="fecharModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-confirm">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<div class="modal" id="modalExcluir">
    <div class="modal-box danger">
        <h2>Excluir Usuário</h2>
        <p>Tem certeza que deseja excluir este usuário?</p>

        <form method="POST" action="usuario_delete.php">
            <input type="hidden" name="id_login" id="delete_id">

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="fecharModal('modalExcluir')">Cancelar</button>
                <button type="submit" class="btn-danger">Excluir</button>
            </div>
        </form>
    </div>
</div>

<script>

function abrirModal(id) {
    document.getElementById(id).classList.add('show');
}

function fecharModal(id) {
    document.getElementById(id).classList.remove('show');
}

/* ===== EDITAR ===== */
function editarUsuario(id, nome, email, tipo) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nome').value = nome;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_tipo').value = tipo;

    abrirModal('modalEditar');
}

/* ===== EXCLUIR ===== */
function excluirUsuario(id) {
    document.getElementById('delete_id').value = id;
    abrirModal('modalExcluir');
}


    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('closed');
    }

    function filtrarTabela(tipo, botao) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        botao.classList.add('active');

        document.querySelectorAll('.user-row').forEach(row => {
            if (tipo === 'todos' || row.getAttribute('data-tipo') === tipo) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function buscarNome() {
        let input = document.getElementById('buscaInput').value.toLowerCase();
        let rows = document.querySelectorAll('.user-row');

        rows.forEach(row => {
            let texto = row.innerText.toLowerCase();
            row.style.display = texto.includes(input) ? 'table-row' : 'none';
        });
    }
    setInterval(() => {
    fetch("ping_online.php", { method: "POST" });
}, 15000); // a cada 15 segundos
</script>

</body>
</html>