<?php
session_start();
require_once 'config.php';
header("Content-Type: application/json");

$question = $_POST["question"] ?? "";
$doc_id   = intval($_POST["doc_id"] ?? 0);
$filename = "";

// Get filename from database
if ($doc_id > 0) {
    $stmt = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $filename = basename($row['file_path'] ?? $row['file_name']);
        $_SESSION['current_pdf'] = $filename;
    }
}

// Fallback to session
if (empty($filename) && isset($_SESSION['current_pdf'])) {
    $filename = $_SESSION['current_pdf'];
}

error_log("[ASK_BRIDGE] doc_id=$doc_id, filename=$filename, question=" . substr($question, 0, 50));

if (trim($question) === "") {
    echo json_encode(["status" => "error", "answer" => "Please enter a question.", "sources" => [], "confidence" => 0]);
    exit();
}

if (empty($filename)) {
    echo json_encode(["status" => "error", "answer" => "No document selected. Go back and select a document.", "sources" => [], "confidence" => 0]);
    exit();
}

// Check if Python server is running
$status_response = @file_get_contents("http://127.0.0.1:5000/status");
if ($status_response === false) {
    echo json_encode(["status" => "error", "answer" => "AI server not running. Start it with: cd python && python app.py", "sources" => [], "confidence" => 0]);
    exit();
}

// Ask the question directly — Python server auto-indexes if needed
$data    = json_encode(["question" => $question, "filename" => $filename]);
$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => $data,
        "timeout" => 120  // Enough time for indexing + answering
    ]
];
$context = stream_context_create($options);
$result  = @file_get_contents("http://127.0.0.1:5000/ask", false, $context);

if ($result === false) {
    echo json_encode(["status" => "error", "answer" => "AI server did not respond. It may be busy indexing. Try again in a few seconds.", "sources" => [$filename], "confidence" => 0]);
    exit();
}

$response_data = json_decode($result, true);

if ($response_data === null) {
    echo json_encode(["status" => "error", "answer" => "Invalid response from AI server.", "sources" => [], "confidence" => 0]);
    exit();
}

if (!isset($response_data['confidence'])) $response_data['confidence'] = 85;
if (!isset($response_data['sources']))    $response_data['sources'] = [$filename];

echo json_encode($response_data);
?>