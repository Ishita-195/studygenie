<?php
/**
 * chat_bridge.php — Proxy to Python /chat endpoint
 * Supports conversation history for follow-up questions.
 * Browser → PHP → Python (server-side, no CORS)
 */
session_start();
require_once 'config.php';
header("Content-Type: application/json");

if (!isset($_SESSION["user_name"])) {
    echo json_encode(["status" => "error", "answer" => "Not authenticated."]);
    exit();
}

$input   = json_decode(file_get_contents("php://input"), true);
$doc_id  = intval($input['doc_id'] ?? 0);
$question = trim($input['question'] ?? '');
$history  = $input['history']  ?? [];   // [{role, content}, ...]

if (!$question) {
    echo json_encode(["status" => "error", "answer" => "Please enter a question."]);
    exit();
}

// Resolve filename from DB
if ($doc_id > 0) {
    $stmt = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $row      = $stmt->get_result()->fetch_assoc();
    $filename = $row ? basename($row['file_path'] ?? $row['file_name']) : '';
} else {
    $filename = $_SESSION['current_pdf'] ?? '';
}

if (!$filename) {
    echo json_encode(["status" => "error", "answer" => "No document selected. Go back and select a document."]);
    exit();
}

// Check Python server
if (@file_get_contents("http://127.0.0.1:5000/status") === false) {
    echo json_encode(["status" => "error", "answer" => "AI server not running. Start it with: python/start_server.bat"]);
    exit();
}

// Call /chat with history
$payload = json_encode([
    "filename" => $filename,
    "question" => $question,
    "history"  => array_slice($history, -12)   // send last 12 messages max
]);
$ctx = stream_context_create(["http" => [
    "method"  => "POST",
    "header"  => "Content-Type: application/json\r\n",
    "content" => $payload,
    "timeout" => 60
]]);
$raw = @file_get_contents("http://127.0.0.1:5000/chat", false, $ctx);

if ($raw === false) {
    echo json_encode(["status" => "error", "answer" => "AI server did not respond. Try again in a moment."]);
    exit();
}

$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(["status" => "error", "answer" => "Invalid response from AI server."]);
    exit();
}

echo json_encode($data);
?>
