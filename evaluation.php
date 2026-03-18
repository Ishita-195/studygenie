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
<title>StudyGenie Evaluation</title>

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

/* HEADER */
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

/* CARD */
.card{
    max-width:850px;
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

/* SCORE */
.overall-score{
    text-align:center;
    font-size:48px;
    font-weight:bold;
    color:#2e7d32;
    margin-bottom:25px;
}

/* METRIC */
.metric{
    margin-bottom:20px;
}

.metric-label{
    display:flex;
    justify-content:space-between;
    margin-bottom:6px;
    font-weight:600;
    color:#444;
}

.progress{
    width:100%;
    height:10px;
    background:#f1f8f4;
    border-radius:20px;
    overflow:hidden;
}

.progress-bar{
    height:100%;
    background:#4caf50;
    width:0%;
    transition:width 1s ease-in-out;
}

/* FEEDBACK */
.feedback-box{
    background:#f1f8f4;
    padding:20px;
    border-radius:15px;
    line-height:1.6;
    color:#444;
}

.strength{
    color:#2e7d32;
    font-weight:600;
}

.weakness{
    color:#c62828;
    font-weight:600;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>AI Evaluation</div>
</div>

<!-- SCORE BREAKDOWN -->
<div class="card">
    <h2>Score Breakdown</h2>

    <div class="overall-score" id="overallScore">0%</div>

    <div class="metric">
        <div class="metric-label">
            <span>Clarity</span>
            <span id="clarityValue">0%</span>
        </div>
        <div class="progress">
            <div class="progress-bar" id="clarityBar"></div>
        </div>
    </div>

    <div class="metric">
        <div class="metric-label">
            <span>Structure</span>
            <span id="structureValue">0%</span>
        </div>
        <div class="progress">
            <div class="progress-bar" id="structureBar"></div>
        </div>
    </div>

    <div class="metric">
        <div class="metric-label">
            <span>Concept Coverage</span>
            <span id="coverageValue">0%</span>
        </div>
        <div class="progress">
            <div class="progress-bar" id="coverageBar"></div>
        </div>
    </div>

</div>

<!-- AI FEEDBACK -->
<div class="card">
    <h2>AI Feedback</h2>

    <div class="feedback-box" id="feedbackText">
        Generating AI feedback...
    </div>
</div>

<script>

/* ============================= */
/*   FAKE AI SCORE GENERATOR     */
/* ============================= */

function randomScore(min,max){
    return Math.floor(Math.random()*(max-min+1))+min;
}

let clarity = randomScore(60,95);
let structure = randomScore(60,95);
let coverage = randomScore(60,95);

let overall = Math.floor((clarity + structure + coverage)/3);

/* Display overall */
document.getElementById("overallScore").innerText = overall + "%";

/* Animate bars */
function setBar(id,value,labelId){
    document.getElementById(id).style.width = value + "%";
    document.getElementById(labelId).innerText = value + "%";
}

setTimeout(()=>{
    setBar("clarityBar",clarity,"clarityValue");
    setBar("structureBar",structure,"structureValue");
    setBar("coverageBar",coverage,"coverageValue");
},300);

/* ============================= */
/*   AI FEEDBACK LOGIC           */
/* ============================= */

let strengths = [];
let weaknesses = [];

if(clarity > 80) strengths.push("Excellent clarity in explanation.");
else weaknesses.push("Improve clarity and simplify explanations.");

if(structure > 80) strengths.push("Well-structured and organized content.");
else weaknesses.push("Content structure can be improved.");

if(coverage > 80) strengths.push("Strong concept coverage with good depth.");
else weaknesses.push("Add more concept coverage and examples.");

let feedbackHTML = "";

if(strengths.length){
    feedbackHTML += "<p class='strength'>Strengths:</p><ul>";
    strengths.forEach(s=>feedbackHTML += "<li>"+s+"</li>");
    feedbackHTML += "</ul><br>";
}

if(weaknesses.length){
    feedbackHTML += "<p class='weakness'>Areas for Improvement:</p><ul>";
    weaknesses.forEach(w=>feedbackHTML += "<li>"+w+"</li>");
    feedbackHTML += "</ul>";
}

document.getElementById("feedbackText").innerHTML = feedbackHTML;

</script>

</body>
</html>