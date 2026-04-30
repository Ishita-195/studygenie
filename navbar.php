<div class="logo" style="font-size:30px;cursor:pointer" onclick="openNav()" id="main">
&#9776; Study<span>Genie</span>
</div>

<style>

/* ========================= */
/* Navbar Base CSS           */
/* ========================= */

body {
  font-family: "Lato", sans-serif;
  transition: background-color .5s, color .5s;
}

#main {
  transition: margin-left .5s;
  padding: 10px;
}

/* ========================= */
/* Sidebar                   */
/* ========================= */

.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 2;
  top: 0;
  left: 0;
  background-color: white;
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 60px;
}

.sidenav a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 22px;
  color: #555;
  display: block;
  transition: 0.3s;
}

.sidenav a:hover {
  color: black;
}

.sidenav .closebtn {
  position: absolute;
  top: 0;
  right: 20px;
  font-size: 36px;
}

/* ========================= */
/* Overlay                   */
/* ========================= */

#overlay{
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.4);
  display: none;
  z-index: 1;
}

/* ========================= */
/* DARK MODE                 */
/* ========================= */

body.dark{
  background:#121212;
  color:#f1f1f1;
}

/* Sidebar dark */
body.dark .sidenav{
  background:#1e1e1e;
}

body.dark .sidenav a{
  color:#e0e0e0;
}

body.dark .sidenav a:hover{
  color:#4CAF50;
}

/* Dashboard sections */
body.dark .container,
body.dark .content,
body.dark .dashboard,
body.dark .card,
body.dark .box,
body.dark .section,
body.dark .recent-activity{
  background:#1e1e1e !important;
  color:#f1f1f1 !important;
}

/* Tables */
body.dark table{
  background:#1e1e1e !important;
}

body.dark th,
body.dark td{
  color:#f1f1f1 !important;
  border-color:#333;
}

/* Overlay darker */
body.dark #overlay{
  background: rgba(0,0,0,0.7);
}

/* ========================= */

@media screen and (max-height:450px){
  .sidenav {padding-top:15px;}
  .sidenav a {font-size:18px;}
}

</style>


<!-- ========================= -->
<!-- Sidebar Menu              -->
<!-- ========================= -->

<div id="mySidenav" class="sidenav">

  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>

  <a href="dashboard.php">🏠 Dashboard</a>
  <a href="pdfupflow.php">📤 Upload</a>
  <a href="analysis.php">📊 Analytics</a>
  <a href="pf.php">👤 Profile</a>

</div>

<div id="overlay"></div>


<script>

/* Sidebar controls */

function openNav(){
  document.getElementById("mySidenav").style.width="250px";
  document.getElementById("overlay").style.display="block";
}

function closeNav(){
  document.getElementById("mySidenav").style.width="0";
  document.getElementById("overlay").style.display="none";
}


/* Load saved theme */

document.addEventListener("DOMContentLoaded", function(){

  let theme = localStorage.getItem("theme");

  if(theme === "dark"){
    document.body.classList.add("dark");
  }

});

</script>