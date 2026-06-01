<?php
// Proxy to Python /index-status — avoids CORS on direct JS→Python calls
session_start();
header("Content-Type: application/json");

$raw = @file_get_contents("http://127.0.0.1:5000/index-status");
if ($raw === false) {
    echo json_encode(["ready"=>[],"pending"=>[],"errors"=>[],"total_indexed"=>0,"server_offline"=>true]);
} else {
    echo $raw;
}
?>
