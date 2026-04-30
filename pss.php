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
<title>StudyGenie Processing</title>

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
    margin-bottom:30px;
}

.card{
    max-width:650px;
    margin:auto;
    background:#fff;
    padding:35px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
}

.card h2{
    color:#2e7d32;
    margin-bottom:10px;
}

.info{
    color:#666;
    margin-bottom:25px;
}

/* PROGRESS BAR */
.progress-container{
    width:100%;
    height:18px;
    background:#e0e0e0;
    border-radius:20px;
    overflow:hidden;
    margin-bottom:25px;
}

.progress{
    height:100%;
    width:0%;
    background:#4caf50;
    transition:width .4s ease;
}

/* STEPS */
.steps{
    list-style:none;
}

.steps li{
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:10px;
    background:#f1f8f4;
    color:#777;
    display:flex;
    justify-content:space-between;
}

.steps li.active{
    background:#c8e6c9;
    color:#2e7d32;
    font-weight:600;
}

.steps li.done{
    background:#d4edda;
    color:#155724;
}

.safe{
    margin-top:25px;
    padding:15px;
    background:#f1f8f4;
    border-radius:12px;
    color:#2e7d32;
    text-align:center;
    font-weight:600;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>Processing File</div>
</div>

<div class="card">

    <h2>Processing your document...</h2>
    <p class="info">Our AI is analyzing your PDF. This may take a moment.</p>

    <div class="progress-container">
        <div class="progress" id="progressBar"></div>
    </div>

    <ul class="steps">
        <li id="step1">Extracting Text <span>⏳</span></li>
        <li id="step2">Chunking <span>⏳</span></li>
        <li id="step3">AI Analysis <span>⏳</span></li>
        <li id="step4">Storing Vectors <span>⏳</span></li>
    </ul>

    <div class="safe">
        You can safely leave this page. Processing continues in background.
    </div>

</div>

<script>

let progress = 0;
const bar = document.getElementById("progressBar");

const steps = [
    {id:"step1", limit:25},
    {id:"step2", limit:50},
    {id:"step3", limit:75},
    {id:"step4", limit:100}
];

function updateSteps(){

    steps.forEach(step => {

        const el = document.getElementById(step.id);

        if(progress >= step.limit){

            if(!el.classList.contains("done")){
                el.classList.remove("active");
                el.classList.add("done");
                el.innerHTML = el.innerHTML.replace("⏳","✔");
            }

        }
        else if(progress >= step.limit - 25){
            el.classList.add("active");
        }

    });

}

function animateProgress(){

    const interval = setInterval(()=>{

        progress += 25;

        bar.style.width = progress + "%";

        updateSteps();

        if(progress >= 100){

            clearInterval(interval);

            setTimeout(()=>{
                window.location.href="dashboard.php";
            },800);

        }

    },1000);

}

/* Start animation immediately */
animateProgress();

/* Run backend processing silently */
fetch("process_bridge.php", {
    method: "POST"
})
.then(res => res.json())
.then(data => console.log("Processing started:", data))
.catch(err => console.log("Background processing error:", err));

</script>

</body>
</html>