<?php
session_start();
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$pdf_id = $data['pdf_id'];
$score = $data['score'];
$total = $data['total'];
$percentage = $data['percentage'];
$user_email = $_SESSION["user_email"];

$sql = "INSERT INTO `quiz_results`
        (`pdf_id`,`score`,`total_questions`,`percentage`, `user_email`)
        VALUES
        ('$pdf_id','$score','$total','$percentage','$user_email')";

if($con->query($sql) == true){
    echo json_encode(["status"=>"saved"]);
}else{
    echo json_encode(["status"=>"error"]);
}
?>