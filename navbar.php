<div class="sg-header">
  <div class="sg-logo" onclick="openNav()">
    ☰ &nbsp;StudyGenie
  </div>
  <div class="sg-header-right" id="navRight"></div>
</div>

<div id="mySidenav" class="sidenav">
  <span class="nav-brand">StudyGenie</span>
  <button class="closebtn" onclick="closeNav()">✕</button>
  <a href="dashboard.php">🏠&nbsp; Dashboard</a>
  <a href="pdfupflow.php">📤&nbsp; Upload PDF</a>
  <a href="analysis.php">📊&nbsp; Analytics</a>
  <a href="pf.php">👤&nbsp; Profile</a>
  <a href="reindex.php">🔄&nbsp; Re-index Docs</a>
  <a href="logout.php" style="margin-top:auto;color:rgba(255,100,100,.7);">🚪&nbsp; Logout</a>
</div>

<div id="overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Show user name in header if session data available
  const right = document.getElementById('navRight');
  if (right) {
    right.textContent = document.title.replace(' – StudyGenie','').replace('StudyGenie – ','');
  }
});
</script>
