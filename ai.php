<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: dashboard.php"); exit(); }

$stmt = $con->prepare("SELECT file_name, file_path, summary FROM pdf_uploads WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) { header("Location: dashboard.php"); exit(); }

$filename  = basename($row['file_path'] ?? $row['file_name']);
$doc_name  = $row['file_name'];
$db_summary = $row['summary'] ?? '';

$safe_name = htmlspecialchars(clean_name($doc_name));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Summary</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 900px; margin: auto; }

.breadcrumb { font-size:13px; color:rgba(255,255,255,.45); margin-bottom:18px; display:flex; align-items:center; gap:8px; }
.breadcrumb a { color:rgba(255,255,255,.55); } .breadcrumb a:hover { color:#fff; }

/* Summary card */
.summary-text {
  font-size: 16px; line-height: 1.85; color: var(--text);
  white-space: pre-wrap;
}
.cursor { display:inline-block; width:2px; height:1em; background:var(--accent); margin-left:2px; animation:blink .7s step-end infinite; vertical-align:text-bottom; }
@keyframes blink { 50%{opacity:0} }

/* Meta row */
.meta-row {
  display:flex; gap:10px; flex-wrap:wrap; margin-top:18px;
  padding-top:16px; border-top:1px solid var(--border);
}
.meta-chip {
  padding:6px 14px; border-radius:20px;
  font-size:13px; font-weight:600; display:flex; align-items:center; gap:5px;
}
.chip-green  { background:var(--accent-soft);     color:var(--accent); }
.chip-blue   { background:rgba(88,166,255,.13);   color:#79b8ff; }
.chip-purple { background:rgba(210,168,255,.13);  color:#d2a8ff; }
.chip-amber  { background:rgba(210,153,34,.16);   color:#e3b341; }

/* Topics */
.topics-grid { display:flex; flex-wrap:wrap; gap:10px; margin-top:4px; }
.topic-chip {
  padding:10px 18px; border-radius:30px; cursor:pointer;
  font-size:14px; font-weight:500;
  background:var(--surface-2); color:var(--text-muted);
  border:1px solid var(--border);
  transition:all .2s;
}
.topic-chip:hover { background:var(--accent-soft); border-color:var(--accent-line); color:var(--accent); transform:translateY(-2px); }
.topic-chip.active { background:var(--accent); color:#06140a; border-color:transparent; font-weight:600; }

/* Topic result */
.topic-answer {
  margin-top:18px; padding:22px;
  background:var(--surface-2); border:1px solid var(--border);
  border-left:3px solid var(--accent); border-radius:12px;
  font-size:15px; line-height:1.75; display:none; color:var(--text);
  white-space: pre-wrap;
}

/* Action buttons row */
.action-row { display:flex; gap:12px; flex-wrap:wrap; margin-top:22px; }

/* Loading skeleton */
.skel-line { height:18px; border-radius:8px; margin-bottom:12px; }

/* Error */
.err-msg { padding:16px; background:rgba(248,81,73,.08); border:1px solid rgba(248,81,73,.25); border-radius:12px; color:#ff7b72; font-size:14px; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> <span style="opacity:.3">›</span>
    <a href="docdetail.php?id=<?= $id ?>">Document</a> <span style="opacity:.3">›</span>
    <span>Summary</span>
  </div>

  <!-- Summary Card -->
  <div class="sg-card" style="margin-bottom:18px;" id="summaryCard">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
      <div style="font-size:28px;">📄</div>
      <div>
        <h2 class="section-title" style="margin-bottom:2px;"><?= $safe_name ?></h2>
        <span class="text-muted" style="font-size:13px;">AI-Generated Summary</span>
      </div>
    </div>

    <!-- Skeleton while loading -->
    <div id="sumSkeleton">
      <div class="skeleton skel-line" style="width:95%"></div>
      <div class="skeleton skel-line" style="width:88%"></div>
      <div class="skeleton skel-line" style="width:92%"></div>
      <div class="skeleton skel-line" style="width:70%"></div>
    </div>

    <div id="sumContent" style="display:none;">
      <div class="summary-text" id="summaryText"></div>
      <div class="meta-row" id="metaRow"></div>
    </div>

    <div id="sumError" class="err-msg" style="display:none;"></div>
  </div>

  <!-- Key Topics Card -->
  <div class="sg-card" style="margin-bottom:18px;" id="topicsCard">
    <h2 class="section-title">Key Topics</h2>
    <p class="text-muted" style="font-size:14px;margin-bottom:16px;">Click any topic to get a focused explanation from the document.</p>

    <div id="topicsSkeleton">
      <div class="skeleton skel-line" style="width:60%;height:38px;border-radius:30px;"></div>
    </div>

    <div class="topics-grid" id="topicsGrid" style="display:none;"></div>
    <div class="topic-answer" id="topicAnswer"></div>
  </div>

  <!-- Action Buttons -->
  <div class="sg-card-flat">
    <div class="action-row">
      <a href="qa.php?id=<?= $id ?>" class="sg-btn sg-btn-primary">💬 Ask Questions</a>
      <a href="quiz.php?id=<?= $id ?>" class="sg-btn sg-btn-ghost">🧪 Take Quiz</a>
      <a href="docdetail.php?id=<?= $id ?>" class="sg-btn sg-btn-ghost">← Back to Document</a>
    </div>
  </div>
</div>

<script>
const docFilename = <?= json_encode($filename) ?>;
const docId       = <?= intval($id) ?>;
const dbSummary   = <?= json_encode($db_summary) ?>;

async function loadSummary() {
  // If we already have a DB summary, show it instantly, then optionally refresh
  if (dbSummary && dbSummary.length > 30) {
    showSummary({ summary: dbSummary, topics: [], difficulty: 'Medium', word_count: 0 }, false);
  }

  try {
    // PHP bridge: browser → PHP → Python (no CORS)
    const res  = await fetch(`summary_bridge.php?id=${docId}`);
    const data = await res.json();

    if (data.error) { showError(data.error); return; }
    showSummary(data, true);
    showToast('Summary loaded!', 'success');
  } catch (e) {
    if (!dbSummary) {
      showError('AI server not responding. Start it with: python/start_server.bat');
    }
  }
}

async function showSummary(data, animate) {
  document.getElementById('sumSkeleton').style.display = 'none';
  document.getElementById('sumContent').style.display  = 'block';

  const el = document.getElementById('summaryText');
  const text = data.summary || 'No summary available.';

  if (animate && text.length > 0) {
    await typeText(el, text, 8);
  } else {
    el.textContent = text;
  }

  // Meta chips
  const meta = document.getElementById('metaRow');
  const diff = data.difficulty || 'Medium';
  const wc   = data.word_count || 0;
  const ch   = data.chunks || 0;
  const diffColor = { Easy:'chip-green', Medium:'chip-amber', Hard:'chip-purple' }[diff] || 'chip-amber';
  meta.innerHTML = `
    <span class="meta-chip chip-green">📖 ${wc > 0 ? wc.toLocaleString() + ' words' : 'Document indexed'}</span>
    <span class="meta-chip chip-blue">🧩 ${ch > 0 ? ch + ' chunks' : 'Ready'}</span>
    <span class="meta-chip ${diffColor}">⚡ ${diff} difficulty</span>
  `;

  // Topics
  const topics = data.topics || [];
  document.getElementById('topicsSkeleton').style.display = 'none';
  const grid = document.getElementById('topicsGrid');
  grid.style.display = 'flex';
  grid.innerHTML = '';

  if (topics.length > 0) {
    topics.forEach(t => {
      const chip = document.createElement('div');
      chip.className = 'topic-chip';
      chip.textContent = t;
      chip.onclick = () => askTopic(t, chip);
      grid.appendChild(chip);
    });
  } else {
    grid.innerHTML = '<span class="text-muted" style="font-size:14px;">Topics will appear after asking questions.</span>';
  }
}

function showError(msg) {
  document.getElementById('sumSkeleton').style.display  = 'none';
  document.getElementById('topicsSkeleton').style.display = 'none';
  document.getElementById('topicsGrid').style.display   = 'flex';
  document.getElementById('topicsGrid').innerHTML       = '';
  const err = document.getElementById('sumError');
  err.style.display = 'block';
  err.textContent = '❌ ' + msg;
  showToast(msg, 'error');
}

async function askTopic(topic, chipEl) {
  // Toggle active
  document.querySelectorAll('.topic-chip').forEach(c => c.classList.remove('active'));
  chipEl.classList.add('active');

  const box = document.getElementById('topicAnswer');
  box.style.display = 'block';
  box.textContent = '🔍 Looking up "' + topic + '" in the document…';

  try {
    const res  = await fetch('ask_bridge.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `question=${encodeURIComponent('Explain ' + topic + ' as covered in this document.')}&doc_id=${docId}`
    });
    const data = await res.json();
    box.textContent = '';
    await typeText(box, data.answer || 'No information found for this topic.', 10);
    showToast('Topic explained!', 'success');
  } catch (e) {
    box.textContent = '❌ Could not fetch explanation: ' + e.message;
  }
}

async function typeText(el, text, speed = 10) {
  el.textContent = '';
  const cursor = document.createElement('span');
  cursor.className = 'cursor';
  el.appendChild(cursor);
  for (let i = 0; i < text.length; i++) {
    el.insertBefore(document.createTextNode(text[i]), cursor);
    if (i % 3 === 0) await new Promise(r => setTimeout(r, speed));
  }
  cursor.remove();
}

loadSummary();
</script>
</body>
</html>
