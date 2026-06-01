<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
$filename = $_SESSION['file_name'] ?? '';
if (!$filename) { header("Location: dashboard.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Indexing Document</title>
<?php include 'theme.php'; ?>
<style>
body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
.page-wrapper { max-width:560px; width:100%; margin:0 auto; padding:0; }

.card {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  border-radius: 24px;
  box-shadow: var(--glass-shadow);
  padding: 38px 36px;
  text-align: center;
}

.ai-orb {
  width: 86px; height: 86px; border-radius: 50%;
  background: linear-gradient(135deg,#1b5e20,#43a047,#00e676);
  margin: 0 auto 20px;
  display: flex; align-items: center; justify-content: center;
  font-size: 36px;
  animation: pulse 2s ease-in-out infinite;
  box-shadow: 0 0 40px rgba(76,175,80,.35);
}
@keyframes pulse {
  0%,100%{ box-shadow:0 0 20px rgba(76,175,80,.35); transform:scale(1); }
  50%    { box-shadow:0 0 55px rgba(76,175,80,.5);  transform:scale(1.05); }
}

.card h2 {
  font-size:20px; font-weight:800;
  background:linear-gradient(135deg,#1b5e20,#43a047);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  background-clip:text; margin-bottom:6px;
}
.filename { color:var(--text-muted); font-size:13px; word-break:break-all; margin-bottom:22px; }

.prog-track { width:100%; height:10px; background:rgba(0,0,0,.08); border-radius:10px; overflow:hidden; margin-bottom:6px; }
.prog-fill  { height:100%; width:5%; background:linear-gradient(90deg,#2e7d32,#00e676); border-radius:10px; transition:width .6s ease; }
.prog-label { font-size:12px; color:var(--text-muted); margin-bottom:22px; text-align:right; }

.steps { text-align:left; display:flex; flex-direction:column; gap:9px; margin-bottom:20px; }
.step {
  display:flex; align-items:center; gap:13px;
  padding:11px 15px; border-radius:12px;
  background:rgba(0,0,0,.03); font-size:13px; font-weight:600;
  color:var(--text-muted); border:1px solid transparent; transition:all .35s;
}
.step.active { background:rgba(76,175,80,.1); color:var(--g2); border-color:rgba(76,175,80,.2); }
.step.done   { background:rgba(76,175,80,.06); color:var(--g3); }
.step-icon { font-size:18px; flex-shrink:0; }
.step.active .step-icon { animation:spin .9s linear infinite; }
.step.done   .step-icon { animation:none; }
@keyframes spin { to{transform:rotate(360deg);} }

.status-msg { font-size:13px; min-height:20px; transition:.3s; color:var(--text-muted); margin-bottom:14px; }
.status-msg.ok  { color:#2e7d32; font-weight:700; }
.status-msg.err { color:#c62828; font-weight:700; }

.note {
  padding:11px 15px; background:rgba(76,175,80,.07);
  border:1px solid rgba(76,175,80,.15); border-radius:11px;
  font-size:12px; color:var(--g2); font-weight:600;
}
</style>
</head>
<body>
<div class="page-wrapper">
<div class="card">
  <div class="ai-orb">🤖</div>
  <h2>Indexing your document…</h2>
  <p class="filename">📄 <?= htmlspecialchars($filename) ?></p>

  <div class="prog-track"><div class="prog-fill" id="prog"></div></div>
  <div class="prog-label" id="progLabel">0%</div>

  <div class="steps">
    <div class="step" id="s1"><span class="step-icon">📤</span> Sending to AI server</div>
    <div class="step" id="s2"><span class="step-icon">📖</span> Extracting text from PDF</div>
    <div class="step" id="s3"><span class="step-icon">🔪</span> Chunking &amp; building index</div>
    <div class="step" id="s4"><span class="step-icon">✨</span> Generating AI summary</div>
    <div class="step" id="s5"><span class="step-icon">✅</span> Saving to database</div>
  </div>

  <div class="status-msg" id="msg">Starting…</div>
  <div class="note">You can leave this page — processing continues in background.</div>
</div>
</div>

<script>
const filename = <?= json_encode($filename) ?>;
const steps    = ['s1','s2','s3','s4','s5'];
let   cur      = 0;

function setStep(n, msg) {
  // Mark previous done
  if (cur > 0 && cur <= steps.length) {
    const prev = document.getElementById(steps[cur-1]);
    prev.classList.remove('active'); prev.classList.add('done');
    prev.querySelector('.step-icon').textContent = '✔️';
  }
  cur = n;
  if (n > 0 && n <= steps.length) {
    document.getElementById(steps[n-1]).classList.add('active');
  }
  const pct = Math.round((n / steps.length) * 100);
  document.getElementById('prog').style.width = pct + '%';
  document.getElementById('progLabel').textContent = pct + '%';
  if (msg) document.getElementById('msg').textContent = msg;
}

function setMsg(msg, cls = '') {
  const el = document.getElementById('msg');
  el.className = 'status-msg ' + cls;
  el.textContent = msg;
}

async function run() {
  setStep(1, 'Connecting to AI server…');

  // Step 1: trigger indexing via process_bridge.php
  let bridgeData;
  try {
    setStep(2, 'Extracting text from PDF…');
    const res  = await fetch('process_bridge.php', { method: 'POST' });
    const text = await res.text();
    try { bridgeData = JSON.parse(text); }
    catch { bridgeData = { status:'error', message:'Bad response: ' + text.substring(0,120) }; }
  } catch(e) {
    setMsg('❌ Could not reach process_bridge: ' + e.message, 'err');
    setTimeout(() => location.href = 'dashboard.php', 3500);
    return;
  }

  if (bridgeData.status === 'error') {
    // Indexing might be in background — poll status endpoint
    setStep(3, 'Waiting for background indexing…');
    await pollUntilReady();
    return;
  }

  setStep(3, `Indexed ${bridgeData.chunks || '?'} chunks ✓`);
  await new Promise(r => setTimeout(r, 400));
  setStep(4, 'Summary generated ✓');
  await new Promise(r => setTimeout(r, 400));
  setStep(5, 'Saved to database ✓');

  setMsg('✅ Document ready for Q&A and Quiz!', 'ok');
  showToast('Document indexed successfully!', 'success');
  setTimeout(() => location.href = 'dashboard.php', 1800);
}

async function pollUntilReady() {
  const maxWait = 180;  // seconds
  const interval = 2;
  let   waited   = 0;

  while (waited < maxWait) {
    await new Promise(r => setTimeout(r, interval * 1000));
    waited += interval;

    try {
      const res  = await fetch('index_status_bridge.php');
      const data = await res.json();

      const isPending = (data.pending || []).includes(filename);
      const isReady   = (data.ready   || []).includes(filename);
      const errKey    = Object.keys(data.errors || {}).find(k => k === filename);

      if (isReady) {
        setStep(3, 'Indexing complete ✓');
        await new Promise(r => setTimeout(r, 300));
        setStep(4, 'Generating summary…');
        // Trigger summary + DB update
        await fetch('process_bridge.php', { method: 'POST' });
        setStep(5, 'Saved ✓');
        setMsg('✅ Document ready!', 'ok');
        showToast('Document indexed!', 'success');
        setTimeout(() => location.href = 'dashboard.php', 1800);
        return;
      }
      if (errKey) {
        setMsg('❌ Indexing failed: ' + data.errors[errKey], 'err');
        showToast('Indexing failed', 'error');
        setTimeout(() => location.href = 'dashboard.php', 3000);
        return;
      }
      if (isPending) {
        setMsg(`⏳ Indexing in progress… (${waited}s)`);
      }
    } catch(e) {
      setMsg('⚠️ Lost contact with AI server…');
    }
  }

  setMsg('⚠️ Timed out. Document may still be indexing in background.', 'err');
  setTimeout(() => location.href = 'dashboard.php', 3000);
}

run();
</script>
</body>
</html>
