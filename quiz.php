<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
$pdf_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Quiz</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 780px; margin: auto; }

/* Header progress */
.quiz-meta {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 10px; font-size: 13px; font-weight: 600; color: var(--text-muted);
}
.progress-track {
  height: 7px; background: rgba(0,0,0,.07); border-radius: 10px;
  overflow: hidden; margin-bottom: 22px;
}
.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #2e7d32, #00e676);
  border-radius: 10px;
  transition: width .6s cubic-bezier(.4,0,.2,1);
  width: 0%;
}

/* Question */
.q-text {
  font-size: 19px; font-weight: 700; color: var(--text);
  line-height: 1.5; margin-bottom: 24px;
}

/* Options */
.options { list-style: none; display: flex; flex-direction: column; gap: 12px; }

.opt {
  padding: 16px 20px;
  border: 2px solid rgba(0,0,0,.09);
  border-radius: 14px;
  cursor: pointer;
  font-size: 15px; font-weight: 500;
  display: flex; align-items: center; gap: 14px;
  transition: all .2s;
  background: rgba(255,255,255,.6);
  backdrop-filter: blur(4px);
  user-select: none;
}
.opt:hover { border-color: #4caf50; background: rgba(76,175,80,.06); transform: translateX(4px); }
.opt.selected { border-color: #4caf50; background: rgba(76,175,80,.1); }
.opt.correct  {
  border-color: #2e7d32; background: rgba(76,175,80,.15);
  animation: correctBounce .4s ease;
}
.opt.wrong    {
  border-color: #e53935; background: rgba(244,67,54,.08);
  animation: wrongShake .4s ease;
}
@keyframes correctBounce { 0%,100%{transform:scale(1)} 50%{transform:scale(1.02)} }
@keyframes wrongShake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-6px)} 75%{transform:translateX(6px)} }

