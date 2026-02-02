<?php
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_sala = intval($_GET['sala'] ?? 0);
if(!$id_sala){
    header("Location: salas.php");
    exit;
}
$stmt = $cone->prepare("SELECT nome FROM salas WHERE id=?");
$stmt->bind_param("i", $id_sala);
$stmt->execute();
$sala = $stmt->get_result()->fetch_assoc();

$id_login = ensureLoggedIn('prof');

$stmt = $cone->prepare("SELECT id_prof, nome FROM tb_professor WHERE id_login=?");
$stmt->bind_param("i", $id_login);
$stmt->execute();
$prof = $stmt->get_result()->fetch_assoc();
$id_prof = $prof['id_prof'];

$stmt = $cone->prepare("
    SELECT 1
    FROM sala_profs
    WHERE id_sala = ? AND id_prof = ?
");
$stmt->bind_param("ii", $id_sala, $id_prof);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    die("Você não faz parte desta sala");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Professor | Premium UI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --deep-emerald: #075E54;
            --mint-soft: #D1E7DD;
            --off-white: #F8F9FA;
            --glass-white: rgba(255, 255, 255, 0.65);
            --gradient-emerald: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            --shadow-soft: 0 12px 40px rgba(0, 0, 0, 0.08);
            --radius-xl: 24px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

        body {
            background: radial-gradient(circle at top right, var(--mint-soft), #E5DDD5);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .badge {
    background: #e74c3c;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 12px;
    margin-left: auto;
    min-width: 18px;
    text-align: center;
}

        #main-app {
            width: 95vw;
            max-width: 1200px;
            height: 85vh;
            background: var(--glass-white);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.4);
            display: flex;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        /* Sidebar Estilizada */
        #alunos {
            width: 320px;
            background: rgba(255, 255, 255, 0.3);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        #alunos h2 {
            font-size: 1.2rem;
            color: var(--deep-emerald);
            margin-bottom: 20px;
            padding-left: 10px;
        }

        .aluno {
            background: rgba(255, 255, 255, 0.5);
            padding: 12px 16px;
            border-radius: 18px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
        }

        .aluno:hover {
            background: white;
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }

        .aluno.active-aluno {
    background: white;
    border: 2px solid var(--deep-emerald) !important; /* Borda visível */
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transform: scale(1.02);
}
        .avatar-circle {
            width: 40px;
            height: 40px;
            background: var(--gradient-emerald);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Área do Chat */
        #chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.2);
        }

        #header-chat {
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.4);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #chat {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            scroll-behavior: smooth;
        }

        /* Scrollbar Minimalista */
        #chat::-webkit-scrollbar { width: 4px; }
        #chat::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }

        /* Mensagens - UX Emocional */
        .msg-prof {
            align-self: flex-end;
            background: var(--gradient-emerald);
            color: white;
            padding: 12px 18px;
            border-radius: 20px 20px 4px 20px;
            max-width: 70%;
            box-shadow: 0 4px 12px rgba(7, 94, 84, 0.2);
            animation: slideIn 0.3s ease;
        }

        .msg-user {
            align-self: flex-start;
            background: white;
            padding: 12px 18px;
            border-radius: 20px 20px 20px 4px;
            max-width: 70%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            animation: slideIn 0.3s ease;
        }

        /* Footer e Inputs */
        #input-wrapper {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .floating-bar {
            background: white;
            border-radius: 30px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        input {
            flex: 1;
            border: none;
            outline: none;
            padding: 10px;
            font-size: 0.95rem;
        }

        button {
            border: none;
            background: none;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-send-main {
            background: var(--gradient-emerald);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Estilo para a mensagem GERAL dentro do chat do professor */
    .msg-geral-enviada {
        align-self: flex-end; /* Fica na direita pois foi o prof que enviou */
        background: var(--mint-soft) !important;
        color: var(--mint-text) !important;
        padding: 12px 18px;
        border-radius: 20px 20px 4px 20px;
        max-width: 70%;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        border: 1px solid rgba(7, 94, 84, 0.1);
        font-weight: 500;
        animation: slideIn 0.3s ease;
    }
/* MENU 3 PONTINHOS */
.menu-wrapper {
    position: relative;
}

.menu-btn {
    font-size: 1.4rem;
    background: none;
    border: none;
    cursor: pointer;
}

.menu-dropdown {
    position: absolute;
    right: 0;
    top: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    overflow: hidden;
    display: none;
    min-width: 160px;
    z-index: 100;
}

.menu-dropdown a {
    display: block;
    padding: 12px 16px;
    font-size: 0.9rem;
    color: #333;
    text-decoration: none;
    transition: background 0.2s;
}

.menu-dropdown a:hover {
    background: #f1f5f4;
}

    /* Adicione essa variável ao seu :root para o texto da mensagem geral */
    :root {
        /* ... suas outras variáveis */
        --mint-text: #0a4d44;
    }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .status {
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.online {
    background: #2ecc71;
}

.offline {
    background: #e74c3c;
}

/* ===== AUDIO RECORD (IGUAL CHAT MOBILE) ===== */

.mic-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(7, 94, 84, 0.1);
    color: var(--deep-emerald);
    font-size: 1.2rem;
}

.mic-button.recording {
    background: linear-gradient(135deg, #059669, #10b981);
    color: white;
}

.recording-indicator {
    display: none;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
}

.recording-indicator.active {
    display: flex;
}
.recording-dot {
    width: 8px;
    height: 8px;
    background: #dc2626;
    border-radius: 50%;
    animation: pulse 1.4s infinite;
}

@keyframes pulse {
    0%,100% { opacity: 1 }
    50% { opacity: .4 }
}
.recording-wave {
    display: flex;
    gap: 2px;
}

.wave-bar {
    width: 3px;
    height: 12px;
    background: var(--deep-emerald);
    animation: wave 1s infinite ease-in-out;
}

@keyframes wave {
    0%,100% { transform: scaleY(.4) }
    50% { transform: scaleY(1) }
}

.audio-wave span {
    width: 3px;
    height: 8px;
    background: rgba(255,255,255,0.7);
    border-radius: 4px;
    animation: none;
}

.msg-user .audio-wave span {
    background: rgba(7,94,84,0.6);
}

.audio-wave.playing span {
    animation: waveMove 1s infinite ease-in-out;
}

.audio-wave span:nth-child(1) { animation-delay: 0s; }
.audio-wave span:nth-child(2) { animation-delay: .1s; }
.audio-wave span:nth-child(3) { animation-delay: .2s; }
.audio-wave span:nth-child(4) { animation-delay: .3s; }
.audio-wave span:nth-child(5) { animation-delay: .4s; }
.icon-button svg {
      width: 24px;
      height: 24px;
    }

@keyframes waveMove {
    0%,100% { height: 6px; opacity: .5 }
    50% { height: 16px; opacity: 1 }
}

.msg-prof .audio-msg {
    background: linear-gradient(135deg, #075E54, #128C7E);
    color: white;
}

.msg-user .audio-msg {
    background: #ffffff;
    color: #333;
}

.audio-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    cursor: pointer;
}

.msg-user .audio-btn {
    background: rgba(7,94,84,0.1);
}



.msg-user .audio-wave {
    background: rgba(7,94,84,0.15);
}


.audio-time {
    font-size: 0.75rem;
    opacity: 0.8;
}

.audio-msg {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 18px;
    max-width: 240px;
}

/* PROF */
.msg-prof .audio-msg {
    background: linear-gradient(135deg, #075E54, #128C7E);
    color: white;
}

/* ALUNO */
.msg-user .audio-msg {
    background: #ffffff;
    color: #333;
    border: 1px solid rgba(7,94,84,0.15);
}

/* BOTÃO PLAY */
.audio-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    cursor: pointer;
    transition: transform .15s ease, background .15s ease;
}

.audio-btn:hover {
    transform: scale(1.05);
    background: rgba(255,255,255,0.35);
}

.msg-user .audio-btn {
    background: rgba(7,94,84,0.1);
}

/* WAVE */
.audio-wave {
    display: flex;
    align-items: center;
    gap: 3px;
    flex: 1;
}

/* BARRINHAS */
.audio-wave span {
    width: 2.5px;
    height: 6px;
    background: rgba(255,255,255,0.65);
    border-radius: 6px;
    animation: none;
}

.msg-user .audio-wave span {
    background: rgba(7,94,84,0.6);
}

/* ANIMAÇÃO QUANDO TOCANDO */
.audio-wave.playing span {
    animation: waveMove 1s infinite ease-in-out;
}

.audio-wave span:nth-child(1) { animation-delay: 0s; }
.audio-wave span:nth-child(2) { animation-delay: .1s; }
.audio-wave span:nth-child(3) { animation-delay: .2s; }
.audio-wave span:nth-child(4) { animation-delay: .3s; }
.audio-wave span:nth-child(5) { animation-delay: .4s; }

@keyframes waveMove {
    0%,100% { height: 5px; opacity: .4 }
    50% { height: 14px; opacity: 1 }
}

    </style>
</head>
<body>

<div id="main-app">
    <aside id="alunos">
        <h2>Mensagens - Sala: <?= htmlspecialchars($sala['nome']) ?></h2>
        <div id="lista-alunos-container">
            </div>
    </aside>

    <main id="chat-area">
        <header id="header-chat">
            <div style="display:flex; align-items:center; gap:12px;">
                <div id="chat-avatar" class="avatar-circle" style="display:none">?</div>
                <span id="nome-aluno-header" style="font-weight:600">Selecione uma conversa</span>
            </div>
            <div class="menu-wrapper">
                <button class="menu-btn" onclick="toggleMenu(event)">⋮</button>
                <div id="dropdown" class="menu-dropdown">
                    <a href="salas.php">🚪 Sair da sala</a>
                </div>
            </div>
        </header>
        
        <section id="chat">
            </section>

        <footer id="input-wrapper">
            <div id="geral" class="floating-bar">
                <span title="Mensagem Geral">📢</span>
                <input id="msg-geral" placeholder="Aviso geral para todos os alunos...">
                <button onclick="enviarGeral()" style="color:var(--deep-emerald); font-weight:bold">ENVIAR</button>
            </div>

            <div id="input" class="floating-bar" style="display:none; gap:10px;">
            <button id="cancel-record"
        type="button"
        style="display:none; color:#dc2626; font-weight:bold; font-size:1.2rem">
    ✖
</button>

    <div class="recording-indicator" id="recording-indicator">
            <div class="recording-dot"></div>
            <span id="recording-time">0:00</span>
            <div class="recording-wave">
                <div class="wave-bar"></div>
                <div class="wave-bar"></div>
                <div class="wave-bar"></div>
                <div class="wave-bar"></div>
                <div class="wave-bar"></div>
            </div>
        </div>

    <button style="font-size:1.4rem">😊</button>

    <input id="msg" placeholder="Escreva uma mensagem privada...">

     <button
  type="button"
  id="mic-button"
  class="icon-button mic-button"
  title="Gravar áudio"
><svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path> <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path> <line x1="12" y1="19" x2="12" y2="23"></line> <line x1="8" y1="23" x2="16" y2="23"></line>
      </svg></button>

    <button onclick="enviarPrivada()" class="btn-send-main">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="white">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
        </svg>
    </button>
</div>
        </footer>
    </main>
</div>

<script>
/* ===== AUDIO RECORD PROF (COM CANCELAR – FIXADO) ===== */

let mediaRecorder = null;
let audioChunks = [];
let audioStream = null;

let gravando = false;
let cancelado = false;

let tempo = 0;
let timer = null;

const micBtn = document.getElementById("mic-button");
const cancelarBtn = document.getElementById("cancel-record");
const indicador = document.getElementById("recording-indicator");
const tempoEl = document.getElementById("recording-time");
const inputMsg = document.getElementById("msg");

let alunoAtual = null;
let ultimaMsgId = 0;
let carregandoChat = false;

const SALA_ID = <?= (int)$id_sala ?>;
const salaAtual = <?= (int)$_GET['sala'] ?>;

/* ===== GRAVAÇÃO DE ÁUDIO ===== */

micBtn.addEventListener("click", async () => {
    if (!alunoAtual) return;

    // ▶️ INICIAR GRAVAÇÃO
    if (!gravando) {
        cancelado = false;

        try {
            audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        } catch (e) {
            alert("Permissão de microfone negada");
            return;
        }

        const mime = MediaRecorder.isTypeSupported("audio/webm;codecs=opus")
            ? "audio/webm;codecs=opus"
            : "";

        mediaRecorder = new MediaRecorder(
            audioStream,
            mime ? { mimeType: mime } : undefined
        );

        audioChunks = [];

        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            if (audioStream) {
                audioStream.getTracks().forEach(t => t.stop());
                audioStream = null;
            }

            if (!cancelado && audioChunks.length > 0) {
                enviarAudio();
            }
        };

        mediaRecorder.start();
        gravando = true;

        // UI
        micBtn.classList.add("recording");
        indicador.classList.add("active");
        cancelarBtn.style.display = "block";
        inputMsg.style.display = "none";

        tempo = 0;
        tempoEl.innerText = "0:00";

        timer = setInterval(() => {
            tempo++;
            const min = Math.floor(tempo / 60);
            const seg = tempo % 60;
            tempoEl.innerText = `${min}:${seg.toString().padStart(2, "0")}`;
        }, 1000);

    // ⏹️ PARAR E ENVIAR
    } else {
        mediaRecorder.stop();
        limparUIGravacao();
    }
});

// ❌ CANCELAR
cancelarBtn.addEventListener("click", () => {
    if (!gravando) return;

    cancelado = true;
    audioChunks = [];

    if (mediaRecorder && mediaRecorder.state !== "inactive") {
        mediaRecorder.stop();
    }

    limparUIGravacao();
});

function limparUIGravacao() {
    clearInterval(timer);
    gravando = false;

    micBtn.classList.remove("recording");
    indicador.classList.remove("active");
    cancelarBtn.style.display = "none";
    inputMsg.style.display = "block";
}

function enviarAudio() {
    const blob = new Blob(audioChunks, { type: "audio/webm;codecs=opus" });
    if (blob.size < 500) return;

    const fd = new FormData();
    fd.append("audio", blob);
    fd.append("id_user", alunoAtual);
    fd.append("sala", SALA_ID);

    fetch("audios/enviar_audio_prof.php", {
        method: "POST",
        body: fd
    }).then(() => carregarMensagens());
}

/* ===== MENSAGENS ===== */

function enviarGeral() {
    const input = document.getElementById("msg-geral");
    const msg = input.value.trim();
    if (!msg) return;

    fetch("enviar_geral.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `mensagem=${encodeURIComponent(msg)}&sala=${SALA_ID}`
    }).then(() => {
        input.value = "";
        if (alunoAtual) carregarMensagens();
    });
}

function enviarPrivada() {
    if (!alunoAtual) return;

    const msg = inputMsg.value.trim();
    if (!msg) return;

    fetch("enviar_privada_prof.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_user=${alunoAtual}&mensagem=${encodeURIComponent(msg)}&sala=${SALA_ID}`
    }).then(() => {
        inputMsg.value = "";
        carregarMensagens();
    });
}

