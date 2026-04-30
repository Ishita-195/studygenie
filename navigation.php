<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie Navigation</title>

<style>

/* ===== BRAND COLORS ===== */
:root{
    --deep-green:#1b5e20;
    --light-green:#66bb6a;
    --bg:#e8f5e9;
    --card:#ffffff;
    --text:#222;
}

/* ===== GLOBAL ===== */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:var(--bg);
}

/* ===== TOP NAVBAR ===== */
.navbar{
    height:60px;
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 20px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
    position:fixed;
    width:100%;
    top:0;
    z-index:1000;
}

.logo{
    font-size:22px;
    font-weight:bold;
}

.logo .study{color:var(--deep-green);}
.logo .genie{color:var(--light-green);}

/* NAV LINKS */
.nav-links{
    display:flex;
    gap:25px;
}

.nav-links a{
    text-decoration:none;
    color:var(--text);
    font-weight:500;
}

.nav-links a:hover{
    color:var(--deep-green);
}

/* HAMBURGER */
.hamburger{
    font-size:24px;
    cursor:pointer;
    display:none;
}

/* ===== SIDEBAR ===== */
.sidebar{
    position:fixed;
    top:60px;
    left:0;
    width:220px;
    height:calc(100% - 60px);
    background:#fff;
    box-shadow:2px 0 10px rgba(0,0,0,0.08);
    padding-top:20px;
}

.side-item{
    padding:14px 20px;
    display:flex;
    align-items:center;
    gap:12px;
    cursor:pointer;
    color:#333;
}

.side-item:hover{
    background:#f1f8f4;
    color:var(--deep-green);
}

.icon{
    font-size:18px;
}

/* ===== CONTENT ===== */
.content{
    margin-left:220px;
    padding:90px 25px 80px 25px;
}

/* ===== MOBILE BOTTOM NAV ===== */
.bottom-nav{
    display:none;
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    height:65px;
    background:#fff;
    box-shadow:0 -3px 10px rgba(0,0,0,0.1);
    justify-content:space-around;
    align-items:center;
}

.bottom-item{
    text-align:center;
    font-size:12px;
    color:#333;
}

.bottom-item span{
    display:block;
    font-size:20px;
}

.bottom-item:hover{
    color:var(--deep-green);
}

/* ===== MOBILE VIEW ===== */
@media(max-width:768px){

    .sidebar{
        left:-240px;
        transition:.3s;
    }

    .sidebar.active{
        left:0;
    }

    .content{
        margin-left:0;
    }

    .nav-links{
        display:none;
    }

    .hamburger{
        display:block;
    }

    .bottom-nav{
        display:flex;
    }
}

</style>
</head>
<body>

<!-- TOP NAVBAR -->
<div class="navbar">
    <div class="logo">
        <span class="study">Study</span><span class="genie">Genie</span>
    </div>

    <div class="nav-links">
        <a href="#">Dashboard</a>
        <a href="#">Upload</a>
        <a href="#">Documents</a>
        <a href="#">Analytics</a>
        <a href="#">Profile</a>
    </div>

    <div class="hamburger" onclick="toggleSidebar()">☰</div>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="side-item"><span class="icon">🏠</span> Dashboard</div>
    <div class="side-item"><span class="icon">📤</span> Upload</div>
    <div class="side-item"><span class="icon">📄</span> Documents</div>
    <div class="side-item"><span class="icon">📊</span> Analytics</div>
    <div class="side-item"><span class="icon">👤</span> Profile</div>
</div>

<!-- CONTENT -->
<div class="content">
    <h1>Navigation Demo</h1>
    <p>This page demonstrates top nav, sidebar, and mobile navigation.</p>
</div>

<!-- MOBILE BOTTOM NAV -->
<div class="bottom-nav">
    <div class="bottom-item"><span>🏠</span>Home</div>
    <div class="bottom-item"><span>📤</span>Upload</div>
    <div class="bottom-item"><span>📄</span>Docs</div>
    <div class="bottom-item"><span>📊</span>Stats</div>
    <div class="bottom-item"><span>👤</span>Profile</div>
</div>

<script>
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("active");
}
</script>

</body>
</html>