<?php
// Temporary diagnostic: proxy to Python /groq-selftest to reveal the REAL Groq
// runtime status (whether actual API calls succeed, not just client init).
// Safe to delete once the AI pipeline is confirmed working.
header("Content-Type: application/json");
$raw = @file_get_contents("http://127.0.0.1:5000/groq-selftest");
if ($raw === false) {
    echo json_encode(["ok" => false, "reason" => "python_server_offline"]);
} else {
    echo $raw;
}
?>
