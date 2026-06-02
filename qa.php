<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$doc_id      = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_pdf = ''; $doc_name = '';

if ($doc_id > 0) {
    $stmt2 = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
    $stmt2->bind_param("i", $doc_id); $stmt2->execute();
    $result = $stmt2->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $current_pdf = basename($row['file_path'] ?? $row['file_name']);
        $doc_name    = $row['file_name'];
        $_SESSION['current_pdf'] = $current_pdf;
    }
}

$safe_name  = htmlspecialchars(clean_name($doc_name));
$safe_label = $doc_name ? $safe_name : 'No document selected';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Chatbot</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 860px; margin: auto; }

/* Doc banner */
.doc-banner {
  display: flex; align-items: center; gap: 14px;
  padding: 13px 18px;
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 12px; margin-bottom: 16px;
  font-size: 14px; font-weight: 600; color: var(--text);
}
.doc-banner .doc-icon { font-size: 22px; }

/* Server status bar */
.status-bar {
  padding: 10px 16px; border-radius: 10px; font-size: 13px;
  font-weight: 600; margin-bottom: 14px; display: none; border: 1px solid;
}
.status-ok  { background: var(--accent-soft);     color: var(--accent); border-color: var(--accent-line); display: block; }
.status-err { background: rgba(248,81,73,.1);     color: #ff7b72;       border-color: rgba(248,81,73,.3); display: block; }

/* Chatbox container */
.chatbox-card {
  background: var(--glass-bg);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  border-radius: 22px;
  box-shadow: var(--glass-shadow);
  display: flex; flex-direction: column;
  height: 72vh; min-height: 480px;
  overflow: hidden;
}

/* Chat header */
.chat-header {
  padding: 16px 22px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 12px;
  background: var(--surface-2);
}
.chat-avatar {
  width: 40px; height: 40px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent-bright));
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; flex-shrink: 0;
}
.chat-header-info h3 { font-size: 15px; font-weight: 700; color: var(--text); }
.chat-header-info p  { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
.chat-status-dot {
  width: 9px; height: 9px; border-radius: 50%;
  background: #4caf50; margin-left: auto; flex-shrink: 0;
  box-shadow: 0 0 0 2px rgba(76,175,80,.25);
  animation: dotPulse 2s ease infinite;
}
@keyframes dotPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

/* Messages area */
.messages {
  flex: 1; overflow-y: auto; padding: 20px 22px;
  display: flex; flex-direction: column; gap: 16px;
  scroll-behavior: smooth;
}
.messages::-webkit-scrollbar { width: 4px; }
.messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,.12); border-radius: 2px; }

/* Message bubbles */
.msg { display: flex; gap: 10px; max-width: 88%; animation: msgIn .25s ease; }
@keyframes msgIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }

.msg.user { flex-direction: row-reverse; align-self: flex-end; }
.msg.bot  { align-self: flex-start; }

.msg-avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.msg.bot  .msg-avatar { background: linear-gradient(135deg, var(--accent), var(--accent-bright)); }
.msg.user .msg-avatar { background: var(--surface-3); color: var(--text); }

.msg-bubble {
  padding: 13px 16px; border-radius: 16px;
  font-size: 14px; line-height: 1.7; max-width: 100%;
}
.msg.user .msg-bubble {
  background: var(--accent);
  color: #06140a; font-weight: 500; border-bottom-right-radius: 4px;
}
.msg.bot .msg-bubble {
  background: var(--surface-2);
  color: var(--text); border: 1px solid var(--border);
  border-bottom-left-radius: 4px;
}

/* Markdown rendering in bot bubbles */
.msg.bot .msg-bubble strong { color: var(--accent); }
.msg.bot .msg-bubble ul, .msg.bot .msg-bubble ol {
  margin: 8px 0 8px 18px;
}
.msg.bot .msg-bubble li { margin-bottom: 4px; }
.msg.bot .msg-bubble h3, .msg.bot .msg-bubble h4 {
  color: var(--accent); font-size: 14px; margin: 10px 0 4px;
}
.msg.bot .msg-bubble p { margin-bottom: 8px; }
.msg.bot .msg-bubble p:last-child { margin-bottom: 0; }

/* Confidence chip */
.conf-chip {
  font-size: 11px; color: var(--text-muted);
  margin-top: 5px; padding-left: 2px;
}

/* Typing indicator */
.typing-indicator {
  display: flex; gap: 5px; padding: 14px 16px;
  background: var(--surface-2); border: 1px solid var(--border); border-radius: 16px;
  border-bottom-left-radius: 4px; width: fit-content;
}
.typing-indicator span {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--accent); animation: bounce .9s ease infinite;
}
.typing-indicator span:nth-child(2) { animation-delay: .15s; }
.typing-indicator span:nth-child(3) { animation-delay: .30s; }
@keyframes bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-8px)} }

/* Welcome message */
.welcome-msg {
  text-align: center; padding: 30px 20px; color: var(--text-muted);
  font-size: 14px;
}
.welcome-msg .wicon { font-size: 42px; margin-bottom: 12px; }
.welcome-msg h3 { font-size: 17px; font-weight: 700; color: var(--text); margin-bottom: 6px; }