/* ===== ALUNOS ===== */

function carregarAlunos() {
    fetch("listar_alunos.php?sala=" + salaAtual)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById("lista-alunos-container");
            if (!container) return;

            container.innerHTML = data.map(a => `
                <div class="aluno ${alunoAtual == a.id_user ? 'active-aluno' : ''}"
                     data-id="${a.id_user}"
                     onclick="abrirChat(${a.id_user}, '${a.nome.replace(/'/g,"\\'")}')">

                    <div style="display:flex; gap:12px; flex:1">
                        <div class="avatar-circle">${a.nome.charAt(0)}</div>
                        <div>
                            <strong>${a.nome}</strong>
                            <div class="status">
                                <span class="status-dot ${a.online ? 'online' : 'offline'}"></span>
                                <span>${a.online ? 'Online' : 'Offline'}</span>
                            </div>
                        </div>
                    </div>

                    ${a.nao_lidas > 0 ? `<span class="badge">${a.nao_lidas}</span>` : ``}
                </div>
            `).join("");
        });
}

/* ===== CHAT ===== */

function abrirChat(id, nome) {
    alunoAtual = id;
    ultimaMsgId = 0;

    document.querySelectorAll(".aluno").forEach(a => a.classList.remove("active-aluno"));
    document.querySelector(`.aluno[data-id="${id}"]`)?.classList.add("active-aluno");

    marcarMensagensComoLidas(id);

    document.getElementById("nome-aluno-header").innerText = nome;
    document.getElementById("chat-avatar").style.display = "flex";
    document.getElementById("chat-avatar").innerText = nome.charAt(0);
    document.getElementById("chat").innerHTML = "";
    document.getElementById("input").style.display = "flex";

    carregarMensagens();
}

