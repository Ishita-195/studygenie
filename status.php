<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie States</title>

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

/* ================= BRAND COLORS ================= */
:root{
    --deep-green:#1b5e20;
    --light-green:#66bb6a;
}

/* ================= HEADER ================= */
.header{
    background:#fff;
    padding:18px 25px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,128,0,0.12);
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* LOGO */
.logo{
    font-size:26px;
    font-weight:bold;
}

.logo .study{
    color:var(--deep-green);
}

.logo .genie{
    color:var(--light-green);
}

/* ================= CARD ================= */
.card{
    max-width:850px;
    margin:25px auto;
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
}

/* ================= ERROR ================= */
.error-box{
    text-align:center;
    color:#c62828;
}

.error-box h2{
    margin-bottom:10px;
}

.retry-btn{
    margin-top:15px;
    padding:10px 18px;
    border:none;
    background:var(--deep-green);
    color:#fff;
    border-radius:10px;
    cursor:pointer;
}

.retry-btn:hover{
    background:#0d3c14;
}

/* ================= EMPTY ================= */
.empty{
    text-align:center;
    color:#666;
}

.cta{
    margin-top:15px;
    padding:10px 18px;
    border:none;
    background:var(--light-green);
    color:#fff;
    border-radius:10px;
    cursor:pointer;
}

.cta:hover{
    background:#4caf50;
}

/* ================= SKELETON ================= */
.skeleton{
    background:linear-gradient(
        90deg,
        #f0f0f0 25%,
        #e0e0e0 37%,
        #f0f0f0 63%
    );
    background-size:400% 100%;
    animation:shimmer 1.4s infinite;
    border-radius:8px;
}

@keyframes shimmer{
    0%{background-position:100% 0}
    100%{background-position:-100% 0}
}

.s-line{
    height:14px;
    margin-bottom:12px;
}

.s-card{
    height:70px;
    margin-bottom:15px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

</style>
</head>
<body>

<div class="header">
    <div class="logo">
        <span class="study">Study</span>
        <span class="genie">Genie</span>
    </div>
    <div>Error & Edge States</div>
</div>

<!-- GLOBAL ERROR -->
<div class="card">
    <div class="error-box">
        <h2>⚠️ Something went wrong</h2>
        <p id="errorText">Unable to connect to server (API failure)</p>
        <button class="retry-btn" onclick="retry()">Retry</button>
    </div>
</div>

<!-- NETWORK ERROR -->
<div class="card">
    <div class="error-box">
        <h2>📡 Network Issue</h2>
        <p>No internet connection detected.</p>
        <button class="retry-btn" onclick="retry()">Try Again</button>
    </div>
</div>

<!-- DASHBOARD SKELETON -->
<div class="card">
    <h3>Dashboard Loading Skeleton</h3>
    <div class="grid">
        <div class="skeleton s-card"></div>
        <div class="skeleton s-card"></div>
        <div class="skeleton s-card"></div>
        <div class="skeleton s-card"></div>
    </div>
</div>

<!-- DOCUMENT PAGE SKELETON -->
<div class="card">
    <h3>Document Page Loading</h3>
    <div class="skeleton s-line"></div>
    <div class="skeleton s-line"></div>
    <div class="skeleton s-line"></div>
    <div class="skeleton s-line"></div>
</div>

<!-- EMPTY STATE: NO UPLOADS -->
<div class="card">
    <div class="empty">
        <h2>📭 No PDFs uploaded yet</h2>
        <p>Upload a document to start AI analysis</p>
        <button class="cta">Upload PDF</button>
    </div>
</div>

<!-- EMPTY STATE: NO ANALYSIS -->
<div class="card">
    <div class="empty">
        <h2>🧠 No analysis available</h2>
        <p>Process the document to generate AI insights</p>
        <button class="cta">Start Processing</button>
    </div>
</div>

<script>
function retry(){
    alert("Retrying connection...");
}
</script>

</body>
</html>