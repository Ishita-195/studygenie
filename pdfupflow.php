<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }

$msg = ""; $msgType = "";

if (isset($_POST["upload"])) {
    $file = $_FILES["pdfFile"];
    $name = $file["name"]; $tmp = $file["tmp_name"]; $size = $file["size"];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ["pdf", "docx"];
    if (!in_array($ext, $allowed))                 { $msg = "Only PDF and DOCX files are allowed."; $msgType = "error"; }
    elseif ($size > 100 * 1024 * 1024)             { $msg = "File too large. Maximum 100 MB."; $msgType = "error"; }
    else {
        $newName = time() . "_" . basename($name);
        require_once 'config.php';
        $user = $_SESSION['user_email'];
        $stmt = $con->prepare("INSERT INTO pdf_uploads (user_email, file_name, upload_time, file_path) VALUES (?, ?, NOW(), ?)");
        $filePath = "uploads/" . $newName;
        $stmt->bind_param("sss", $user, $newName, $filePath);
        if ($stmt->execute()) {
            move_uploaded_file($tmp, __DIR__ . "/uploads/" . $newName);
            $_SESSION["file_name"] = $newName;
            header("Location: pss.php"); exit();
        } else { $msg = "Database error. Please try again."; $msgType = "error"; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Upload PDF</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 640px; margin: auto; }

.upload-card {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  border-radius: 24px;
  box-shadow: var(--glass-shadow);
  padding: 36px;
  text-align: center;
}

.upload-card h2 { font-size: 22px; font-weight: 800; color: var(--g2); margin-bottom: 6px; }
.upload-card .subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 28px; }

.drop-area {
  border: 2.5px dashed rgba(76,175,80,.45);
  padding: 52px 24px;
  border-radius: 18px;
  background: rgba(76,175,80,.04);
  cursor: pointer;
  transition: all .25s;
  position: relative;
  margin-bottom: 20px;
}
.drop-area:hover, .drop-area.dragover {
  background: rgba(76,175,80,.1);
  border-color: #4caf50;
  transform: scale(1.01);
}

.drop-icon {
  font-size: 52px;
  display: block;
  margin-bottom: 14px;
  filter: drop-shadow(0 4px 8px rgba(76,175,80,.3));
  transition: transform .3s;
}
.drop-area:hover .drop-icon { transform: translateY(-4px) scale(1.05); }

.drop-text { font-size: 16px; font-weight: 600; color: var(--g2); margin-bottom: 6px; }
.drop-hint { font-size: 13px; color: var(--text-muted); }

.file-chosen {
  margin-top: 14px;
  padding: 10px 16px;
  background: rgba(76,175,80,.1);
  border-radius: 10px;
  color: var(--g2);
  font-size: 14px;
  font-weight: 600;
  display: none;
  word-break: break-all;
}

.sg-btn { width: 100%; padding: 14px; font-size: 16px; border-radius: 14px; }
.hint-text { font-size: 12px; color: var(--text-muted); margin-top: 12px; }

.alert {
  padding: 13px 16px; border-radius: 12px;
  font-size: 14px; font-weight: 600; margin-bottom: 20px;
  display: flex; align-items: center; gap: 8px;
}
.alert-error   { background: rgba(244,67,54,.08); color: #c62828; border: 1px solid rgba(244,67,54,.2); }
.alert-success { background: rgba(76,175,80,.1);  color: #2e7d32; border: 1px solid rgba(76,175,80,.25); }

.choose-btn {
  margin-top: 12px;
  padding: 9px 20px;
  background: rgba(76,175,80,.12);
  color: var(--g2);
  border: 1.5px solid rgba(76,175,80,.3);
  border-radius: 10px;
  font-family: inherit;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: .2s;
}
.choose-btn:hover { background: rgba(76,175,80,.2); }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="upload-card">
    <h2>Upload Study Material</h2>
    <p class="subtitle">Drop a PDF to enable AI Q&amp;A and instant quiz generation</p>

    <?php if (!empty($msg)): ?>
      <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>">
        <?= $msgType === 'error' ? '❌' : '✅' ?> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <div class="drop-area" id="dropArea">
        <span class="drop-icon">📄</span>
        <p class="drop-text">Drag &amp; drop your PDF or Word document here</p>
        <p class="drop-hint">or</p>
        <button type="button" class="choose-btn"
                onclick="document.getElementById('fileInput').click()">
          Browse File
        </button>
        <input type="file" id="fileInput" name="pdfFile" hidden accept=".pdf,.docx">
        <div class="file-chosen" id="fileChosen"></div>
      </div>

      <button type="submit" name="upload" class="sg-btn sg-btn-primary">
        ⚡ Upload &amp; Index
      </button>
      <p class="hint-text">PDF &amp; DOCX · Max 100 MB · Your file is private</p>
    </form>
  </div>
</div>

<script>
const dropArea  = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");
const fileChosen = document.getElementById("fileChosen");
const MAX = 100 * 1024 * 1024;

dropArea.addEventListener("dragover", e => { e.preventDefault(); dropArea.classList.add("dragover"); });
dropArea.addEventListener("dragleave", () => dropArea.classList.remove("dragover"));
dropArea.addEventListener("drop", e => { e.preventDefault(); dropArea.classList.remove("dragover"); handleFile(e.dataTransfer.files[0]); });
fileInput.addEventListener("change", () => handleFile(fileInput.files[0]));

function handleFile(file) {
  if (!file) return;
  const okExt = /\.(pdf|docx)$/i.test(file.name);
  if (!okExt) { showToast("Only PDF and DOCX files are allowed.", "error"); return; }
  if (file.size > MAX) { showToast("File exceeds 100 MB limit.", "error"); return; }
  const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files;
  fileChosen.style.display = "block";
  fileChosen.textContent = "✅ " + file.name + " (" + (file.size / 1024 / 1024).toFixed(1) + " MB)";
  showToast("File ready to upload!", "success");
}
</script>
<script src="routing.js"></script>
</body>
</html>