.opt-letter {
  width: 30px; height: 30px; flex-shrink: 0;
  border-radius: 50%; background: rgba(0,0,0,.06);
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 800; transition: .2s;
}
.opt.selected .opt-letter,
.opt.correct  .opt-letter { background: #4caf50; color: #fff; }
.opt.wrong    .opt-letter { background: #e53935; color: #fff; }

/* Feedback */
.feedback {
  margin-top: 18px; padding: 14px 18px;
  border-radius: 12px; font-size: 15px; font-weight: 700;
  display: none; animation: fadeIn .3s ease;
}
.feedback.correct { background: rgba(76,175,80,.12); color: #2e7d32; border: 1px solid rgba(76,175,80,.25); }
.feedback.wrong   { background: rgba(244,67,54,.08); color: #c62828; border: 1px solid rgba(244,67,54,.18); }

/* Submit */
.submit-wrap { margin-top: 22px; }
.sg-btn { width: 100%; padding: 14px; font-size: 16px; border-radius: 14px; }

/* Error */
.err-box {
  display: none; padding: 22px; border-radius: 16px;
  background: rgba(244,67,54,.07); border: 1px solid rgba(244,67,54,.2);
  color: #c62828; text-align: center;
}
.err-box p { margin-bottom: 14px; font-size: 15px; }

/* Skeleton */
.skel-q  { height: 28px; width: 90%; margin-bottom: 22px; }
.skel-opt{ height: 58px; border-radius: 14px; margin-bottom: 12px; }

/* Final score */
.final-wrap { text-align: center; padding: 10px 0; }
.score-circle {
  width: 140px; height: 140px; border-radius: 50%;
  background: linear-gradient(135deg, #1b5e20, #43a047);
  color: #fff; display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  margin: 24px auto;
  box-shadow: 0 12px 40px rgba(46,125,50,.35);
  animation: popIn .5s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes popIn { from { transform: scale(.5); opacity: 0; } }
.score-circle .pct { font-size: 38px; font-weight: 900; }
.score-circle .lbl { font-size: 13px; opacity: .8; }

.final-msg { font-size: 18px; font-weight: 700; color: var(--g2); margin-bottom: 8px; }
.final-sub { color: var(--text-muted); font-size: 14px; margin-bottom: 24px; }

.countdown-chip {
  display: inline-block;
  padding: 10px 22px;
  background: rgba(76,175,80,.1); border: 1px solid rgba(76,175,80,.2);
  border-radius: 30px; font-size: 14px; font-weight: 600; color: var(--g2);
}

.loading-state { text-align: center; padding: 40px 0; color: var(--text-muted); }
.spin { display: inline-block; font-size: 36px; animation: rotate 1.1s linear infinite; }
@keyframes rotate { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="sg-card" id="quizCard">
    <!-- progress -->
    <div class="quiz-meta">
      <span id="qCounter">Question 0 of 0</span>
      <span id="scoreTracker">Score: 0</span>
    </div>
    <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>

    <!-- skeleton loader shown initially -->
    <div id="skeletonLoader">
      <div class="skeleton skel-q"></div>
      <div class="skeleton skel-opt"></div>
      <div class="skeleton skel-opt"></div>
      <div class="skeleton skel-opt"></div>
      <div class="skeleton skel-opt"></div>
    </div>

    <div id="quizBody" style="display:none;">
      <div class="q-text" id="qText"></div>
      <ul class="options" id="optList"></ul>
      <div class="feedback" id="feedback"></div>
      <div class="submit-wrap">
        <button class="sg-btn sg-btn-primary" id="submitBtn" onclick="submitAnswer()" style="display:none;">
          Submit Answer →
        </button>
      </div>
    </div>
  </div>

  <div class="sg-card err-box" id="errBox">
    <p id="errText"></p>
    <button class="sg-btn sg-btn-primary" onclick="loadQuiz()" style="max-width:200px;margin:auto;">🔄 Retry</button>
  </div>

</div>

<script>
let quiz = [], cur = 0, score = 0;
const pdfId = <?= intval($pdf_id) ?>;

async function loadQuiz() {
  document.getElementById("skeletonLoader").style.display = "block";
  document.getElementById("quizBody").style.display  = "none";
  document.getElementById("errBox").style.display    = "none";
  document.getElementById("quizCard").style.display  = "block";
  setProgress(0);

  try {
    const res  = await fetch("quiz_bridge.php?id=" + pdfId);
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch { showErr("Unexpected response from server."); return; }
    if (data.error)             { showErr(data.error); return; }
    if (!data.questions?.length){ showErr("No questions generated. Try uploading a more text-rich PDF."); return; }

    quiz = data.questions; cur = 0; score = 0;
    document.getElementById("skeletonLoader").style.display = "none";
    document.getElementById("quizBody").style.display  = "block";
    renderQ();
    showToast("Quiz generated! " + quiz.length + " questions ready.", "success");
  } catch (e) { showErr("Network error: " + e.message); }
}

function setProgress(n) {
  const pct = quiz.length ? (n / quiz.length) * 100 : 0;
  document.getElementById("progressFill").style.width = pct + "%";
  document.getElementById("qCounter").textContent = `Question ${Math.min(n+1,quiz.length)} of ${quiz.length}`;
  document.getElementById("scoreTracker").textContent = `Score: ${score}`;
}

function renderQ() {
  setProgress(cur);
  const q = quiz[cur];
  document.getElementById("qText").textContent = (cur + 1) + ". " + q.question;

  const LETTERS = ["A","B","C","D"];
  document.getElementById("optList").innerHTML = q.options.map((opt, i) => `
    <li class="opt" id="opt${i}" onclick="selectOpt(${i})">
      <span class="opt-letter">${LETTERS[i]}</span>
      ${escHtml(opt)}
    </li>`).join("");

  document.getElementById("feedback").style.display = "none";

  // Reset submit button fully so it's ready for the new question
  const btn = document.getElementById("submitBtn");
  btn.style.display  = "none";
  btn.textContent    = "Submit Answer →";
  btn.disabled       = false;
  btn.onclick        = submitAnswer;
}

function selectOpt(i) {
  document.querySelectorAll(".opt").forEach(o => o.classList.remove("selected"));
  document.getElementById("opt" + i).classList.add("selected");
  document.getElementById("submitBtn").style.display = "block";
}

function submitAnswer() {
  const sel = document.querySelector(".opt.selected");
  if (!sel) { showToast("Please select an answer.", "info"); return; }

  const chosen  = parseInt(sel.id.replace("opt",""));
  const correct = quiz[cur].answer;

  document.getElementById("submitBtn").disabled = true;
  document.querySelectorAll(".opt").forEach((o, i) => {
    o.onclick = null;
    if (i === correct) o.classList.add("correct");
    else if (i === chosen && i !== correct) o.classList.add("wrong");
  });

  const fb = document.getElementById("feedback");
  if (chosen === correct) {
    score++;
    fb.className = "feedback correct";
    fb.textContent = "✅ Correct!";
    showToast("Correct! +1 point", "success");
  } else {
    fb.className = "feedback wrong";
    fb.textContent = "❌ Incorrect. Answer: " + escHtml(quiz[cur].options[correct]);
    showToast("Incorrect.", "error");
  }
  fb.style.display = "block";

  cur++;
  if (cur < quiz.length) {
    document.getElementById("submitBtn").textContent = "Next Question →";
    document.getElementById("submitBtn").disabled = false;
    document.getElementById("submitBtn").onclick = () => renderQ();
  } else {
    document.getElementById("submitBtn").textContent = "See Results 🎯";
    document.getElementById("submitBtn").disabled = false;
    document.getElementById("submitBtn").onclick = showFinal;
  }
}

function showFinal() {
  const pct = Math.round((score / quiz.length) * 100);
  const msg = pct >= 80 ? "🏆 Excellent work!" : pct >= 60 ? "👍 Good job!" : "📚 Keep practising!";
  const sub = `You scored ${score} out of ${quiz.length} questions correctly.`;

  document.getElementById("quizBody").innerHTML = `
    <div class="final-wrap">
      <div class="final-msg">${msg}</div>
      <div class="final-sub">${sub}</div>
      <div class="score-circle"><span class="pct">${pct}%</span><span class="lbl">${score}/${quiz.length}</span></div>
      <div class="countdown-chip">↩ Returning to dashboard in <b id="cd">5</b>s</div>
    </div>`;
  setProgress(quiz.length);

  fetch("save_quiz_score.php", {
    method:"POST", headers:{"Content-Type":"application/json"},
    body: JSON.stringify({ pdf_id: pdfId, score, total: quiz.length, percentage: pct })
  });

  let t = 5;
  const timer = setInterval(() => {
    t--;
    const el = document.getElementById("cd");
    if (el) el.textContent = t;
    if (t <= 0) {
      clearInterval(timer);         // ← stop the interval before navigating
      location.href = "dashboard.php";
    }
  }, 1000);
}

function showErr(msg) {
  document.getElementById("quizCard").style.display = "none";
  document.getElementById("errBox").style.display   = "block";
  document.getElementById("errText").textContent    = "❌ " + msg;
  showToast(msg, "error");
}

function escHtml(s) {
  return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
}

loadQuiz();
</script>
</body>
</html>
