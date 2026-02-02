<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "helpers.php";
require_once "cone.php";
date_default_timezone_set('America/Sao_Paulo');
$id_login = ensureLoggedIn('user');
$id_sala = intval($_GET['sala'] ?? 0);

if(!$id_sala){
    header("Location: salas.php");
    exit;
}

// Busca dados do aluno
$sql = "SELECT id_user, nome FROM tb_user WHERE id_login = ?";
$stmt = $cone->prepare($sql);
$stmt->bind_param("i", $id_login);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$id_user = $user['id_user'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Aluno | Premium UI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --deep-emerald: #075E54;
            --mint-soft: #D1E7DD;
            --mint-text: #0a4d44;
            --glass-white: rgba(255, 255, 255, 0.65);
            --gradient-emerald: linear-gradient(135deg, #075E54 0%, #128C7E 100%);
            --shadow-soft: 0 12px 40px rgba(0, 0, 0, 0.08);
            --radius-xl: 24px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

        body {
            background: radial-gradient(circle at top right, var(--mint-soft), #E5DDD5);
            height: 100vh; display: flex; align-items: center; justify-content: center; color: #333;
        }

        #main-app {
            width: 95vw; max-width: 1200px; height: 85vh;
            background: var(--glass-white); backdrop-filter: blur(15px);
            border-radius: var(--radius-xl); display: flex; box-shadow: var(--shadow-soft); overflow: hidden;
        }

        /* Sidebar */
        #sidebar {
            width: 320px; background: rgba(255, 255, 255, 0.3);
            border-right: 1px solid rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; padding: 20px;
        }

        #sidebar h2 { font-size: 1.2rem; color: var(--deep-emerald); margin-bottom: 20px; }

        .prof-card {
            background: rgba(255, 255, 255, 0.5); padding: 12px 16px; border-radius: 18px;
            margin-bottom: 10px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; align-items: center; gap: 12px; border: 1px solid transparent;
        }

        .prof-card:hover { background: white; transform: scale(1.02); }
        .prof-card.active-prof { background: white; border-color: var(--deep-emerald); box-shadow: 0 4px 15px rgba(0,0,0,0.04); }

        .avatar-circle {
            width: 40px; height: 40px; background: var(--gradient-emerald);
            color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;
        }

        /* Chat Area */
        #chat-area { flex: 1; display: flex; flex-direction: column; }

        #header-chat {
            padding: 15px 25px; background: rgba(255, 255, 255, 0.4);
            border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;
        }

        #chat { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth; }

        /* Mensagens com Animação */
        .msg-aluno {
            align-self: flex-end; background: var(--gradient-emerald); color: white;
            padding: 12px 18px; border-radius: 20px 20px 4px 20px; max-width: 70%;
            box-shadow: 0 4px 12px rgba(7, 94, 84, 0.2); animation: slideIn 0.3s ease forwards;
        }

        .msg-prof {
            align-self: flex-start; background: white; padding: 12px 18px;
            border-radius: 20px 20px 20px 4px; max-width: 70%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); animation: slideIn 0.3s ease forwards;
        }

        .msg-geral {
            align-self: flex-start; background: var(--mint-soft); color: var(--mint-text);
            padding: 12px 18px; border-radius: 15px; max-width: 85%; font-weight: 500;
            border: 1px solid rgba(7, 94, 84, 0.1); animation: slideIn 0.3s ease forwards;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Footer e Inputs */
        #input-wrapper { padding: 20px; }
        .floating-bar {
            background: white; border-radius: 30px; padding: 8px 15px;
            display: flex; align-items: center; gap: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        #msg-input { flex: 1; border: none; outline: none; padding: 10px; font-size: 0.95rem; }

        .btn-send-main {
            background: var(--gradient-emerald); color: white; width: 40px; height: 40px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; border:none; cursor:pointer;
        }

        /* Menu 3 Pontinhos */
        .menu-wrapper { position: relative; }
        .menu-btn { font-size: 1.4rem; background: none; border: none; cursor: pointer; padding: 5px; }
        .menu-dropdown {
            position: absolute; right: 0; top: 40px; background: white; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); display: none; min-width: 160px; z-index: 100; overflow: hidden;
        }
        .menu-dropdown a { display: block; padding: 12px 16px; text-decoration: none; color: #333; font-size: 0.9rem; }
        .menu-dropdown a:hover { background: #f1f5f4; }

        /* Badges e Status */
        .badge { background: #e74c3c; color: white; font-size: 0.7rem; font-weight: 700; padding: 2px 7px; border-radius: 12px; margin-left: auto; min-width: 18px; text-align: center; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .online { background: #2ecc71; }
        .offline { background: #e74c3c; }
        /* ===== AUDIO RECORD USER ===== */

.mic-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(7, 94, 84, 0.1);
    color: var(--deep-emerald);
    font-size: 1.2rem;
    border: none;
    cursor: pointer;
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
/* ===== AUDIO PLAYER CHAT ===== */

.audio-msg {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 160px;
}

.audio-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(0,0,0,0.15);
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    flex-shrink: 0;
}

.msg-prof .audio-btn {
    background: rgba(0,0,0,0.25);
    color: #000;
}

.audio-wave {
    display: flex;
    align-items: center;
    gap: 3px;
    height: 18px;
}

.audio-wave span {
    width: 3px;
    height: 100%;
    background: currentColor;
    opacity: .4;
    border-radius: 2px;
}

.audio-wave.playing span {
    animation: audioWave 1s infinite ease-in-out;
    opacity: .9;
}

.audio-wave span:nth-child(1) { animation-delay: 0s }
.audio-wave span:nth-child(2) { animation-delay: .1s }
.audio-wave span:nth-child(3) { animation-delay: .2s }
.audio-wave span:nth-child(4) { animation-delay: .3s }
.audio-wave span:nth-child(5) { animation-delay: .4s }

.icon-button svg {
      width: 24px;
      height: 24px;
    }

@keyframes audioWave {
    0%,100% { transform: scaleY(.4) }
    50% { transform: scaleY(1) }
}

.audio-time {
    font-size: 0.7rem;
    opacity: .75;
    white-space: nowrap;
}

/* ===== BOTÃO CANCELAR GRAVAÇÃO ===== */
.cancel-record-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(220, 38, 38, 0.12);
    color: #dc2626;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;

    display: none; /* começa escondido */

    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.cancel-record-btn:hover {
    background: rgba(220, 38, 38, 0.2);
    transform: scale(1.05);
}

.cancel-record-btn:active {
    transform: scale(0.95);
}

    </style>
</head>
<body>

<div id="main-app">
    <aside id="sidebar">
        <h2>Professores</h2>
        <div id="lista-professores-container"></div>
    </aside>

    <main id="chat-area">
        <header id="header-chat">
            <div style="display:flex; align-items:center; gap:12px;">
                <div id="avatar-header" class="avatar-circle" style="display:none">?</div>
                <span id="nome-header" style="font-weight:600">Selecione um professor</span>
            </div>
            <div class="menu-wrapper">
                <button class="menu-btn" onclick="toggleMenu(event)">⋮</button>
                <div id="dropdown" class="menu-dropdown">
                    <a href="salas.php">🚪 Sair da sala</a>
                </div>
            </div>
        </header>
        
        <section id="chat"></section>

        <footer id="input-wrapper" style="display:none;">
    <div class="floating-bar" style="gap:10px">
            <button id="cancel-record" type="button" class="cancel-record-btn">
             ✖
            </button>
        <!-- 📊 INDICADOR DE GRAVAÇÃO (deve estar ANTES do input) -->
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

    <button style="font-size:1.4rem; background:none; border:none; cursor:pointer">😊</button>

        <!-- 💬 INPUT DE TEXTO -->
        <input id="msg-input" placeholder="Escreva uma mensagem privada...">

        <!-- 🎤 BOTÃO MICROFONE -->
        <button
  type="button"
  id="mic-button"
  class="icon-button mic-button"
  title="Gravar áudio"
><svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path> <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path> <line x1="12" y1="19" x2="12" y2="23"></line> <line x1="8" y1="23" x2="16" y2="23"></line>
      </svg></button>

        <!-- ➤ BOTÃO ENVIAR -->
        <button onclick="enviar()" class="btn-send-main">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="white">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
            </svg>
        </button>
    </div>
</footer>
    </main>
</div>



<script>
/* =========================
   VARIÁVEIS GLOBAIS
========================= */
let mediaRecorder = null;
let audioChunks = [];
let gravando = false;
let tempo = 0;
let timer = null;
let audioStream = null;

let professorAtual = null;
let ultimaMsgId = 0;
let carregandoChat = false;

const SALA_ID = <?= $id_sala ?>;

const micBtn = document.getElementById("mic-button");
const indicador = document.getElementById("recording-indicator");
const tempoEl = document.getElementById("recording-time");
const inputMsg = document.getElementById("msg-input");
const cancelBtn = document.getElementById("cancel-record");
function atualizarListaProfessores() {
    fetch(`listar_professores.php?sala=${SALA_ID}`)
        .then(r => r.json())
        .then(profs => {
            const container = document.getElementById("lista-professores-container");
            if (!container) return;

            container.innerHTML = profs.map(p => {
                const ativo = p.id_prof == professorAtual;

                return `
                    <div class="prof-card ${ativo ? 'active-prof' : ''}"
                         onclick="abrirChat(${p.id_prof}, '${p.nome.replace(/'/g, "\\'")}')">
                        <div class="avatar-circle">${p.nome.charAt(0)}</div>
                        <div style="display:flex; flex-direction:column">
                            <strong style="font-size:.9rem">${p.nome}</strong>
                            <div style="font-size:.7rem">
                                <span class="status-dot ${parseInt(p.online) ? 'online' : 'offline'}"></span>
                                ${parseInt(p.online) ? 'Online' : 'Offline'}
                            </div>
                        </div>
                        ${(!ativo && p.nao_lidas > 0) ? `<span class="badge">${p.nao_lidas}</span>` : ''}
                    </div>
                `;
            }).join("");
        })
        .catch(err => console.error("Erro listar professores:", err));
}
/* =========================
   GRAVAÇÃO DE ÁUDIO
========================= */
micBtn.addEventListener("click", async () => {
    if (!professorAtual) return;

    if (!gravando) {
        try {
            audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });

            // 🔥 MIME CORRETO (IGUAL AO PROF)
            const mime = MediaRecorder.isTypeSupported("audio/webm;codecs=opus")
                ? "audio/webm;codecs=opus"
                : "audio/webm";

            mediaRecorder = new MediaRecorder(audioStream, { mimeType: mime });
            audioChunks = [];

            mediaRecorder.ondataavailable = e => {
                if (e.data.size > 0) audioChunks.push(e.data);
            };

            mediaRecorder.onstop = () => {
                if (audioStream) {
                    audioStream.getTracks().forEach(t => t.stop());
                    audioStream = null;
                }
                if (audioChunks.length > 0) enviarAudioUser();
            };

            mediaRecorder.start();
            gravando = true;

            // UI
            micBtn.classList.add("recording");
            indicador.classList.add("active");
            inputMsg.style.display = "none";
            cancelBtn.style.display = "inline";

            tempo = 0;
            tempoEl.innerText = "0:00";
            timer = setInterval(() => {
                tempo++;
                const min = Math.floor(tempo / 60);
                const seg = tempo % 60;
                tempoEl.innerText = `${min}:${seg.toString().padStart(2, "0")}`;
            }, 1000);

        } catch (err) {
            console.error("Erro microfone:", err);
            alert("Não foi possível acessar o microfone");
        }
    } else {
        gravando = false;
        mediaRecorder.stop();

        clearInterval(timer);
        micBtn.classList.remove("recording");
        indicador.classList.remove("active");
        cancelBtn.style.display = "none";
        inputMsg.style.display = "block";
    }
});

/* =========================
   CANCELAR GRAVAÇÃO
========================= */
cancelBtn.addEventListener("click", () => {
    if (!gravando) return;

    gravando = false;
    audioChunks = [];

    if (mediaRecorder && mediaRecorder.state !== "inactive") {
        mediaRecorder.onstop = null;
        mediaRecorder.stop();
    }

    if (audioStream) {
        audioStream.getTracks().forEach(t => t.stop());
        audioStream = null;
    }

    clearInterval(timer);
    micBtn.classList.remove("recording");
    indicador.classList.remove("active");
    inputMsg.style.display = "block";
    cancelBtn.style.display = "none";
});

/* =========================
   ENVIO DO ÁUDIO
========================= */
function enviarAudioUser() {
    const blob = new Blob(audioChunks, { type: "audio/webm;codecs=opus" });
    if (blob.size < 500) return;

    const fd = new FormData();
    fd.append("audio", blob, "audio.webm");
    fd.append("sala", SALA_ID);
    fd.append("id_prof", professorAtual);

    fetch("audios/enviar_audio_user.php", {
        method: "POST",
        body: fd
    })
    .then(() => carregarMensagens())
    .catch(err => console.error("Erro envio áudio:", err));
}

/* =========================
   PLAYER DE ÁUDIO
========================= */
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

    audio.play().catch(err => console.warn("Erro ao tocar áudio:", err));

    btn.innerText = "⏸";
    wave.classList.add("playing");

    audio.onended = () => {
        btn.innerText = "▶";
        wave.classList.remove("playing");
    };
}

/* =========================
   CHAT
========================= */
function abrirChat(id, nome) {
    if (professorAtual === id) return;

    professorAtual = id;
    ultimaMsgId = 0;

    document.getElementById("nome-header").innerText = "Prof. " + nome;
    document.getElementById("avatar-header").innerText = nome.charAt(0);
    document.getElementById("avatar-header").style.display = "flex";
    document.getElementById("chat").innerHTML = "";
    document.getElementById("input-wrapper").style.display = "block";

    marcarComoLidas();
    carregarMensagens();
    atualizarListaProfessores();
}

function marcarComoLidas() {
    if (!professorAtual) return;
    const fd = new URLSearchParams();
    fd.append("sala", SALA_ID);
    fd.append("id_prof", professorAtual);
    fetch("marcar_lidas_user.php", { method: "POST", body: fd });
}

function carregarMensagens() {
    if (!professorAtual || carregandoChat) return;
    carregandoChat = true;

    fetch(`buscar_mensagem_user.php?sala=${SALA_ID}&prof=${professorAtual}&last_id=${ultimaMsgId}`)
        .then(r => r.json())
        .then(msgs => {
            const chat = document.getElementById("chat");

            msgs.forEach(m => {
                const div = document.createElement("div");

                if (m.tipo === "geral") {
                    div.className = "msg-geral";
                    div.innerHTML = `<strong>📢 AVISO</strong><br>${m.mensagem}`;
                } else {
                    div.className = m.tipo_remetente === "user" ? "msg-aluno" : "msg-prof";

                    if (m.tipo === "audio") {
                        const audioId = "audio_" + m.id_msg;
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

                        setTimeout(() => {
                            const audio = document.getElementById(audioId);
                            const timeEl = document.getElementById(timeId);

                            const atualizarTempo = () => {
                                if (!isNaN(audio.duration)) {
                                    const d = Math.floor(audio.duration);
                                    const min = Math.floor(d / 60);
                                    const sec = d % 60;
                                    timeEl.innerText = `${min}:${sec.toString().padStart(2, "0")}`;
                                }
                            };

                            audio.addEventListener("loadedmetadata", atualizarTempo);
                            audio.addEventListener("durationchange", atualizarTempo);
                        }, 0);
                    } else {
                        div.innerText = m.mensagem;
                    }
                }

                chat.appendChild(div);
                if (m.id_msg > ultimaMsgId) ultimaMsgId = m.id_msg;
            });

            chat.scrollTop = chat.scrollHeight;
        })
        .finally(() => carregandoChat = false);
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

/* =========================
   AUTO UPDATE
========================= */
setInterval(() => {
    atualizarListaProfessores();
    if (professorAtual) carregarMensagens();
}, 2000);

atualizarListaProfessores();
</script>

</body>
</html>