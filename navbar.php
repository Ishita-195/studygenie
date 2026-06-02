<?php
// Persistent desktop sidebar + topbar. Included by all app pages.
$__cur   = basename($_SERVER['PHP_SELF']);
$__uname = $_SESSION['user_name'] ?? 'User';
if (!function_exists('nav_active')) {
    function nav_active($file, $cur) { return $file === $cur ? 'active' : ''; }
}
?>
<!-- Sidebar -->
<nav id="mySidenav" class="sidenav">
  <div class="nav-head">
    <span class="nav-brand">🧞 StudyGenie</span>
    <button class="closebtn" onclick="closeNav()" aria-label="Close menu">✕</button>
  </div>
  <div class="nav-links">
    <a href="dashboard.php" class="<?= nav_active('dashboard.php', $__cur) ?>"><span class="ni">🏠</span><span>Dashboard</span></a>
    <a href="pdfupflow.php" class="<?= nav_active('pdfupflow.php', $__cur) ?>"><span class="ni">📤</span><span>Upload PDF</span></a>
    <a href="analysis.php" class="<?= nav_active('analysis.php', $__cur) ?>"><span class="ni">📊</span><span>Analytics</span></a>
    <a href="pf.php" class="<?= nav_active('pf.php', $__cur) ?>"><span class="ni">👤</span><span>Profile</span></a>
    <a href="reindex.php" class="<?= nav_active('reindex.php', $__cur) ?>"><span class="ni">🔄</span><span>Re-index Docs</span></a>
  </div>
  <div class="nav-footer">
    <a href="logout.php" class="nav-logout"><span class="ni">🚪</span><span>Logout</span></a>
  </div>
</nav>

<!-- Topbar -->
<header class="sg-header">
  <button class="ham-btn" onclick="openNav()" aria-label="Open menu">☰</button>
  <div class="topbar-title" id="navRight"></div>
  <div class="topbar-user">
    <span class="tb-name"><?= htmlspecialchars($__uname) ?></span>
    <span class="tb-avatar"><?= strtoupper(substr($__uname, 0, 1)) ?></span>
  </div>
</header>

<div id="overlay"></div>

<script>
// Populate topbar title from the page's <title>
document.addEventListener('DOMContentLoaded', function () {
  const t = document.getElementById('navRight');
  if (t) t.textContent = document.title.replace(/StudyGenie\s*[–-]\s*/i, '').trim();
});
</script>
