<?php 
session_start();
if (!isset($_SESSION["user_name"])) {
    header("Location: authentication.php");
    exit;
}
require_once 'config.php';

$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$current_pdf = '';
$debug_msg = '';

if ($doc_id > 0) {
    $sql = "SELECT file_name, file_path FROM pdf_uploads WHERE id=$doc_id";
    $result = $con->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $current_pdf = basename($row['file_path'] ?? $row['file_name']);
        $_SESSION['current_pdf'] = $current_pdf;
        $debug_msg = "Document: $current_pdf";
    } else {
        $debug_msg = "Document not found (id=$doc_id)";
    }
} else {
    $debug_msg = "No document ID provided";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie RAG Q&A</title>

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

/* INPUT */
.input-area{
    display:flex;
    gap:10px;
}

input{
    flex:1;
    padding:12px;
    border-radius:10px;
    border:1px solid #ccc;
}

button{
    padding:12px 18px;
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

/* LOADING */
.loading{
    margin-top:15px;
    display:none;
    color:#2e7d32;
    font-weight:600;
}

/* ANSWER */
.answer-box{
    margin-top:20px;
    padding:20px;
    background:#f1f8f4;
    border-radius:15px;
    display:none;
}

.sources{
    margin-top:15px;
    font-size:14px;
    color:#555;
}

.confidence{
    margin-top:10px;
    font-weight:600;
    color:#2e7d32;
}

/* NO ANSWER */
.no-answer{
    margin-top:20px;
    padding:15px;
    background:#ffebee;
    color:#c62828;
    border-radius:12px;
    display:none;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>RAG Q&A</div>
</div>

<div style="background:#e8f5e9;padding:10px;margin:0 auto 15px;max-width:850px;font-family:monospace;font-size:12px;border-radius:8px;border:1px solid #c8e6c9;">
  DEBUG: id=<?php echo $doc_id; ?> | <?php echo $debug_msg; ?>
</div>

<div class="card">

    <h2>Ask a Question</h2>

    <div class="input-area">
        <input type="text" id="questionInput" placeholder="Ask something from your uploaded document...">
        <button onclick="askQuestion()">Ask</button>
    </div>

    <div class="loading" id="loading">🔍 Searching document...</div>

    <div class="answer-box" id="answerBox">
        <div id="answerText"></div>

        <div class="sources" id="sourcesText"></div>

        <div class="confidence" id="confidenceText"></div>
    </div>

    <div class="no-answer" id="noAnswer">
        ❌ Not enough information in document.
    </div>

</div>

<script>

/* ============================= */
/*   SIMULATED DOCUMENT DATA     */
/* ============================= */

const documentChunks = {
    "supervised": {
        text: "Supervised learning uses labeled datasets to train models for prediction tasks.",
        source: "Page 2 - Supervised Learning"
    },
    "unsupervised": {
        text: "Unsupervised learning identifies hidden patterns in data without labeled outputs.",
        source: "Page 3 - Unsupervised Learning"
    },
    "regression": {
        text: "Regression is used to predict continuous numerical values.",
        source: "Page 5 - Regression"
    },
    "classification": {
        text: "Classification predicts categorical labels such as spam or not spam.",
        source: "Page 6 - Classification"
    },
    "overfitting": {
        text: "Overfitting occurs when a model performs well on training data but poorly on unseen data.",
        source: "Page 8 - Overfitting"
    }
};

/* ============================= */
/*   ASK QUESTION FUNCTION       */
/* ============================= */

function askQuestion(){

    let question = document.getElementById("questionInput").value.trim();

    if(!question) return;

    document.getElementById("answerBox").style.display="none";
    document.getElementById("noAnswer").style.display="none";
    document.getElementById("loading").style.display="block";

    fetch("ask_bridge.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        body:"question="+encodeURIComponent(question)+"&doc_id=<?php echo $doc_id; ?>"
    })
    .then(res => res.json())
    .then(data => {

        document.getElementById("loading").style.display="none";

        if(data.status === "ok"){

            document.getElementById("answerText").innerText =
                "🤖 " + data.answer;

            document.getElementById("sourcesText").innerText =
                "📚 Sources used: " + data.sources.join(", ");

            document.getElementById("confidenceText").innerText =
                "AI Confidence: " + data.confidence + "%";

            document.getElementById("answerBox").style.display="block";

        }
        else{
            document.getElementById("noAnswer").style.display="block";
        }

    });
}
/* ============================= */
/*   DISPLAY ANSWER              */
/* ============================= */

function showAnswer(text,source){

    document.getElementById("answerText").innerText = "🤖 " + text;

    document.getElementById("sourcesText").innerText =
        "📚 Sources used: " + source;

    let confidence = Math.floor(Math.random()*15)+85;

    document.getElementById("confidenceText").innerText =
        "AI Confidence: " + confidence + "%";

    document.getElementById("answerBox").style.display="block";
}

</script>

</body>
</html>