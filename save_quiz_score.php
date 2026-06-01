<?php
session_start();
require_once 'config.php';
header("Content-Type: application/json");

if (!isset($_SESSION["user_email"])) {
    echo json_encode(["status" => "error", "message" => "Not authenticated"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$pdf_id     = isset($data['pdf_id'])     ? intval($data['pdf_id'])     : 0;
$score      = isset($data['score'])      ? intval($data['score'])      : 0;
$total      = isset($data['total'])      ? intval($data['total'])      : 0;
$percentage = isset($data['percentage']) ? intval($data['percentage']) : 0;
$user_email = $_SESSION["user_email"];

if ($pdf_id <= 0 || $total <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit();
}

$stmt = $con->prepare(
    "INSERT INTO quiz_results (pdf_id, score, total_questions, percentage, user_email)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("iiiis", $pdf_id, $score, $total, $percentage, $user_email);

if ($stmt->execute()) {
    echo json_encode(["status" => "saved"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB error"]);
}
?>
