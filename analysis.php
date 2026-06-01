<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$user = $_SESSION["user_email"];

// ── Real quiz results ─────────────────────────────────────────────────────
$stmt = $con->prepare(
    "SELECT q.score, q.total_questions, q.percentage, q.taken_at, p.file_name
     FROM quiz_results q
     LEFT JOIN pdf_uploads p ON p.id = q.pdf_id
     WHERE q.user_email = ?
     ORDER BY q.taken_at DESC"
);
$stmt->bind_param("s", $user);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Weekly score trend (last 7 days) ─────────────────────────────────────
$days        = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
$weekScores  = array_fill(0, 7, null);   // null = no data that day
$weekCounts  = array_fill(0, 7, 0);

foreach ($results as $r) {
    $di = intval(date('N', strtotime($r['taken_at']))) - 1;  // 0=Mon … 6=Sun
    $ts = strtotime($r['taken_at']);
    if ($ts >= strtotime('-7 days')) {
        $weekScores[$di] = ($weekScores[$di] ?? 0) + $r['percentage'];
        $weekCounts[$di]++;
    }
}
for ($i = 0; $i < 7; $i++) {
    if ($weekCounts[$i] > 0) {
        $weekScores[$i] = round($weekScores[$i] / $weekCounts[$i]);
    }
}

// ── Per-document stats ────────────────────────────────────────────────────
$docStmt = $con->prepare(
    "SELECT p.file_name, COUNT(q.id) AS attempts,
            MAX(q.percentage) AS best, AVG(q.percentage) AS avg_score,
            MAX(q.taken_at) AS last_taken
     FROM pdf_uploads p
     LEFT JOIN quiz_results q ON q.pdf_id = p.id AND q.user_email = ?
     WHERE p.user_email = ?
     GROUP BY p.id, p.file_name
     ORDER BY last_taken DESC"
);
$docStmt->bind_param("ss", $user, $user);
$docStmt->execute();
$docStats = $docStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Summary stats ──────────────────────────────────────────────────────────
$totalQuizzes  = count($results);
$avgScore      = $totalQuizzes > 0 ? round(array_sum(array_column($results,'percentage')) / $totalQuizzes) : 0;
$bestScore     = $totalQuizzes > 0 ? max(array_column($results,'percentage')) : 0;
$totalPdfs     = count($docStats);
$pdfsWithQuiz  = count(array_filter($docStats, fn($d) => $d['attempts'] > 0));

