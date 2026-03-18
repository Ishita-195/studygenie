<?php session_start();
if (!isset($_SESSION["user_name"])) {
    header("Location: authentication.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie Dashboard</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#e8f5e9;
    padding:25px;
}

/* HEADER */
.header{
    background:#ffffff;
    padding:18px 25px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,128,0,0.12);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.logo{
    font-size:28px;
    color:#2e7d32;
    font-weight:bold;
}

.logo span{
    color:#4caf50;
}

._name{
    color:#2e7d32;
    font-weight:600;
}

/* OVERVIEW CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,128,0,0.1);
    transition:.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    font-size:14px;
    color:#777;
    margin-bottom:10px;
}

.card .value{
    font-size:28px;
    color:#2e7d32;
    font-weight:bold;
}

/* ACTIVITY SECTION */
.activity{
    background:#fff;
    border-radius:18px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.1);
}

.activity h2{
    color:#2e7d32;
    margin-bottom:15px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:12px;
    text-align:left;
    border-bottom:1px solid #e0e0e0;
}

th{
    color:#4caf50;
}

.status{
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.processing{
    background:#fff3cd;
    color:#856404;
}

.completed{
    background:#d4edda;
    color:#155724;
}

.score{
    background:#4caf50;
    color:white;
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
}

.empty{
    text-align:center;
    padding:40px;
    color:#777;
}

.upload-btn{
    margin-top:15px;
    padding:12px 20px;
    background:#4caf50;
    border:none;
    border-radius:10px;
    color:white;
    font-size:15px;
    cursor:pointer;
}

a{
    color:black;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="header">
<?php include "navbar.php"; ?>
<div class="user">Welcome, <?php echo $_SESSION["user_name"]; ?></div>
</div>

<div class="cards">

<?php 
include 'config.php';

$sql = "SELECT COUNT(*) AS total FROM pdf_uploads";
$result = $con->query($sql);
$total = $result->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) AS total FROM pdf_uploads WHERE status='completed'";
$result = $con->query($sql);
$total_processed = $result->fetch_assoc()['total'];

$sql_score = "SELECT percentage FROM quiz_results";
$result = $con->query($sql_score);

$avg_score = 0;
$count_scores = 0;

if($result->num_rows > 0){
    while($row=$result->fetch_assoc()){
        $avg_score += $row['percentage'];
        $count_scores++;
    }
}

if($count_scores > 0){
    $avg_score = round($avg_score / $count_scores);
}
?>

<div class="card">
<h3>Total PDFs Uploaded</h3>
<div class="value"><?php echo $total; ?></div>
</div>

<div class="card">
<h3>PDFs Processed</h3>
<div class="value"><?php echo $total_processed; ?></div>
</div>

<div class="card">
<h3>Average Score</h3>
<div class="value"><?php echo $avg_score . '%'; ?></div>
</div>

</div>

<div class="activity">
<h2>Recent Activity</h2>

<table id="activityTable">
<thead>
<tr>
<th>File Name</th>
<th>Status</th>
<th>Score</th>
</tr>
</thead>

<tbody>

<?php

$sql = "SELECT p.id, p.file_name, p.status, q.percentage AS score
        FROM pdf_uploads p
        LEFT JOIN quiz_results q ON q.pdf_id = p.id
        ORDER BY p.upload_time DESC";

$result = $con->query($sql);

if($result->num_rows > 0){

while($row=$result->fetch_assoc()){

?>

<tr>

<td>
<a href="docdetail.php?id=<?php echo $row['id']; ?>">
<?php echo $row['file_name']; ?>
</a>
</td>

<td>
<span class="status <?php echo $row['status']; ?>">
<?php echo ucfirst($row['status']); ?>
</span>
</td>

<td>

<?php
if($row['score'] != null){
echo '<span class="score">'.$row['score'].'%</span>';
}else{
echo '-';
}
?>

</td>

</tr>

<?php
}
}
?>

</tbody>
</table>

<div class="empty" id="emptyState" style="display:none;">
<h3>No PDFs uploaded yet</h3>
<button class="upload-btn" onclick="window.location.href='pdfupflow.php'">
Upload PDF
</button>
</div>

</div>

<script>
const table = document.querySelector("#activityTable tbody");
const empty = document.getElementById("emptyState");

if(table.children.length === 0){
document.getElementById("activityTable").style.display="none";
empty.style.display="block";
}
</script>

<script src="routing.js"></script>

</body>
</html>