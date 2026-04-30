<?php session_start();
if (!isset($_SESSION["user_name"])) {
    header("Location: authentication.php");
}
$pdf_id = $_GET['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie Quiz</title>

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
    max-width:850px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
}

.question{
    font-size:18px;
    font-weight:600;
    margin-bottom:20px;
}

.options{
    list-style:none;
}

.options li{
    margin-bottom:12px;
}

.options input{
    margin-right:8px;
}

button{
    margin-top:20px;
    padding:12px 20px;
    border:none;
    background:#4caf50;
    color:white;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
}

button:hover{
    background:#388e3c;
}

.result{
    margin-top:25px;
    padding:15px;
    border-radius:12px;
    display:none;
}

.correct{
    background:#d4edda;
    color:#155724;
}

.incorrect{
    background:#ffebee;
    color:#c62828;
}

.summary{
    margin-top:20px;
    font-weight:600;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>Practice Quiz</div>
</div>

<div class="card">

    <div id="quizContainer">
        <div class="question" id="questionText"></div>

        <ul class="options" id="optionsList"></ul>

        <button onclick="submitAnswer()">Submit Answer</button>
    </div>

    <div class="result" id="resultBox"></div>
    <div class="summary" id="scoreSummary"></div>

</div>

<script>
let quizData = [];
let currentQuestion = 0;
let score = 0;
const pdf_id = <?php echo $pdf_id; ?>;

/* ============================= */
/*   GET QUIZ FROM PYTHON        */
/* ============================= */

async function loadQuizFromServer(){

    try{

        const response = await fetch("quiz_bridge.php?id=" + pdf_id);

        const data = await response.json();

        quizData = data.questions;

        if(!quizData || quizData.length === 0){
            document.getElementById("questionText").innerText =
            "No quiz questions received.";
            return;
        }

        loadQuestion();

    }catch(err){

        document.getElementById("questionText").innerText =
        "Error loading quiz from AI server.";
        console.error(err);
    }
}

loadQuizFromServer();

/* ============================= */
/*   LOAD QUESTION               */
/* ============================= */

function loadQuestion(){

    let q = quizData[currentQuestion];

    document.getElementById("questionText").innerText =
        (currentQuestion+1) + ". " + q.question;

    let optionsHTML = "";

    q.options.forEach((opt,index)=>{
        optionsHTML += `
        <li>
            <label>
                <input type="radio" name="option" value="${index}">
                ${opt}
            </label>
        </li>`;
    });

    document.getElementById("optionsList").innerHTML = optionsHTML;
}

/* ============================= */
/*   SUBMIT ANSWER               */
/* ============================= */

function submitAnswer(){

    let selected = document.querySelector('input[name="option"]:checked');

    if(!selected){
        alert("Please select an option.");
        return;
    }

    let answerIndex = parseInt(selected.value);
    let correctIndex = quizData[currentQuestion].answer;

    let resultBox = document.getElementById("resultBox");
    resultBox.style.display = "block";

    if(answerIndex === correctIndex){
        score++;
        resultBox.className = "result correct";
        resultBox.innerText = "✅ Correct!";
    }else{
        resultBox.className = "result incorrect";
        resultBox.innerText =
        "❌ Incorrect! Correct answer: " +
        quizData[currentQuestion].options[correctIndex];
    }

    currentQuestion++;

    if(currentQuestion < quizData.length){

        setTimeout(()=>{
            resultBox.style.display="none";
            loadQuestion();
        },1500);

    }else{
        showFinalScore();
    }
}

/* ============================= */
/*   FINAL SCORE                 */
/* ============================= */

function showFinalScore(){

    document.getElementById("quizContainer").style.display="none";

    let percent = Math.round((score/quizData.length)*100);

    document.getElementById("scoreSummary").innerHTML =
        "🎯 Quiz Completed!<br><br>" +
        "Score: " + score + " / " + quizData.length +
        "<br>Percentage: " + percent + "%<br><br>" +
        "<div style='margin-top:15px;padding:15px;background:#e8f5e9;border:1px solid #4CAF50;border-radius:10px;font-size:18px;'>"+
        "✅ Redirecting to dashboard in <b><span id='countdown'>2</span></b> seconds..." +
        "</div>";

    fetch("save_quiz_score.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            pdf_id: pdf_id,
            score: score,
            total: quizData.length,
            percentage: percent
        })
    });

    let timeLeft = 2;

    let timer = setInterval(function(){

        timeLeft--;

        document.getElementById("countdown").innerText = timeLeft;

        if(timeLeft <= 0){

            clearInterval(timer);

            window.location.href = "dashboard.php";

        }

    },1000);
}
</script>

</body>
</html>