// Upload activity per day (last 7 days) ──────────────────────────────────
$upStmt = $con->prepare(
    "SELECT DAYOFWEEK(upload_time) AS dow, COUNT(*) AS cnt
     FROM pdf_uploads
     WHERE user_email = ? AND upload_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     GROUP BY dow"
);
$upStmt->bind_param("s", $user);
$upStmt->execute();
$upRows = $upStmt->get_result()->fetch_all(MYSQLI_ASSOC);
// MySQL DAYOFWEEK: 1=Sun, 2=Mon … 7=Sat → remap to Mon=0 … Sun=6
$uploadActivity = array_fill(0, 7, 0);
foreach ($upRows as $ur) {
    $mapped = ($ur['dow'] == 1) ? 6 : $ur['dow'] - 2;   // Sun=6, Mon=0
    $uploadActivity[$mapped] = intval($ur['cnt']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 1050px; margin: auto; }

/* Stat strip */
.stats-strip {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px; margin-bottom: 20px;
}
.stat-tile {
  background: var(--glass-bg);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  padding: 20px 18px;
  box-shadow: var(--glass-shadow);
  transition: transform var(--transition);
}
.stat-tile:hover { transform: translateY(-4px); }
.tile-icon { font-size: 24px; margin-bottom: 10px; }
.tile-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px; margin-bottom: 4px; }
.tile-value { font-size: 30px; font-weight: 900; background: linear-gradient(135deg,#1b5e20,#43a047); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }

/* Charts */
.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px; }
@media(max-width:700px) { .charts-row { grid-template-columns: 1fr; } }

.chart-card {
  background: var(--glass-bg); backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid var(--glass-border); border-radius: var(--radius);
  padding: 24px; box-shadow: var(--glass-shadow);
}
.chart-card h3 { font-size: 15px; font-weight: 800; color: var(--g2); margin-bottom: 16px; }

/* Table */
.doc-table { width: 100%; border-collapse: collapse; }
.doc-table th { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; padding: 10px 12px; border-bottom: 1px solid rgba(0,0,0,.06); text-align: left; }
.doc-table td { padding: 13px 12px; font-size: 14px; border-bottom: 1px solid rgba(0,0,0,.04); }
.doc-table tr:last-child td { border-bottom: none; }
.doc-table tbody tr:hover td { background: rgba(76,175,80,.04); }
.doc-name { color: var(--g2); font-weight: 600; font-size: 13px; word-break: break-all; max-width: 220px; }

.score-pill { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 800; }
.pill-great { background: linear-gradient(135deg,#2e7d32,#43a047); color: #fff; }
.pill-ok    { background: rgba(255,193,7,.2); color: #856404; }
.pill-low   { background: rgba(244,67,54,.1); color: #c62828; }

/* Insight box */
.insight-box {
  padding: 20px 22px; border-radius: 14px;
  background: linear-gradient(135deg,rgba(232,245,233,.9),rgba(241,248,233,.9));
  border-left: 4px solid #4caf50; font-size: 15px; line-height: 1.8; color: #1a1a2e;
}
.insight-box .hi  { color: #2e7d32; font-weight: 700; }
.insight-box .lo  { color: #c62828; font-weight: 700; }

.empty-state { text-align:center; padding:48px 20px; color:var(--text-muted); }
.empty-state .icon { font-size:44px; margin-bottom:14px; opacity:.5; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <!-- Stat strip -->
  <div class="stats-strip">
    <div class="stat-tile">
      <div class="tile-icon">📄</div>
      <div class="tile-label">Documents</div>
      <div class="tile-value" id="cnt-pdfs"><?= $totalPdfs ?></div>
    </div>
    <div class="stat-tile">
      <div class="tile-icon">🧪</div>
      <div class="tile-label">Quizzes Taken</div>
      <div class="tile-value" id="cnt-quizzes"><?= $totalQuizzes ?></div>
    </div>
    <div class="stat-tile">
      <div class="tile-icon">🎯</div>
      <div class="tile-label">Average Score</div>
      <div class="tile-value" id="cnt-avg"><?= $avgScore ?>%</div>
    </div>
    <div class="stat-tile">
      <div class="tile-icon">🏆</div>
      <div class="tile-label">Best Score</div>
      <div class="tile-value" id="cnt-best"><?= $bestScore ?>%</div>
    </div>
  </div>

  <!-- Charts row -->
  <div class="charts-row">
    <div class="chart-card">
      <h3>📈 Quiz Score Trend (Last 7 Days)</h3>
      <canvas id="scoreChart" height="180"></canvas>
    </div>
    <div class="chart-card">
      <h3>📤 Upload Activity (Last 7 Days)</h3>
      <canvas id="uploadChart" height="180"></canvas>
    </div>
  </div>

  <!-- Per-document table -->
  <div class="sg-card-flat" style="margin-bottom:18px;">
    <h2 class="section-title" style="margin-bottom:16px;">📚 Per-Document Performance</h2>

    <?php if ($docStats): ?>
    <div style="overflow-x:auto;">
    <table class="doc-table">
      <thead>
        <tr>
          <th>Document</th>
          <th>Quizzes</th>
          <th>Best Score</th>
          <th>Avg Score</th>
          <th>Last Activity</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($docStats as $d):
        $best = $d['best'] !== null ? intval($d['best']) : null;
        $avg  = $d['avg_score'] !== null ? round($d['avg_score']) : null;
        $pillClass = $best === null ? '' : ($best >= 80 ? 'pill-great' : ($best >= 60 ? 'pill-ok' : 'pill-low'));
        $lastDate  = $d['last_taken'] ? date('d M Y', strtotime($d['last_taken'])) : 'Never';
      ?>
      <tr>
        <td><div class="doc-name">📄 <?= htmlspecialchars(clean_name($d['file_name'])) ?></div></td>
        <td><?= $d['attempts'] ?: '—' ?></td>
        <td><?= $best !== null ? "<span class='score-pill $pillClass'>$best%</span>" : '<span style="color:#bbb">—</span>' ?></td>
        <td><?= $avg  !== null ? "$avg%" : '<span style="color:#bbb">—</span>' ?></td>
        <td style="font-size:12px;color:var(--text-muted);"><?= $lastDate ?></td>
        <td>
          <?php
          // Get ID from docStats — need a sub-query; find it via docdetail
          $idStmt = $con->prepare("SELECT id FROM pdf_uploads WHERE file_name = ? AND user_email = ? LIMIT 1");
          $idStmt->bind_param("ss", $d['file_name'], $user);
          $idStmt->execute();
          $idRow = $idStmt->get_result()->fetch_assoc();
          $docId = $idRow ? $idRow['id'] : 0;
          ?>
          <?php if ($docId): ?>
          <a href="quiz.php?id=<?= $docId ?>" class="sg-btn sg-btn-ghost" style="padding:5px 12px;font-size:12px;">
            <?= $d['attempts'] > 0 ? 'Retake' : 'Start Quiz' ?> →
          </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <div class="icon">📚</div>
      <p>No documents yet. <a href="pdfupflow.php" style="color:var(--g2);">Upload your first PDF →</a></p>
    </div>
    <?php endif; ?>
  </div>

  <!-- AI Insights -->
  <div class="sg-card-flat">
    <h2 class="section-title" style="margin-bottom:14px;">💡 Study Insights</h2>
    <div class="insight-box" id="insightBox">
      <?php
      if ($totalQuizzes === 0) {
          echo "No quiz data yet. Take some quizzes to see personalised insights here!";
      } else {
          $trend = "";
          $nonNull = array_values(array_filter($weekScores, fn($v) => $v !== null && $v > 0));
          if (count($nonNull) >= 2) {
              $trend = end($nonNull) >= $nonNull[0] ? "📈 Your scores are trending <span class='hi'>upward</span> this week. Keep it up!" : "📉 Your scores dipped recently. <span class='lo'>Review your materials</span> and try again.";
          }

          $bestDayIdx  = array_search(max($weekScores), $weekScores);
          $worstDayIdx = array_search(min(array_filter($weekScores, fn($v) => $v > 0) ?: [0]), $weekScores);
          $bestDocArr  = array_filter($docStats, fn($d) => $d['best'] !== null);
          usort($bestDocArr, fn($a,$b) => $b['best'] <=> $a['best']);
          $bestDoc     = $bestDocArr ? basename($bestDocArr[0]['file_name']) : null;

          echo "🎯 Your average score across <span class='hi'>$totalQuizzes quizzes</span> is <span class='hi'>$avgScore%</span>.<br><br>";
          if ($trend) echo "$trend<br><br>";
          if ($bestDoc) echo "🏆 Best performance: <span class='hi'>".htmlspecialchars($bestDoc)."</span> with a top score of <span class='hi'>{$bestDocArr[0]['best']}%</span>.<br><br>";
          if ($pdfsWithQuiz < $totalPdfs) {
              $untested = $totalPdfs - $pdfsWithQuiz;
              echo "📌 You have <span class='lo'>$untested document".($untested>1?'s':'')."</span> with no quiz attempts yet. Challenge yourself!";
          } else {
              echo "✅ You've taken quizzes on all your documents. Try uploading new material!";
          }
      }
      ?>
    </div>
  </div>

</div>

<script>
// Animate counters
document.addEventListener('DOMContentLoaded', () => {
  [['cnt-pdfs', <?= $totalPdfs ?>], ['cnt-quizzes', <?= $totalQuizzes ?>],
   ['cnt-avg', <?= $avgScore ?>], ['cnt-best', <?= $bestScore ?>]]
  .forEach(([id, val]) => {
    const el = document.getElementById(id);
    if (el) animateCount(el, val);
  });
});

const days = <?= json_encode($days) ?>;
const weekScores    = <?= json_encode(array_map(fn($v) => $v ?: null, $weekScores)) ?>;
const uploadActivity= <?= json_encode($uploadActivity) ?>;

// Score trend chart
new Chart(document.getElementById('scoreChart'), {
  type: 'line',
  data: {
    labels: days,
    datasets: [{
      label: 'Score (%)',
      data: weekScores,
      borderColor: '#4caf50',
      backgroundColor: 'rgba(76,175,80,0.12)',
      pointBackgroundColor: '#2e7d32',
      pointRadius: 5,
      tension: 0.4,
      fill: true,
      spanGaps: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, min: 0, max: 100,
           ticks: { callback: v => v + '%' },
           grid: { color: 'rgba(0,0,0,.04)' } },
      x: { grid: { display: false } }
    }
  }
});

// Upload activity chart
new Chart(document.getElementById('uploadChart'), {
  type: 'bar',
  data: {
    labels: days,
    datasets: [{
      label: 'PDFs Uploaded',
      data: uploadActivity,
      backgroundColor: 'rgba(76,175,80,0.65)',
      borderRadius: 8,
      borderSkipped: false
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 },
           grid: { color: 'rgba(0,0,0,.04)' } },
      x: { grid: { display: false } }
    }
  }
});
</script>
</body>
</html>
