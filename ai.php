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
<title>StudyGenie AI Analysis</title>

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
    max-width:800px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
    margin-bottom:25px;
}

.card h2{
    color:#2e7d32;
    margin-bottom:15px;
}

/* SUMMARY */
.summary{
    color:#444;
    line-height:1.6;
    margin-bottom:20px;
}

/* META BADGES */
.meta{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.badge{
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}

.readtime{
    background:#f1f8f4;
    color:#2e7d32;
}

.easy{background:#d4edda;color:#155724}
.medium{background:#fff3cd;color:#856404}
.hard{background:#ffebee;color:#c62828}

/* TOPICS */
.topics{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    margin-top:15px;
}

.topic{
    padding:10px 16px;
    border-radius:20px;
    background:#f1f8f4;
    color:#2e7d32;
    cursor:pointer;
    transition:.25s;
    font-weight:500;
}

.topic:hover{
    background:#c8e6c9;
}

/* CLICK RESULT */
.topic-result{
    margin-top:20px;
    padding:15px;
    background:#f1f8f4;
    border-radius:12px;
    display:none;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>AI Analysis</div>
</div>

<!-- SUMMARY -->
<div class="card">
    <h2>Document Summary</h2>
        <?php 
        include 'config.php';
        $id = $_GET['id'];
        $sql = "Select summary from pdf_uploads where id='$id'";
        $result = $con->query($sql);
        $row = $result->fetch_assoc();
        ?>

    <p class="summary" id="summaryText"><?php echo $row['summary']; ?></p>

    <div class="meta">
        <span class="badge readtime" id="readTime">⏱ Calculating...</span>
        <span class="badge medium" id="difficultyBadge">Medium Difficulty</span>
    </div>
</div>

<!-- KEY TOPICS -->
<div class="card">
    <h2>Key Topics</h2>

    <div class="topics" id="topicsContainer">
        <!-- Topics inserted dynamically -->
    </div>

    <div class="topic-result" id="topicResult"></div>

</div>

<script>

/* ============================= */
/*   GET FILE FROM UPLOAD PAGE   */
/* ============================= */

let file = localStorage.getItem("studyFile") || "document.pdf";

/* ============================= */
/*   FAKE AI SUMMARY GENERATOR   */
/* ============================= */

// document.getElementById("summaryText").innerText =
// "This document (" + file + ") has been analyzed using AI. It covers fundamental concepts, key definitions, important examples, and practical applications relevant to the subject. The system has extracted the most important study areas to help in quick revision and exam preparation.";

/* Random read time */
let minutes = Math.floor(Math.random() * 5) + 3;
document.getElementById("readTime").innerText = "⏱ " + minutes + " min read";

/* Random difficulty */
const levels = ["Easy","Medium","Hard"];
let level = levels[Math.floor(Math.random()*levels.length)];
let diffBadge = document.getElementById("difficultyBadge");
diffBadge.innerText = level + " Difficulty";
diffBadge.className = "badge " + level.toLowerCase();

/* ============================= */
/*   TOPIC DATABASE (FAKE AI)    */
/* ============================= */

const topicDB = {

    "ml":[
        "Supervised Learning",
        "Unsupervised Learning",
        "Regression",
        "Classification",
        "Overfitting",
        "Model Evaluation"
    ],

    "dsa":[
        "Arrays",
        "Linked Lists",
        "Stacks & Queues",
        "Trees",
        "Graphs",
        "Time Complexity"
    ],

    "os":[
        "Process Scheduling",
        "Deadlocks",
        "Memory Management",
        "Paging",
        "Threads",
        "CPU Scheduling"
    ],

    "dbms":[
        "Normalization",
        "Transactions",
        "Indexing",
        "SQL Queries",
        "ER Model",
        "Concurrency Control"
    ],

    "default":[
        "Introduction",
        "Core Concepts",
        "Important Definitions",
        "Examples",
        "Applications",
        "Summary"
    ]
};

/* ============================= */
/*   SUBJECT DETECTION           */
/* ============================= */

function detectSubject(name){
    name = name.toLowerCase();

    if(name.includes("ml") || name.includes("machine")) return "ml";
    if(name.includes("dsa") || name.includes("data")) return "dsa";
    if(name.includes("os") || name.includes("operating")) return "os";
    if(name.includes("db") || name.includes("database")) return "dbms";

    return "default";
}

let subject = detectSubject(file);
let topics = topicDB[subject];

/* ============================= */
/*   RENDER TOPICS               */
/* ============================= */

let topicContainer = document.getElementById("topicsContainer");

topics.forEach(topic=>{
    let div = document.createElement("div");
    div.className = "topic";
    div.innerText = topic;

    div.onclick = function(){
        showTopic(topic + " explained in simple terms for quick revision and better understanding.");
    };

    topicContainer.appendChild(div);
});

/* ============================= */
/*   TOPIC EXPLANATION DISPLAY   */
/* ============================= */

function showTopic(text){
    let box = document.getElementById("topicResult");
    box.style.display = "block";
    box.innerText = text;
}

</script>

</body>
</html>