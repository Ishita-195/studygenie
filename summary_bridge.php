<?php
/**
 * summary_bridge.php — Server-side proxy to Python /summarize
 * Called from docdetail.php and ai.php via fetch()
 * Routes: Browser → PHP (same origin) → Python (server-to-server)
 */
session_start();
require_once 'config.php';
header("Content-Type: application/json");

if (!isset($_SESSION["user_name"])) {
    echo json_encode(["error" => "Not authenticated"]);
    exit();
}

$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($doc_id <= 0) {
    echo json_encode(["error" => "Invalid document ID"]);
    exit();
}

// Get filename from DB
$stmt = $con->prepare("SELECT file_name, file_path, summary FROM pdf_uploads WHERE id = ?");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["error" => "Document not found"]);
    exit();
}

$filename    = basename($row['file_path'] ?? $row['file_name']);
$db_summary  = $row['summary'] ?? '';

// Check Python server is up
$status_raw = @file_get_contents("http://127.0.0.1:5000/status");
if ($status_raw === false) {
    // Return DB summary if available, otherwise error
    if ($db_summary) {
        echo json_encode([
            "status"     => "ok",
            "summary"    => $db_summary,
            "topics"     => [],
            "difficulty" => "Medium",
            "word_count" => 0,
            "chunks"     => 0,
            "source"     => "db_cache"
        ]);
    } else {
        echo json_encode(["error" => "AI server not running. Start it: double-click python/start_server.bat"]);
    }
    exit();
}

// Ensure the file is indexed first (handles new uploads not yet in memory)
$idx_ctx = stream_context_create(["http" => [
    "method"  => "POST",
    "header"  => "Content-Type: application/json\r\n",
    "content" => json_encode(["filename" => $filename]),
    "timeout" => 120
]]);
@file_get_contents("http://127.0.0.1:5000/upload-and-index", false, $idx_ctx);

// Call Python /summarize
$ctx = stream_context_create(["http" => [
    "method"  => "POST",
    "header"  => "Content-Type: application/json\r\n",
    "content" => json_encode(["filename" => $filename]),
    "timeout" => 120
]]);
$raw = @file_get_contents("http://127.0.0.1:5000/summarize", false, $ctx);

if ($raw === false) {
    // Fall back to DB cached summary if available
    if ($db_summary) {
        echo json_encode([
            "status"     => "ok",
            "summary"    => $db_summary,
            "topics"     => [],
            "difficulty" => "Medium",
            "word_count" => 0,
            "source"     => "db_cache"
        ]);
    } else {
        echo json_encode(["error" => "Could not generate summary. The AI server may be indexing this file — please wait a moment and refresh."]);
    }
    exit();
}

$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(["error" => "Invalid response from AI server."]);
    exit();
}

// If fresh summary differs from DB, update it
if (!empty($data['summary']) && $data['summary'] !== $db_summary) {
    $upd = $con->prepare("UPDATE pdf_uploads SET summary = ?, status = 'completed' WHERE id = ?");
    $upd->bind_param("si", $data['summary'], $doc_id);
    $upd->execute();
}

echo json_encode($data);
?>