/* Suggested questions */
.suggestions { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 16px; }
.suggest-chip {
  padding: 7px 14px; border-radius: 20px;
  background: var(--surface-2); border: 1px solid var(--border);
  color: var(--text-muted); font-size: 13px; font-weight: 500;
  cursor: pointer; transition: .2s;
}
.suggest-chip:hover { background: var(--accent-soft); border-color: var(--accent-line); color: var(--accent); transform: translateY(-1px); }

/* Input area */
.chat-input-wrap {
  padding: 14px 18px;
  border-top: 1px solid var(--border);
  background: var(--surface-2);
  display: flex; gap: 10px; align-items: flex-end;
}
.chat-input {
  flex: 1; padding: 12px 16px;
  border: 1px solid var(--border);
  border-radius: 12px; background: var(--surface-3);
  font-family: inherit; font-size: 14px; color: var(--text);
  outline: none; resize: none; max-height: 120px; overflow-y: auto;
  transition: border-color .2s, box-shadow .2s;
  line-height: 1.5;
}
.chat-input:focus { border-color: var(--accent-line); box-shadow: 0 0 0 3px var(--accent-soft); }
.chat-input::placeholder { color: var(--text-dim); }

.send-btn {
  width: 44px; height: 44px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent-bright));
  border: none; cursor: pointer; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; transition: .2s;
  box-shadow: 0 3px 10px rgba(46,125,50,.3);
}
.send-btn:hover  { transform: scale(1.08); box-shadow: 0 5px 16px rgba(46,125,50,.4); }
.send-btn:active { transform: scale(.95); }
.send-btn:disabled { opacity: .5; cursor: default; transform: none; }

