<?php
session_start();
require_once 'config.php';
header("Content-Type: application/json");

$doc_id   = intval($_GET['id'] ?? 0);
$filename = "";

// Resolve doc_id to filename
if ($doc_id > 0) {
    $stmt = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $filename = basename($row['file_path'] ?? $row['file_name']);
    }
}

if (empty($filename) && isset($_SESSION['current_pdf'])) {
    $filename = $_SESSION['current_pdf'];
}

if (empty($filename)) {
    echo json_encode(["error" => "No document found for id=$doc_id. Upload a PDF first.", "questions" => []]);
    exit();
}

error_log("[QUIZ_BRIDGE] doc_id=$doc_id, filename=$filename");

// Check Python server
$status_response = @file_get_contents("http://127.0.0.1:5000/status");
if ($status_response === false) {
    echo json_encode(["error" => "AI server not running. Start with: cd python && python app.py", "questions" => []]);
    exit();
}

// Generate quiz — Python server auto-indexes if needed
$data = json_encode(["filename" => $filename, "num_questions" => 5]);
$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => $data,
        "timeout" => 120
    ]
];

$context = stream_context_create($options);
$result  = @file_get_contents("http://127.0.0.1:5000/generate-quiz", false, $context);

if ($result === false) {
    echo json_encode(["error" => "Quiz generation timed out. The AI server may be busy. Try again.", "questions" => []]);
    exit();
}

$response_data = json_decode($result, true);
if ($response_data === null) {
    echo json_encode(["error" => "Invalid JSON from AI server. Raw: " . substr($result, 0, 200), "questions" => []]);
    exit();
}

echo json_encode($response_data);
?>