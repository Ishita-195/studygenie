<?php
/**
 * process_bridge.php
 * Called by pss.php after a new PDF is uploaded.
 * 1. Tells Python to index the file
 * 2. Generates AI summary
 * 3. Updates DB status + summary
 */
session_start();
require_once 'config.php';
header('Content-Type: application/json');

$filename = $_SESSION['file_name'] ?? '';
if (empty($filename)) {
    echo json_encode(["status"=>"error","message"=>"No filename in session"]);
    exit();
}

// ── 1. Check server is up ─────────────────────────────────────────────────
$status_raw = @file_get_contents("http://127.0.0.1:5000/status");
if ($status_raw === false) {
    echo json_encode(["status"=>"error","message"=>
        "AI server not running. Start it: double-click python/start_server.bat"]);
    exit();
}

// ── 2. Request indexing ───────────────────────────────────────────────────
$ctx = stream_context_create(["http"=>[
    "method"  => "POST",
    "header"  => "Content-Type: application/json\r\n",
    "content" => json_encode(["filename" => $filename]),
    "timeout" => 180          // large PDFs can take 2-3 min
]]);
$index_raw = @file_get_contents("http://127.0.0.1:5000/upload-and-index", false, $ctx);

if ($index_raw === false) {
    // Could be background indexing — check status
    $st_raw = @file_get_contents("http://127.0.0.1:5000/index-status");
    $st     = $st_raw ? json_decode($st_raw, true) : [];
    $ready  = in_array($filename, $st['ready']  ?? []);
    $pending= in_array($filename, $st['pending'] ?? []);
    if (!$ready && !$pending) {
        echo json_encode(["status"=>"error","message"=>
            "Indexing request failed — the AI server may be busy. Try again in a moment."]);
        exit();
    }
    if ($pending) {
        echo json_encode(["status"=>"pending","message"=>"Indexing in background"]);
        exit();
    }
    // ready — fall through to summary
    $chunks = 0;
} else {
    $indexed = json_decode($index_raw, true);
    if (!$indexed || ($indexed['status'] ?? '') !== 'indexed') {
        echo json_encode(["status"=>"error","message"=>$indexed['error'] ?? "Indexing failed"]);
        exit();
    }
    $chunks = $indexed['chunks'] ?? 0;
}

// ── 3. Generate AI summary ────────────────────────────────────────────────
$sum_ctx = stream_context_create(["http"=>[
    "method"  => "POST",
    "header"  => "Content-Type: application/json\r\n",
    "content" => json_encode(["filename" => $filename]),
    "timeout" => 90
]]);
$sum_raw      = @file_get_contents("http://127.0.0.1:5000/summarize", false, $sum_ctx);
$summary_text = "";
if ($sum_raw !== false) {
    $sd = json_decode($sum_raw, true);
    $summary_text = $sd['summary'] ?? '';
}

// ── 4. Update DB ──────────────────────────────────────────────────────────
$stmt = $con->prepare(
    "UPDATE pdf_uploads SET status='completed', summary=? WHERE file_name=?"
);
$stmt->bind_param("ss", $summary_text, $filename);
$stmt->execute();

echo json_encode([
    "status"  => "indexed",
    "message" => "Document indexed and summarised",
    "chunks"  => $chunks,
    "summary" => $summary_text ? "yes" : "fallback"
]);
?>
