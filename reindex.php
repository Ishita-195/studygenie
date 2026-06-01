<?php
/**
 * reindex.php  —  Re-index all uploaded PDFs and generate summaries.
 * Visit: http://localhost/ad_lab/reindex.php
 * Safe to run multiple times.
 */
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$results = [];
$triggered = isset($_POST['run']);

if ($triggered) {
    // 1. Check Python server
    $status_raw = @file_get_contents("http://127.0.0.1:5000/status");
    if ($status_raw === false) {
        $results[] = ["type" => "error", "msg" => "❌ Python AI server not running. Start it with: cd python && python app.py"];
    } else {
        // 2. Trigger reindex-all on Python server
        $ctx = stream_context_create(["http" => ["method"=>"POST","header"=>"Content-Type: application/json\r\n","content"=>"{}","timeout"=>180]]);
        $reindex_raw = @file_get_contents("http://127.0.0.1:5000/reindex-all", false, $ctx);

        if ($reindex_raw === false) {
            $results[] = ["type"=>"error","msg"=>"❌ Reindex call timed out. Try again."];
        } else {
            $reindex = json_decode($reindex_raw, true);
            $indexed = $reindex['indexed'] ?? 0;
            $total   = $reindex['total']   ?? 0;
            $results[] = ["type"=>"ok","msg"=>"✅ Indexed $indexed / $total PDFs on AI server."];

            // 3. For each successfully indexed file, get a summary and update DB
            $file_results = $reindex['results'] ?? [];
            foreach ($file_results as $filename => $info) {
                if (($info['status'] ?? '') !== 'ok') {
                    $results[] = ["type"=>"warn","msg"=>"⚠️ $filename — " . ($info['error'] ?? 'failed')];
                    continue;
                }

                // Get summary from Python
                $sum_payload = json_encode(["filename" => $filename]);
                $sum_ctx = stream_context_create(["http"=>["method"=>"POST","header"=>"Content-Type: application/json\r\n","content"=>$sum_payload,"timeout"=>90]]);
                $sum_raw = @file_get_contents("http://127.0.0.1:5000/summarize", false, $sum_ctx);
                $summary_text = "";
                if ($sum_raw !== false) {
                    $sd = json_decode($sum_raw, true);
                    $summary_text = $sd['summary'] ?? '';
                }

                // Update DB
                $stmt = $con->prepare("UPDATE pdf_uploads SET status='completed', summary=? WHERE file_name=?");
                $stmt->bind_param("ss", $summary_text, $filename);
                $stmt->execute();
                $chunks = $info['chunks'] ?? '?';
                $results[] = ["type"=>"ok","msg"=>"✅ $filename — $chunks chunks" . ($summary_text ? ", summary generated" : "")];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Re-index Documents</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width:700px; margin:auto; }
.result-item { padding:11px 16px; border-radius:10px; margin-bottom:8px; font-size:14px; font-weight:600; word-break:break-all; }
.result-ok   { background:rgba(76,175,80,.1);  color:#2e7d32; }
.result-warn { background:rgba(255,193,7,.12); color:#856404; }
.result-error{ background:rgba(244,67,54,.08); color:#c62828; }
.info-box { padding:16px; background:rgba(33,150,243,.08); border:1px solid rgba(33,150,243,.2); border-radius:12px; font-size:14px; color:#1565c0; margin-bottom:20px; line-height:1.6; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="sg-card">
    <h2 class="section-title">🔄 Re-index All Documents</h2>

    <div class="info-box">
      <strong>When to use this:</strong><br>
      • After restarting the Python AI server (it loses its in-memory index)<br>
      • When documents show "not indexed" errors in Q&amp;A or Quiz<br>
      • After uploading documents manually to the uploads folder<br><br>
      ⏱ <strong>This may take 1-2 minutes</strong> for large PDFs.
    </div>

    <?php if ($triggered && !empty($results)): ?>
      <div style="margin-bottom:20px;">
        <?php foreach ($results as $r): ?>
          <div class="result-item result-<?= $r['type'] === 'ok' ? 'ok' : ($r['type'] === 'warn' ? 'warn' : 'error') ?>">
            <?= htmlspecialchars($r['msg']) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <a href="dashboard.php" class="sg-btn sg-btn-primary">← Back to Dashboard</a>
    <?php else: ?>
      <form method="POST">
        <button type="submit" name="run" class="sg-btn sg-btn-primary" style="font-size:16px;padding:14px 28px;">
          ⚡ Start Re-indexing All Documents
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
