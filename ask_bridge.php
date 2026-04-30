<?php
session_start();
require_once 'config.php';

header("Content-Type: application/json");

$question = $_POST["question"] ?? "";
$doc_id = intval($_POST["doc_id"] ?? 0);
$filename = "";

// Get filename from database
if ($doc_id > 0) {
    $sql = "SELECT file_name, file_path FROM pdf_uploads WHERE id=$doc_id";
    $result = $con->query($sql);
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

if (trim($question) == "") {
    echo json_encode([
        "status" => "error",
        "answer" => "Please enter a question.",
        "sources" => [],
        "confidence" => 0
    ]);
    exit();
}

// First, check if file is indexed. If not, trigger indexing (non-blocking).
$status_response = @file_get_contents("http://127.0.0.1:5000/status");
$status_data = json_decode($status_response, true);
$indexed_files = $status_data['indexed_files'] ?? [];

if (!empty($filename) && !in_array($filename, $indexed_files)) {
    // Trigger indexing with short timeout (fire and forget)
    error_log("[ASK_BRIDGE] File not indexed, triggering index for: $filename");
    $index_data = json_encode(['filename' => $filename]);
    $index_options = [
        "http" => [
            "header" => "Content-Type: application/json\r\n",
            "method" => "POST",
            "content" => $index_data,
            "timeout" => 1  // Non-blocking: just trigger, don't wait
        ]
    ];
    $index_context = stream_context_create($index_options);
    @file_get_contents("http://127.0.0.1:5000/upload-and-index", false, $index_context);
    
    // Return message that indexing is in progress
    echo json_encode([
        "status" => "ok",
        "answer" => "Document is being indexed. Please wait 5 seconds and ask again.",
        "sources" => [$filename],
        "confidence" => 0
    ]);
    exit();
}

// Prepare request for Python RAG server
$data = json_encode([
    "question" => $question,
    "filename" => $filename
]);

$options = [
    "http" => [
        "header" => "Content-Type: application/json\r\n",
        "method" => "POST",
        "content" => $data,
        "timeout" => 30
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents("http://127.0.0.1:5000/ask", false, $context);

if ($result === FALSE) {
    echo json_encode([
        "status" => "error",
        "answer" => "AI backend server is not running. Start with: python app.py",
        "sources" => [],
        "confidence" => 0,
        "debug" => "filename=$filename"
    ]);
    exit();
}

// Ensure the response has expected fields
$response_data = json_decode($result, true);
if (!isset($response_data['confidence'])) {
    $response_data['confidence'] = 85;
}
if (!isset($response_data['sources'])) {
    $response_data['sources'] = [$filename];
}

echo json_encode($response_data);
?>
