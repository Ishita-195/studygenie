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
    <span class="nav-brand">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 6v6c0 5 3.5 8 8 10 4.5-2 8-5 8-10V6l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg>
      StudyGenie
    </span>
    <button class="closebtn" onclick="closeNav()" aria-label="Close menu">&times;</button>
  </div>

  <div class="nav-links">
    <div class="nav-section">Menu</div>
    <a href="dashboard.php" class="<?= nav_active('dashboard.php', $__cur) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
      <span>Dashboard</span>
    </a>
    <a href="pdfupflow.php" class="<?= nav_active('pdfupflow.php', $__cur) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
      <span>Upload Document</span>
    </a>
    <a href="analysis.php" class="<?= nav_active('analysis.php', $__cur) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="11" width="3" height="6"/><rect x="12" y="7" width="3" height="10"/><rect x="17" y="13" width="3" height="4"/></svg>
      <span>Analytics</span>
    </a>

    <div class="nav-section">Account</div>
    <a href="pf.php" class="<?= nav_active('pf.php', $__cur) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
      <span>Profile</span>
    </a>
    <a href="reindex.php" class="<?= nav_active('reindex.php', $__cur) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
      <span>Re-index Docs</span>
    </a>
  </div>

  <div class="nav-footer">
    <a href="logout.php" class="nav-logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
      <span>Logout</span>
    </a>
  </div>
</nav>

<!-- Topbar -->
<header class="sg-header">
  <button class="ham-btn" onclick="openNav()" aria-label="Open menu">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
  </button>
  <div class="topbar-title" id="navRight"></div>
  <div class="topbar-user">
    <span class="tb-name"><?= htmlspecialchars($__uname) ?></span>
    <span class="tb-avatar"><?= strtoupper(substr($__uname, 0, 1)) ?></span>
  </div>
</header>

<div id="overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const t = document.getElementById('navRight');
  if (t) t.textContent = document.title.replace(/StudyGenie\s*[–-]\s*/i, '').trim();
});
</script>
