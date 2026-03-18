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
<title>StudyGenie Profile</title>

<style>

:root{
    --bg:#e8f5e9;
    --card:#ffffff;
    --text:#222;
    --accent:#2e7d32;
}

.dark{
    --bg:#121212;
    --card:#1e1e1e;
    --text:#f1f1f1;
    --accent:#4caf50;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:var(--bg);
    padding:30px;
    color:var(--text);
    transition:0.3s;
}

.header{
    background:var(--card);
    padding:18px 25px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.logo{
    font-size:26px;
    color:var(--accent);
    font-weight:bold;
}
.logo span{opacity:0.8}

.card{
    max-width:750px;
    margin:auto;
    background:var(--card);
    padding:28px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    margin-bottom:25px;
}

.card h2{
    color:var(--accent);
    margin-bottom:18px;
}

/* PROFILE INFO */
.info{
    margin-bottom:14px;
    font-size:16px;
}

.label{
    font-weight:600;
    opacity:0.7;
}

/* TOGGLES */
.setting{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
}

.switch{
    position:relative;
    width:50px;
    height:25px;
}

.switch input{
    display:none;
}

.slider{
    position:absolute;
    cursor:pointer;
    background:#ccc;
    border-radius:25px;
    width:100%;
    height:100%;
    transition:.3s;
}

.slider:before{
    content:"";
    position:absolute;
    height:19px;
    width:19px;
    left:3px;
    top:3px;
    background:white;
    border-radius:50%;
    transition:.3s;
}

input:checked + .slider{
    background:var(--accent);
}

input:checked + .slider:before{
    transform:translateX(25px);
}

.logout-btn{
    display: inline-block;
    padding: 8px 18px;
    background-color: #d32f2f;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: 0.3s ease;
}

.logout-btn:hover{
    background-color: #b71c1c;
    transform: scale(1.05);
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>Profile & Settings</div>
</div>

<!-- USER PROFILE -->
<div class="card">
    <h2>User Profile</h2>

    <div class="info">
        <span class="label">Name:</span> <span id="name"><?php echo $_SESSION["user_name"]; ?></span>
    </div>

    <div class="info">
        <span class="label">Email:</span> <span id="email"><?php echo $_SESSION["user_email"]; ?></span>
    </div>

    <div class="info">
        <span class="label">Joined Date:</span> <span id="joined"><?php echo date("d M Y", strtotime($_SESSION["user_date"])); ?></span>
    </div>
</div>

<!-- PREFERENCES -->
<div class="card">
    <h2>Preferences</h2>

    <div class="setting">
        <span>Dark Theme</span>
        <label class="switch">
            <input type="checkbox" id="themeToggle">
            <span class="slider"></span>
        </label>
    </div>

    <!-- <div class="setting">
        <span>Notifications</span>
        <label class="switch">
            <input type="checkbox" id="notifyToggle">
            <span class="slider"></span>
        </label>
    </div> -->

</div>

<div class="card">
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<script>

/* ============================= */
/*   FAKE USER DATA              */
/* ============================= */

// if(!localStorage.getItem("userName")){
//     localStorage.setItem("userName","Student User");
//     localStorage.setItem("userEmail","student@kiit.ac.in");
//     localStorage.setItem("joinedDate",new Date().toDateString());
// }

// document.getElementById("name").innerText =
//     localStorage.getItem("userName");

// document.getElementById("email").innerText =
//     localStorage.getItem("userEmail");

// document.getElementById("joined").innerText =
//     localStorage.getItem("joinedDate");

/* ============================= */
/*   THEME TOGGLE                */
/* ============================= */

const toggle = document.getElementById("themeToggle");

/* Load saved theme */
let savedTheme = localStorage.getItem("theme");

if(savedTheme === "dark"){
    document.body.classList.add("dark");
    toggle.checked = true;
}

/* Change theme */
toggle.addEventListener("change", function(){

    if(toggle.checked){
        document.body.classList.add("dark");
        localStorage.setItem("theme","dark");
    }else{
        document.body.classList.remove("dark");
        localStorage.setItem("theme","light");
    }

});
/* ============================= */
/*   NOTIFICATION TOGGLE         */
/* ============================= */

// const notifyToggle = document.getElementById("notifyToggle");

// if(localStorage.getItem("notify")==="on"){
//     notifyToggle.checked=true;
// }

// notifyToggle.addEventListener("change",()=>{
//     localStorage.setItem("notify",notifyToggle.checked?"on":"off");
// });

</script>

</body>
</html>