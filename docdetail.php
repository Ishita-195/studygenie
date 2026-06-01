<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: dashboard.php"); exit(); }

$stmt = $con->prepare("SELECT file_name, upload_time, file_path, status, summary FROM pdf_uploads WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) { header("Location: dashboard.php"); exit(); }

if (isset($_POST['delete_file'])) {
    $del = $con->prepare("DELETE FROM pdf_uploads WHERE id = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        $f = __DIR__ . '/uploads/' . basename($row['file_name']);
        if (file_exists($f)) unlink($f);
        header("Location: dashboard.php"); exit();
    }
}

$safe_name   = htmlspecialchars(clean_name($row['file_name']));
$safe_path   = htmlspecialchars(basename($row['file_name']));
$filename    = basename($row['file_path'] ?? $row['file_name']);
$upload_date = date("d M Y · h:i A", strtotime($row['upload_time']));
$db_summary  = $row['summary'] ?? '';
$status      = $row['status'] ?? 'processing';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – <?= $safe_name ?></title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 920px; margin: auto; }

.breadcrumb { font-size:13px; color:rgba(255,255,255,.45); margin-bottom:18px; display:flex; align-items:center; gap:8px; }
.breadcrumb a { color:rgba(255,255,255,.55); } .breadcrumb a:hover { color:#fff; }

.file-title {
  font-size:20px; font-weight:800;
  background:linear-gradient(135deg,#1b5e20,#43a047);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  background-clip:text; word-break:break-all; margin-bottom:4px;
}
.file-meta { font-size:13px; color:var(--text-muted); margin-bottom:20px; }

/* Action cards */
.actions-grid {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
  gap:12px; margin-bottom:22px;
}
.action-card {
  padding:18px; border-radius:16px; display:flex; flex-direction:column;
  align-items:flex-start; gap:6px; cursor:pointer; border:1.5px solid transparent;
  text-decoration:none; transition:all .22s;
}
.action-card:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,.1); }
.action-card .ac-icon  { font-size:24px; }
.action-card .ac-label { font-size:14px; font-weight:700; }
.action-card .ac-desc  { font-size:12px; color:var(--text-muted); }
.ac-green  { background:rgba(76,175,80,.08);  border-color:rgba(76,175,80,.2);  }
.ac-blue   { background:rgba(33,150,243,.07); border-color:rgba(33,150,243,.2); }
.ac-purple { background:rgba(156,39,176,.07); border-color:rgba(156,39,176,.2); }
.ac-red    { background:rgba(244,67,54,.06);  border-color:rgba(244,67,54,.15); }
.ac-green .ac-label  { color:#2e7d32; }
.ac-blue  .ac-label  { color:#1565c0; }
.ac-purple .ac-label { color:#7b1fa2; }
.ac-red   .ac-label  { color:#c62828; }
.delete-form { width:100%; }
.delete-form .action-card { width:100%; background:rgba(244,67,54,.06); border:1.5px solid rgba(244,67,54,.15); font-family:inherit; cursor:pointer; }

/* Summary section */
.summary-section { margin-top:4px; }
.summary-text {
  font-size:15px; line-height:1.8; color:#1a1a2e;
  white-space:pre-wrap;
}
.cursor { display:inline-block; width:2px; height:1em; background:var(--g2); margin-left:2px; animation:blink .7s step-end infinite; vertical-align:text-bottom; }
@keyframes blink { 50%{opacity:0} }

.meta-chips { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; padding-top:12px; border-top:1px solid rgba(0,0,0,.06); }
.meta-chip  { padding:5px 13px; border-radius:20px; font-size:12px; font-weight:700; }
.chip-green  { background:rgba(76,175,80,.12); color:#2e7d32; }
.chip-blue   { background:rgba(33,150,243,.1); color:#1565c0; }
.chip-amber  { background:rgba(255,193,7,.12); color:#856404; }

/* Topics */
.topics-wrap { display:flex; flex-wrap:wrap; gap:10px; margin-top:6px; }
.topic-chip {
  padding:9px 16px; border-radius:24px; cursor:pointer;
  font-size:13px; font-weight:600;
  background:rgba(76,175,80,.08); color:var(--g2);
  border:1.5px solid rgba(76,175,80,.2);
  transition:all .2s;
}
.topic-chip:hover { background:rgba(76,175,80,.18); transform:translateY(-2px); }
.topic-chip.active { background:linear-gradient(135deg,#2e7d32,#43a047); color:#fff; border-color:transparent; }

.topic-answer {
  margin-top:14px; padding:20px;
  background:linear-gradient(135deg,rgba(232,245,233,.95),rgba(241,248,233,.95));
  border-left:4px solid #4caf50; border-radius:14px;
  font-size:14px; line-height:1.75; display:none; white-space:pre-wrap;
}

/* Processing badge */
.status-badge { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px; }
.badge-processing { background:rgba(255,193,7,.15); color:#856404; }
.badge-completed  { background:rgba(76,175,80,.15);  color:#2e7d32; }

.not-indexed-warn {
  padding:14px 16px; border-radius:12px;
  background:rgba(255,193,7,.1); border:1px solid rgba(255,193,7,.3);
  color:#856404; font-size:14px; font-weight:600; margin-bottom:16px;
  display:flex; align-items:center; gap:10px;
}

.skel-line { height:16px; border-radius:8px; margin-bottom:10px; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span style="opacity:.3">›</span>
    <span><?= $safe_name ?></span>
  </div>

  <!-- Header card -->
  <div class="sg-card" style="margin-bottom:16px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;">
      <div>
        <div class="file-title">📄 <?= $safe_name ?></div>
        <div class="file-meta">
          Uploaded: <?= $upload_date ?> &nbsp;·&nbsp;
          <span class="status-badge <?= $status === 'completed' ? 'badge-completed' : 'badge-processing' ?>">
            <?= $status === 'completed' ? '✅ Indexed' : '⏳ Processing' ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Action cards -->
    <div class="actions-grid">
      <a href="ai.php?id=<?= $id ?>" class="action-card ac-green">
        <span class="ac-icon">✨</span>
        <span class="ac-label">Full Summary</span>
        <span class="ac-desc">Detailed AI overview</span>
      </a>
      <a href="qa.php?id=<?= $id ?>" class="action-card ac-blue">
        <span class="ac-icon">🤖</span>
        <span class="ac-label">Chatbot</span>
        <span class="ac-desc">Chat with your doc</span>
      </a>
      <a href="quiz.php?id=<?= $id ?>" class="action-card ac-purple">
        <span class="ac-icon">🧪</span>
        <span class="ac-label">Take Quiz</span>
        <span class="ac-desc">Test your knowledge</span>
      </a>
      <form method="POST" class="delete-form" onsubmit="return confirm('Delete this file permanently?');">
        <button type="submit" name="delete_file" class="action-card ac-red">
          <span class="ac-icon">🗑️</span>
          <span class="ac-label">Delete File</span>
          <span class="ac-desc">Remove permanently</span>
        </button>
      </form>
    </div>
  </div>

  <!-- AI Summary inline -->
  <div class="sg-card" style="margin-bottom:16px;">
    <h2 class="section-title">📝 Document Summary</h2>

    <?php if ($status !== 'completed'): ?>
    <div class="not-indexed-warn">
      ⚠️ This document hasn't been indexed yet.
      <a href="pss.php" style="color:#856404;text-decoration:underline;margin-left:4px;">Re-process →</a>
    </div>
    <?php endif; ?>

    <div id="sumSkeleton">
      <div class="skeleton skel-line" style="width:93%"></div>
      <div class="skeleton skel-line" style="width:85%"></div>
      <div class="skeleton skel-line" style="width:90%"></div>
      <div class="skeleton skel-line" style="width:65%"></div>
    </div>

    <div class="summary-section" id="sumContent" style="display:none;">
      <div class="summary-text" id="summaryText"></div>
      <div class="meta-chips" id="metaChips"></div>
    </div>

    <div id="sumError" style="display:none;color:#c62828;font-size:14px;padding:10px 0;"></div>
  </div>

  <!-- Key Topics -->
  <div class="sg-card" id="topicsCard" style="margin-bottom:16px;display:none;">
    <h2 class="section-title">🔑 Key Topics</h2>
    <p class="text-muted" style="font-size:13px;margin-bottom:14px;">Click a topic to get an instant explanation from the document.</p>
    <div class="topics-wrap" id="topicsWrap"></div>
    <div class="topic-answer" id="topicAnswer"></div>
  </div>

</div>

<script>
const docFilename = <?= json_encode($filename) ?>;
const docId       = <?= intval($id) ?>;
const dbSummary   = <?= json_encode($db_summary) ?>;
const isIndexed   = <?= $status === 'completed' ? 'true' : 'false' ?>;

async function loadSummary() {
  if (dbSummary && dbSummary.length > 30) {
    // Show DB version instantly, no typing animation
    renderSummary({ summary: dbSummary, topics: [], word_count: 0, difficulty: 'Medium' }, false);
  }

  if (!isIndexed) {
    document.getElementById('sumSkeleton').style.display = 'none';
    if (!dbSummary) {
      document.getElementById('sumContent').style.display = 'block';
      document.getElementById('summaryText').textContent = 'This document has not been indexed yet. Please re-process it.';
    }
    return;
  }

  try {
    // Use PHP bridge — avoids CORS issues (browser→PHP→Python)
    const res  = await fetch(`summary_bridge.php?id=${docId}`);
    const data = await res.json();
    if (data.error) { showSumError(data.error); return; }
    renderSummary(data, !dbSummary);
    showToast('Summary ready!', 'success');
  } catch (e) {
    if (!dbSummary) showSumError('AI server not running. Start it with: python/start_server.bat');
  }
}

async function renderSummary(data, animate) {
  document.getElementById('sumSkeleton').style.display = 'none';
  const content = document.getElementById('sumContent');
  content.style.display = 'block';

  const el   = document.getElementById('summaryText');
  const text = data.summary || 'No summary available.';

  if (animate) {
    await typeText(el, text, 6);
  } else {
    el.textContent = text;
  }

  // Meta chips
  const wc   = data.word_count;
  const ch   = data.chunks;
  const diff = data.difficulty || 'Medium';
  const diffColor = { Easy:'chip-green', Medium:'chip-amber', Hard:'chip-amber' }[diff] || 'chip-amber';
  document.getElementById('metaChips').innerHTML =
    (wc ? `<span class="meta-chip chip-green">📖 ${wc.toLocaleString()} words</span>` : '') +
    (ch ? `<span class="meta-chip chip-blue">🧩 ${ch} chunks</span>` : '') +
    `<span class="meta-chip ${diffColor}">⚡ ${diff}</span>`;

  // Topics
  const topics = data.topics || [];
  if (topics.length > 0) {
    document.getElementById('topicsCard').style.display = 'block';
    const wrap = document.getElementById('topicsWrap');
    wrap.innerHTML = '';
    topics.forEach(t => {
      const chip = document.createElement('div');
      chip.className = 'topic-chip';
      chip.textContent = t;
      chip.onclick = () => askTopic(t, chip);
      wrap.appendChild(chip);
    });
  }
}

function showSumError(msg) {
  document.getElementById('sumSkeleton').style.display = 'none';
  const e = document.getElementById('sumError');
  e.style.display = 'block';
  e.textContent = '⚠️ ' + msg;
}

async function askTopic(topic, chipEl) {
  document.querySelectorAll('.topic-chip').forEach(c => c.classList.remove('active'));
  chipEl.classList.add('active');
  const box = document.getElementById('topicAnswer');
  box.style.display = 'block';
  box.textContent = '🔍 Looking up "' + topic + '"…';

  try {
    const res  = await fetch('ask_bridge.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `question=${encodeURIComponent('Explain ' + topic + ' as covered in this document.')}&doc_id=${docId}`
    });
    const data = await res.json();
    box.textContent = '';
    await typeText(box, data.answer || 'No information found.', 8);
  } catch (e) {
    box.textContent = '❌ ' + e.message;
  }
}

async function typeText(el, text, speed = 8) {
  el.textContent = '';
  const cursor = document.createElement('span');
  cursor.className = 'cursor';
  el.appendChild(cursor);
  for (let i = 0; i < text.length; i++) {
    el.insertBefore(document.createTextNode(text[i]), cursor);
    if (i % 4 === 0) await new Promise(r => setTimeout(r, speed));
  }
  cursor.remove();
}

loadSummary();
</script>
<script src="routing.js"></script>
</body>
</html>
