<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$total           = $con->query("SELECT COUNT(*) AS c FROM pdf_uploads")->fetch_assoc()['c'];
$total_processed = $con->query("SELECT COUNT(*) AS c FROM pdf_uploads WHERE status='completed'")->fetch_assoc()['c'];
$quiz_count      = $con->query("SELECT COUNT(*) AS c FROM quiz_results")->fetch_assoc()['c'];

$avg_score = 0;
$res = $con->query("SELECT percentage FROM quiz_results");
if ($res->num_rows > 0) {
    $sum = 0; while ($r = $res->fetch_assoc()) $sum += $r['percentage'];
    $avg_score = round($sum / $res->num_rows);
}

$docs = $con->query(
    "SELECT p.id, p.file_name, p.status, p.upload_time, q.percentage AS score
     FROM pdf_uploads p
     LEFT JOIN quiz_results q ON q.pdf_id = p.id
     ORDER BY p.upload_time DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Dashboard</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 1100px; margin: auto; }

/* STAT CARDS */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 22px;
}
.stat-card {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  box-shadow: var(--glass-shadow);
  padding: 22px 20px;
  transition: transform var(--transition), box-shadow var(--transition);
  cursor: default;
}
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,.18); }
.stat-icon {
  font-size: 26px;
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px;
}
.stat-icon-green  { background: rgba(76,175,80,.15); }
.stat-icon-teal   { background: rgba(0,188,212,.15); }
.stat-icon-purple { background: rgba(156,39,176,.12); }
.stat-icon-amber  { background: rgba(255,193,7,.15); }
.stat-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px; }
.stat-value {
  font-size: 34px; font-weight: 900;
  background: linear-gradient(135deg, #1b5e20, #43a047);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* PROGRESS RING */
.ring-wrap { display: flex; align-items: center; gap: 14px; }
.progress-ring { flex-shrink: 0; }
.progress-ring circle { transition: stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1); }

/* TABLE SECTION */
.section-head {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 18px;
}
.section-head h2 { font-size: 18px; font-weight: 800; color: var(--g2); }

table { width: 100%; border-collapse: collapse; }
th {
  font-size: 11px; font-weight: 800; text-transform: uppercase;
  letter-spacing: .7px; color: var(--text-muted);
  padding: 10px 14px; border-bottom: 1px solid rgba(0,0,0,.06);
  text-align: left;
}
td { padding: 13px 14px; border-bottom: 1px solid rgba(0,0,0,.04); font-size: 14px; }
tr:last-child td { border-bottom: none; }
tbody tr { transition: background .15s; cursor: pointer; }
tbody tr:hover td { background: rgba(76,175,80,.04); }

.file-link { color: var(--g2); font-weight: 600; }
.file-link:hover { text-decoration: underline; }

.score-pill {
  padding: 4px 12px; border-radius: 20px;
  font-size: 12px; font-weight: 800;
  background: linear-gradient(135deg,#2e7d32,#43a047);
  color: #fff;
}

.empty-state {
  text-align: center; padding: 56px 20px;
  color: var(--text-muted);
}
.empty-state .empty-icon { font-size: 52px; margin-bottom: 14px; opacity: .5; }
.empty-state p { font-size: 16px; margin-bottom: 20px; }

.time-ago { color: var(--text-muted); font-size: 12px; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <!-- Stats -->
  <div class="stats-grid">

    <div class="stat-card">
      <div class="stat-icon stat-icon-green">📄</div>
      <div class="stat-label">Total PDFs</div>
      <div class="stat-value" id="cnt-total" data-suffix=""><?= $total ?></div>
    </div>

    <div class="stat-card">
      <div class="stat-icon stat-icon-teal">✅</div>
      <div class="stat-label">Processed</div>
      <div class="stat-value" id="cnt-processed" data-suffix=""><?= $total_processed ?></div>
    </div>

    <div class="stat-card">
      <div class="stat-icon stat-icon-purple">🧪</div>
      <div class="stat-label">Quizzes Taken</div>
      <div class="stat-value" id="cnt-quizzes" data-suffix=""><?= $quiz_count ?></div>
    </div>

    <div class="stat-card">
      <div class="stat-icon stat-icon-amber">🎯</div>
      <div class="stat-label">Avg Quiz Score</div>
      <div class="ring-wrap">
        <svg class="progress-ring" width="58" height="58" viewBox="0 0 58 58">
          <circle cx="29" cy="29" r="24" fill="none" stroke="rgba(0,0,0,.07)" stroke-width="5"/>
          <circle id="ringCircle" cx="29" cy="29" r="24" fill="none"
                  stroke="url(#rg)" stroke-width="5" stroke-linecap="round"
                  stroke-dasharray="150.8" stroke-dashoffset="150.8"
                  transform="rotate(-90 29 29)"/>
          <defs>
            <linearGradient id="rg" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#2e7d32"/>
              <stop offset="100%" stop-color="#00e676"/>
            </linearGradient>
          </defs>
        </svg>
        <div class="stat-value" id="cnt-score" data-suffix="%"><?= $avg_score ?>%</div>
      </div>
    </div>

  </div>

  <!-- Documents table -->
  <div class="sg-card-flat">
    <div class="section-head">
      <h2>Your Documents</h2>
      <a href="pdfupflow.php" class="sg-btn sg-btn-primary">+ Upload PDF</a>
    </div>

    <?php if ($docs->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>File Name</th>
          <th>Uploaded</th>
          <th>Status</th>
          <th>Quiz Score</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $docs->fetch_assoc()):
        $dt = new DateTime($row['upload_time']);
        $ago = human_ago($dt);
      ?>
      <tr onclick="location.href='docdetail.php?id=<?= intval($row['id']) ?>'">
        <td>
          <a href="docdetail.php?id=<?= intval($row['id']) ?>" class="file-link" onclick="event.stopPropagation()">
            📄 <?= htmlspecialchars(clean_name($row['file_name'])) ?>
          </a>
        </td>
        <td class="time-ago"><?= $ago ?></td>
        <td>
          <?php if ($row['status'] === 'completed'): ?>
            <span class="badge badge-green">Completed</span>
          <?php else: ?>
            <span class="badge badge-yellow">Processing</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($row['score'] !== null): ?>
            <span class="score-pill"><?= intval($row['score']) ?>%</span>
          <?php else: ?>
            <span class="text-muted">—</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="docdetail.php?id=<?= intval($row['id']) ?>" class="sg-btn sg-btn-ghost"
             style="padding:6px 14px;font-size:12px;" onclick="event.stopPropagation()">Open →</a>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">📚</div>
      <p>No documents uploaded yet.</p>
      <a href="pdfupflow.php" class="sg-btn sg-btn-primary">Upload your first PDF</a>
    </div>
    <?php endif; ?>
  </div>

</div>

<script>
// Animated counters
document.addEventListener('DOMContentLoaded', function() {
  [
    ['cnt-total',     <?= $total ?>],
    ['cnt-processed', <?= $total_processed ?>],
    ['cnt-quizzes',   <?= $quiz_count ?>],
    ['cnt-score',     <?= $avg_score ?>],
  ].forEach(([id, val]) => {
    const el = document.getElementById(id);
    if (el) animateCount(el, val);
  });

  // Progress ring
  const ring = document.getElementById('ringCircle');
  if (ring) {
    const circumference = 150.8;
    const offset = circumference - (<?= $avg_score ?> / 100) * circumference;
    setTimeout(() => ring.style.strokeDashoffset = offset, 300);
  }
});
</script>

<script src="routing.js"></script>
</body>
</html>
<?php
function human_ago(DateTime $dt) {
    $diff = (new DateTime())->diff($dt);
    if ($diff->days === 0) return 'Today';
    if ($diff->days === 1) return 'Yesterday';
    if ($diff->days < 7)  return $diff->days . ' days ago';
    if ($diff->days < 30) return floor($diff->days/7) . ' wk ago';
    return $dt->format('d M Y');
}
?>