.clear-btn {
  font-size: 12px; color: var(--text-muted); background: none;
  border: none; cursor: pointer; padding: 4px 8px; border-radius: 6px;
  transition: .15s; align-self: center; margin-left: -4px;
}
.clear-btn:hover { color: #ff7b72; background: rgba(248,81,73,.1); }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <!-- Document banner -->
  <?php if ($doc_name): ?>
  <div class="doc-banner">
    <span class="doc-icon">📄</span>
    <span><?= $safe_label ?></span>
    <span style="margin-left:auto;display:flex;gap:8px;">
      <a href="ai.php?id=<?= $doc_id ?>"   class="sg-btn sg-btn-ghost" style="padding:5px 12px;font-size:12px;">Summary</a>
      <a href="quiz.php?id=<?= $doc_id ?>"  class="sg-btn sg-btn-primary" style="padding:5px 12px;font-size:12px;">Take Quiz →</a>
    </span>
  </div>
  <?php endif; ?>

  <!-- Server status -->
  <div class="status-bar" id="statusBar"></div>

  <!-- Chatbox -->
  <div class="chatbox-card">

    <!-- Header -->
    <div class="chat-header">
      <div class="chat-avatar">🤖</div>
      <div class="chat-header-info">
        <h3>StudyGenie Chatbot</h3>
        <p>Ask anything about <strong><?= $safe_label ?></strong></p>
      </div>
      <div class="chat-status-dot" id="statusDot" title="Online"></div>
    </div>

    <!-- Messages -->
    <div class="messages" id="messages">
      <div class="welcome-msg" id="welcomeMsg">
        <div class="wicon">💬</div>
        <h3>Hello! I'm your document assistant.</h3>
        <p>I've read <strong><?= $safe_label ?></strong> and I'm ready to answer your questions about it.<br>
        Ask me anything — concepts, definitions, summaries, comparisons, and more.</p>
        <div class="suggestions" id="suggestions">
          <span class="suggest-chip" onclick="usesuggestion(this)">📋 Summarize this document</span>
          <span class="suggest-chip" onclick="usesuggestion(this)">🔑 What are the key topics?</span>
          <span class="suggest-chip" onclick="usesuggestion(this)">❓ What is the main conclusion?</span>
          <span class="suggest-chip" onclick="usesuggestion(this)">📌 List the important points</span>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="chat-input-wrap">
      <button class="clear-btn" onclick="clearChat()" title="Clear conversation">🗑️</button>
      <textarea class="chat-input" id="chatInput" rows="1"
                placeholder="Ask anything about this document…"
                onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
      <button class="send-btn" id="sendBtn" onclick="sendMessage()" title="Send">➤</button>
    </div>

  </div>
</div>

<script>
const docId    = <?= intval($doc_id) ?>;
const docFile  = <?= json_encode($current_pdf) ?>;
let   history  = [];   // [{role:'user'|'assistant', content:'...'}]
let   thinking = false;

// ── Server status ──────────────────────────────────────────────────────
(async function checkStatus() {
  const bar = document.getElementById('statusBar');
  const dot = document.getElementById('statusDot');
  try {
    const r = await fetch('status_bridge.php');
    const d = await r.json();
    if (d.status === 'running') {
      bar.className   = 'status-bar status-ok';
      bar.textContent = `✅ AI ready · ${(d.indexed_files||[]).length} document(s) indexed`;
      dot.style.background = '#4caf50';
    } else throw new Error();
  } catch {
    bar.className   = 'status-bar status-err';
    bar.textContent = '❌ AI server offline — start: python/start_server.bat';
    dot.style.background = '#e53935';
  }
})();

// ── Helpers ────────────────────────────────────────────────────────────
function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function usesuggestion(el) {
  document.getElementById('chatInput').value = el.textContent.replace(/^[^\w]*/, '').trim();
  sendMessage();
}

function clearChat() {
  history = [];
  const msgs = document.getElementById('messages');
  msgs.innerHTML = '';
  // Re-add welcome
  const w = document.createElement('div');
  w.className = 'welcome-msg'; w.id = 'welcomeMsg';
  w.innerHTML = `<div class="wicon">💬</div>
    <h3>Conversation cleared.</h3>
    <p>Ask me anything about <strong>${escHtml(<?= json_encode(clean_name($doc_name ?: 'your document')) ?>)}</strong>.</p>`;
  msgs.appendChild(w);
  showToast('Conversation cleared.', 'info');
}

function scrollBottom() {
  const m = document.getElementById('messages');
  m.scrollTop = m.scrollHeight;
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Markdown-lite renderer ─────────────────────────────────────────────
function renderMarkdown(text) {
  return text
    // Bold **text**
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    // Headings ### and ####
    .replace(/^####\s(.+)$/gm, '<h4>$1</h4>')
    .replace(/^###\s(.+)$/gm,  '<h3>$1</h3>')
    // Bullet lists - item or * item
    .replace(/^[-*]\s(.+)$/gm, '<li>$1</li>')
    // Numbered lists 1. item
    .replace(/^\d+\.\s(.+)$/gm, '<li>$1</li>')
    // Wrap consecutive <li> in <ul>
    .replace(/(<li>.*<\/li>\n?)+/g, m => `<ul>${m}</ul>`)
    // Paragraphs (double newline → <p>)
    .split(/\n{2,}/).map(p => {
      p = p.trim();
      if (!p || p.startsWith('<')) return p;
      return `<p>${p.replace(/\n/g, '<br>')}</p>`;
    }).join('\n');
}

// ── Add message bubble ─────────────────────────────────────────────────
function addMessage(role, content, confidence = null) {
  const welcome = document.getElementById('welcomeMsg');
  if (welcome) welcome.remove();

  const wrap = document.createElement('div');
  wrap.className = `msg ${role}`;

  const avatar = document.createElement('div');
  avatar.className = 'msg-avatar';
  avatar.textContent = role === 'user' ? '👤' : '🤖';

  const inner = document.createElement('div');
  inner.style.maxWidth = '100%';

  const bubble = document.createElement('div');
  bubble.className = 'msg-bubble';

  if (role === 'bot') {
    bubble.innerHTML = renderMarkdown(content);
    if (confidence !== null && confidence > 0) {
      const chip = document.createElement('div');
      chip.className = 'conf-chip';
      chip.textContent = `Confidence: ${confidence}%`;
      inner.appendChild(bubble);
      inner.appendChild(chip);
    } else {
      inner.appendChild(bubble);
    }
  } else {
    bubble.textContent = content;
    inner.appendChild(bubble);
  }

  wrap.appendChild(avatar);
  wrap.appendChild(inner);
  document.getElementById('messages').appendChild(wrap);
  scrollBottom();
  return bubble;
}

// ── Typing indicator ───────────────────────────────────────────────────
function showTyping() {
  const wrap = document.createElement('div');
  wrap.className = 'msg bot'; wrap.id = 'typingWrap';
  wrap.innerHTML = `<div class="msg-avatar">🤖</div>
    <div class="typing-indicator"><span></span><span></span><span></span></div>`;
  document.getElementById('messages').appendChild(wrap);
  scrollBottom();
}
function hideTyping() {
  const t = document.getElementById('typingWrap');
  if (t) t.remove();
}

// ── Send message ───────────────────────────────────────────────────────
async function sendMessage() {
  const input = document.getElementById('chatInput');
  const q = input.value.trim();
  if (!q || thinking) return;

  thinking = true;
  const btn = document.getElementById('sendBtn');
  btn.disabled = true;
  input.value = ''; input.style.height = 'auto';

  // Add user bubble
  addMessage('user', q);
  history.push({ role: 'user', content: q });

  // Show typing indicator
  showTyping();

  try {
    const res  = await fetch('chat_bridge.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ doc_id: docId, question: q, history: history.slice(0,-1) })
    });
    const data = await res.json();
    hideTyping();

    const answer = data.answer || 'Sorry, I could not generate an answer.';
    addMessage('bot', answer, data.confidence || null);
    history.push({ role: 'assistant', content: answer });

    if (data.status === 'error') showToast('Could not answer. Try again.', 'error');

  } catch (err) {
    hideTyping();
    addMessage('bot', '❌ Connection error: ' + err.message);
    showToast('Connection error.', 'error');
  }

  thinking = false;
  btn.disabled = false;
  input.focus();
}
</script>
</body>
</html>
