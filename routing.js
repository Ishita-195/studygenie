function toPdfupflow() {
    window.location.href = 'pdfupflow.php';
}

function toPss() {
    window.location.href = 'pss.php';
}

function toDocDetail() {
    window.location.href = 'docdetail.php';
}

function toPss_s() {
    setTimeout(()=>{
window.location.href = 'pss.php'    
    },2000);
}

function toSummary(id){
    window.location.href = "ai.php?id=" + id;
}
function toAiAnalysis(id) {
    window.location.href = 'qa.php?id='+id;
}
function toQuiz(id) {
    window.location.href = 'quiz.php?id='+id;
}
function toAnalysis() {
    window.location.href = 'analysis.php';
}

