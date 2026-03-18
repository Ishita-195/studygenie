<?php
session_start();
if (!isset($_SESSION["user_name"])) {
    header("Location: authentication.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie Analytics</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#e8f5e9;
    padding:30px;
}

.header{
    background:#fff;
    padding:18px 25px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,128,0,0.12);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.logo{
    font-size:26px;
    color:#2e7d32;
    font-weight:bold;
}
.logo span{color:#4caf50}

.card{
    max-width:900px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
    margin-bottom:25px;
}

.card h2{
    color:#2e7d32;
    margin-bottom:20px;
}

canvas{
    margin-top:10px;
}

.insight-box{
    background:#f1f8f4;
    padding:18px;
    border-radius:15px;
    line-height:1.6;
}

.highlight{
    color:#2e7d32;
    font-weight:600;
}

.improve{
    color:#c62828;
    font-weight:600;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>Analytics & Progress</div>
</div>

<?php
include 'config.php';

$user = $_SESSION["user_email"];

/* ============================= */
/*  PREPARE WEEKLY ARRAYS        */
/* ============================= */

$days = ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
$weeklyScores = [0,0,0,0,0,0,0];
$pdfProcessed = [0,0,0,0,0,0,0];

$sql = "SELECT upload_time 
        FROM pdf_uploads 
        WHERE user_email='$user' 
        AND upload_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";

$result = $con->query($sql);

if($result){
    while($row = $result->fetch_assoc()){

        $dayIndex = date('N', strtotime($row['upload_time'])) - 1;

        $pdfProcessed[$dayIndex] += 1;
        $weeklyScores[$dayIndex] += rand(60,95); 
    }
}

/* ============================= */
/*  CONVERT TOTALS TO AVERAGES   */
/* ============================= */

for($i=0;$i<7;$i++){
    if($pdfProcessed[$i] > 0){
        $weeklyScores[$i] = round($weeklyScores[$i] / $pdfProcessed[$i]);
    }
}
?>

<!-- WEEKLY SCORE TREND -->
<div class="card">
    <h2>Weekly Score Trend</h2>
    <canvas id="scoreChart"></canvas>
</div>

<!-- PDF PROCESSING TREND -->
<div class="card">
    <h2>PDFs Processed Over Time</h2>
    <canvas id="pdfChart"></canvas>
</div>

<!-- INSIGHTS -->
<div class="card">
    <h2>AI Insights</h2>
    <div class="insight-box" id="insightText">
        Generating insights...
    </div>
</div>

<script>

const days = <?php echo json_encode($days); ?>;
const weeklyScores = <?php echo json_encode($weeklyScores); ?>;
const pdfProcessed = <?php echo json_encode($pdfProcessed); ?>;

/* SCORE TREND CHART */

new Chart(document.getElementById("scoreChart"), {
    type: 'line',
    data: {
        labels: days,
        datasets: [{
            label: 'Score (%)',
            data: weeklyScores,
            borderColor: '#4caf50',
            backgroundColor: 'rgba(76,175,80,0.2)',
            tension: 0.4,
            fill:true
        }]
    },
    options: {
        responsive:true,
        plugins:{ legend:{display:false} },
        scales:{
            y:{
                beginAtZero:true,
                min:0,
                max:100
            }
        }
    }
});

/* PDF PROCESS CHART */

new Chart(document.getElementById("pdfChart"), {
    type: 'bar',
    data: {
        labels: days,
        datasets: [{
            label: 'PDFs',
            data: pdfProcessed,
            backgroundColor: '#81c784'
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{display:false} },
        scales:{
            y:{
                beginAtZero:true,
                suggestedMax:10,
                ticks:{
                    precision:0,
                    stepSize:1
                }
            }
        }
    }
});

/* AI INSIGHTS */

let avgScore = 0;

if(weeklyScores.length > 0){
    avgScore = Math.round(
        weeklyScores.reduce((a,b)=>a+b)/weeklyScores.length
    );
}

let bestDayIndex = weeklyScores.indexOf(Math.max(...weeklyScores));
let weakDayIndex = weeklyScores.indexOf(Math.min(...weeklyScores));

document.getElementById("insightText").innerHTML = `
📊 Your average weekly score is <span class="highlight">${avgScore}%</span>.<br><br>

🏆 You perform best on <span class="highlight">${days[bestDayIndex]}</span>
with a score of ${weeklyScores[bestDayIndex]}%.<br><br>

📌 Improve performance on <span class="improve">${days[weakDayIndex]}</span>
where your score dropped to ${weeklyScores[weakDayIndex]}%.<br><br>

📚 Keep practicing consistently to maintain upward growth.
`;

</script>

</body>
</html>