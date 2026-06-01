<?php
// Proxy to Python /status — avoids direct JS→Python calls (CORS)
session_start();
header("Content-Type: application/json");

$raw = @file_get_contents("http://127.0.0.1:5000/status");
if ($raw === false) {
    echo json_encode(["status" => "offline", "indexed_files" => [], "groq_available" => false]);
} else {
    echo $raw;
}
?>
