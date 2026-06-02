<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: dashboard.php"); exit(); }
$stmt = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { header("Location: dashboard.php"); exit(); }
$safe_name = htmlspecialchars(clean_name($row['file_name']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Study Plan</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 900px; margin: auto; }
.breadcrumb { font-size:13px; color:var(--text-dim); margin-bottom:18px; }
.breadcrumb a { color:var(--text-muted); } .breadcrumb a:hover { color:var(--text); }

.plan-controls {
  display:flex; align-items:center; gap:14px; flex-wrap:wrap;
  padding:20px; margin-bottom:18px;
}
.plan-controls label { font-size:14px; color:var(--text-muted); font-weight:500; }
.day-select {
  padding:10px 14px; border:1px solid var(--border); border-radius:9px;
  background:var(--surface-2); color:var(--text); font-family:inherit; font-size:14px; outline:none;
}
.day-select:focus { border-color:var(--accent-line); }

/* Timeline */
.timeline { position:relative; padding-left:38px; }
.timeline::before {
  content:''; position:absolute; left:13px; top:6px; bottom:6px; width:2px;
  background:linear-gradient(var(--accent), var(--border));
}
.day-card {
  position:relative; background:var(--surface); border:1px solid var(--border);
  border-radius:14px; padding:20px 22px; margin-bottom:16px;
  animation: fadeIn .4s ease both;
}
.day-card::before {
  content:''; position:absolute; left:-31px; top:22px;
  width:14px; height:14px; border-radius:50%;
  background:var(--accent); border:3px solid var(--bg);
  box-shadow:0 0 0 2px var(--accent-line);
}
.day-num {
  display:inline-block; font-size:11px; font-weight:800; letter-spacing:.6px;
  text-transform:uppercase; color:var(--accent); margin-bottom:6px;
}
.day-title { font-size:17px; font-weight:700; color:var(--text); margin-bottom:6px; }
.day-focus { font-size:13.5px; color:var(--text-muted); margin-bottom:14px; font-style:italic; }
.day-topics { display:flex; flex-wrap:wrap; gap:7px; margin-bottom:14px; }
.day-topic {
  padding:4px 11px; border-radius:20px; font-size:12px; font-weight:600;
  background:var(--accent-soft); color:var(--accent); border:1px solid var(--accent-line);
}
.day-activities { list-style:none; margin-bottom:12px; }
.day-activities li {
  font-size:13.5px; color:var(--text); padding:4px 0 4px 22px; position:relative;
}
.day-activities li::before { content:'›'; position:absolute; left:6px; color:var(--accent); font-weight:800; }
.day-check {
  display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600;
  color:var(--text); background:var(--surface-2); border:1px solid var(--border);
  border-radius:9px; padding:9px 13px;
}
.day-check .lbl { color:var(--accent); }

.tips-card { margin-top:6px; }
.tips-card ul { list-style:none; }
.tips-card li { font-size:14px; color:var(--text-muted); padding:6px 0 6px 24px; position:relative; }
.tips-card li::before { content:'💡'; position:absolute; left:0; }

.skel-line { height:90px; border-radius:14px; margin-bottom:16px; }
.empty-hint { text-align:center; padding:46px 20px; color:var(--text-muted); }
.empty-hint .ic { font-size:46px; margin-bottom:12px; opacity:.6; }
.err-msg { padding:16px; background:rgba(248,81,73,.08); border:1px solid rgba(248,81,73,.25); border-radius:12px; color:#ff7b72; font-size:14px; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> &nbsp;›&nbsp;
    <a href="docdetail.php?id=<?= $id ?>"><?= $safe_name ?></a> &nbsp;›&nbsp;
    <span>Study Plan</span>
  </div>

  <div class="sg-card-flat plan-controls">
    <label>📅 I have an exam in</label>
    <select class="day-select" id="daySelect">
      <option value="3">3 days</option>
      <option value="5" selected>5 days</option>
      <option value="7">7 days</option>
      <option value="10">10 days</option>
      <option value="14">14 days</option>
    </select>
    <button class="sg-btn sg-btn-primary" id="genBtn" onclick="generatePlan()">Generate Study Plan</button>
  </div>

  <div id="output">
    <div class="empty-hint" id="emptyHint">
      <div class="ic">🗓️</div>
      <p>Choose how many days you have, then generate a personalised, day-by-day study plan from this document.</p>
    </div>
  </div>
</div>

<script>
const docId = <?= intval($id) ?>;

async function generatePlan() {
  const days = document.getElementById('daySelect').value;
  const btn  = document.getElementById('genBtn');
  const out  = document.getElementById('output');
  btn.disabled = true; btn.textContent = 'Generating…';

  out.innerHTML = '<div class="skeleton skel-line"></div>'.repeat(parseInt(days) > 6 ? 5 : parseInt(days));

  try {
    const res  = await fetch('studyplan_bridge.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ doc_id: docId, days: parseInt(days) })
    });
    const data = await res.json();
    btn.disabled = false; btn.textContent = 'Regenerate Plan';

    if (data.error) { out.innerHTML = `<div class="err-msg">⚠️ ${escHtml(data.error)}</div>`; return; }
    renderPlan(data);
    showToast('Study plan ready!', 'success');
  } catch (e) {
    btn.disabled = false; btn.textContent = 'Generate Study Plan';
    out.innerHTML = `<div class="err-msg">❌ ${escHtml(e.message)}</div>`;
  }
}

function renderPlan(data) {
  const out = document.getElementById('output');
  let html = '<div class="timeline">';
  data.days.forEach(d => {
    const topics = (d.topics || []).map(t => `<span class="day-topic">${escHtml(t)}</span>`).join('');
    const acts   = (d.activities || []).map(a => `<li>${escHtml(a)}</li>`).join('');
    html += `
      <div class="day-card">
        <span class="day-num">Day ${d.day}</span>
        <div class="day-title">${escHtml(d.title || ('Day ' + d.day))}</div>
        ${d.focus ? `<div class="day-focus">${escHtml(d.focus)}</div>` : ''}
        ${topics ? `<div class="day-topics">${topics}</div>` : ''}
        ${acts ? `<ul class="day-activities">${acts}</ul>` : ''}
        ${d.checkpoint ? `<div class="day-check"><span class="lbl">✓ Checkpoint:</span> ${escHtml(d.checkpoint)}</div>` : ''}
      </div>`;
  });
  html += '</div>';

  if (data.tips && data.tips.length) {
    html += `<div class="sg-card-flat tips-card"><h2 class="section-title">Study Tips</h2><ul>` +
            data.tips.map(t => `<li>${escHtml(t)}</li>`).join('') + `</ul></div>`;
  }
  out.innerHTML = html;
}

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