function marcarMensagensComoLidas(id) {
    fetch("marcar_lidas_prof.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_user=${id}&sala=${SALA_ID}`
    });
}

function carregarMensagens() {
    if (!alunoAtual || carregandoChat) return;
    carregandoChat = true;

    fetch(`buscar_privada_prof.php?id_user=${alunoAtual}&sala=${SALA_ID}&last_id=${ultimaMsgId}`)
        .then(r => r.json())
        .then(msgs => {
            const chat = document.getElementById("chat");
            if (!Array.isArray(msgs)) return;

            msgs.forEach(m => {
                const div = document.createElement("div");
                const idMsg = m.id_msg || m.id;

                div.className = m.tipo_remetente === "prof" ? "msg-prof" : "msg-user";

                if (m.tipo === "audio") {
                const audioId = "audio_" + idMsg;
                const timeId = "time_" + audioId;

    div.innerHTML = `
        <div class="audio-msg">
            <button class="audio-btn" onclick="toggleAudio('${audioId}', this)">▶</button>

            <div class="audio-wave" id="wave_${audioId}">
                <span></span><span></span><span></span><span></span><span></span>
            </div>

            <span class="audio-time" id="${timeId}">0:00</span>

            <audio id="${audioId}" preload="metadata">
                <source src="audios/${m.mensagem}" type="audio/webm;codecs=opus">
            </audio>
        </div>
    `;

    // calcula duração real
    setTimeout(() => {
        const audio = document.getElementById(audioId);
        const timeEl = document.getElementById(timeId);

        audio.addEventListener("loadedmetadata", () => {
            const d = Math.floor(audio.duration);
            if (!isNaN(d)) {
                const min = Math.floor(d / 60);
                const sec = d % 60;
                timeEl.innerText = `${min}:${sec.toString().padStart(2, "0")}`;
            }
        });
    }, 0);

                } else {
                    div.textContent = m.mensagem;
                }

                chat.appendChild(div);
                if (idMsg > ultimaMsgId) ultimaMsgId = idMsg;
            });

            chat.scrollTop = chat.scrollHeight;
        })
        .finally(() => carregandoChat = false);
}

