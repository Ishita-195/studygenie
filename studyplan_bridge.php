<?php
// Proxy to Python /study-plan
session_start();
require_once 'config.php';
header("Content-Type: application/json");

if (!isset($_SESSION["user_name"])) { echo json_encode(["error" => "Not authenticated"]); exit(); }

$input  = json_decode(file_get_contents("php://input"), true);
$doc_id = intval($input['doc_id'] ?? 0);
$days   = intval($input['days'] ?? 5);

$stmt = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
$stmt->bind_param("i", $doc_id); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { echo json_encode(["error" => "Document not found"]); exit(); }
$filename = basename($row['file_path'] ?? $row['file_name']);

if (@file_get_contents("http://127.0.0.1:5000/status") === false) {
    echo json_encode(["error" => "AI server not running. Start it: python/start_server.bat"]); exit();
}

$ctx = stream_context_create(["http" => [
    "method"  => "POST",
    "header"  => "Content-Type: application/json\r\n",
    "content" => json_encode(["filename" => $filename, "days" => $days]),
    "timeout" => 90
]]);
$raw = @file_get_contents("http://127.0.0.1:5000/study-plan", false, $ctx);
echo ($raw === false) ? json_encode(["error" => "AI server timed out. Try again."]) : $raw;
?>