/* ===== PLAYER ===== */

function toggleAudio(id, btn) {
    const audio = document.getElementById(id);
    const wave = document.getElementById("wave_" + id);

    if (!audio.paused) {
        audio.pause();
        btn.innerText = "▶";
        wave.classList.remove("playing");
        return;
    }

    document.querySelectorAll("audio").forEach(a => {
        a.pause();
        a.currentTime = 0;
    });

    document.querySelectorAll(".audio-btn").forEach(b => b.innerText = "▶");
    document.querySelectorAll(".audio-wave").forEach(w => w.classList.remove("playing"));

    audio.play().catch(err => {
    console.warn("Erro ao tocar áudio:", err);
});

    btn.innerText = "⏸";
    wave.classList.add("playing");

    audio.onended = () => {
        btn.innerText = "▶";
        wave.classList.remove("playing");
    };
}
function toggleMenu(e) {
    e.stopPropagation();

    const dropdown = document.getElementById("dropdown");

    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
    } else {
        dropdown.style.display = "block";
    }
}

// Fecha o menu ao clicar fora
document.addEventListener("click", () => {
    const dropdown = document.getElementById("dropdown");
    if (dropdown) dropdown.style.display = "none";
});

/* ===== LOOP ===== */

setInterval(() => {
    carregarAlunos();
    if (alunoAtual) carregarMensagens();
}, 2000);

setInterval(() => {
    fetch("ping_online.php", { method: "POST" });
}, 15000);

inputMsg.addEventListener("keydown", e => {
    if (e.key === "Enter") {
        e.preventDefault();
        enviarPrivada();
    }
});

document.getElementById("msg-geral").addEventListener("keydown", e => {
    if (e.key === "Enter") {
        e.preventDefault();
        enviarGeral();
    }
});

carregarAlunos();
</script>

</body>
</html>