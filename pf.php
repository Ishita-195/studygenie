<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Profile</title>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 680px; margin: auto; }

.avatar {
  width: 78px; height: 78px; border-radius: 50%;
  background: linear-gradient(135deg, #1b5e20, #43a047);
  display: flex; align-items: center; justify-content: center;
  font-size: 32px; font-weight: 900; color: #fff;
  box-shadow: 0 8px 24px rgba(46,125,50,.35);
  flex-shrink: 0;
}

.profile-header {
  display: flex; align-items: center; gap: 20px; margin-bottom: 24px;
}
.profile-info h3 { font-size: 20px; font-weight: 800; color: var(--g2); }
.profile-info p  { font-size: 14px; color: var(--text-muted); margin-top: 3px; }

.info-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 0; border-bottom: 1px solid rgba(0,0,0,.05);
  font-size: 15px;
}
.info-row:last-child { border-bottom: none; }
.info-label { font-weight: 700; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
.info-value { color: var(--text); font-weight: 600; }

.setting-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 0; border-bottom: 1px solid rgba(0,0,0,.05);
}
.setting-row:last-child { border-bottom: none; }
.setting-label { font-size: 15px; font-weight: 600; }
.setting-desc  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* Toggle switch */
.switch { position: relative; width: 52px; height: 28px; flex-shrink: 0; }
.switch input { display: none; }
.slider {
  position: absolute; inset: 0; border-radius: 28px;
  background: rgba(0,0,0,.15); cursor: pointer; transition: .3s;
}
.slider:before {
  content: ''; position: absolute;
  width: 22px; height: 22px;
  background: #fff; border-radius: 50%;
  top: 3px; left: 3px; transition: .3s;
  box-shadow: 0 2px 6px rgba(0,0,0,.2);
}
input:checked + .slider { background: #4caf50; }
input:checked + .slider:before { transform: translateX(24px); }

.logout-link {
  display: flex; align-items: center; gap: 10px;
  padding: 13px 16px; border-radius: 12px;
  background: rgba(244,67,54,.07); border: 1.5px solid rgba(244,67,54,.15);
  color: #c62828; font-weight: 700; font-size: 15px;
  text-decoration: none; transition: .2s;
}
.logout-link:hover { background: rgba(244,67,54,.13); transform: translateX(4px); }

.section-gap { margin-bottom: 16px; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <!-- Profile card -->
  <div class="sg-card section-gap">
    <div class="profile-header">
      <div class="avatar"><?= strtoupper(substr($_SESSION["user_name"], 0, 1)) ?></div>
      <div class="profile-info">
        <h3><?= htmlspecialchars($_SESSION["user_name"]) ?></h3>
        <p>Student · StudyGenie</p>
      </div>
    </div>

    <div class="info-row">
      <span class="info-label">Email</span>
      <span class="info-value"><?= htmlspecialchars($_SESSION["user_email"]) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Member Since</span>
      <span class="info-value"><?= date("d M Y", strtotime($_SESSION["user_date"])) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Role</span>
      <span class="info-value"><span class="badge badge-green">Student</span></span>
    </div>
  </div>

  <!-- Preferences -->
  <div class="sg-card section-gap">
    <h2 class="section-title">Preferences</h2>

    <div class="setting-row">
      <div>
        <div class="setting-label">🌙 Dark Theme</div>
        <div class="setting-desc">Switch between light and dark mode</div>
      </div>
      <label class="switch">
        <input type="checkbox" id="themeToggle">
        <span class="slider"></span>
      </label>
    </div>

    <div class="setting-row">
      <div>
        <div class="setting-label">⚡ Typing Animation</div>
        <div class="setting-desc">Animate AI answers character-by-character</div>
      </div>
      <label class="switch">
        <input type="checkbox" id="animToggle" checked>
        <span class="slider"></span>
      </label>
    </div>
  </div>

  <!-- Logout -->
  <div class="sg-card-flat">
    <a href="logout.php" class="logout-link">🚪 Sign Out</a>
  </div>

</div>

<script>
// Theme toggle
const themeToggle = document.getElementById("themeToggle");
if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark");
  themeToggle.checked = true;
}
themeToggle.addEventListener("change", () => {
  document.body.classList.toggle("dark", themeToggle.checked);
  localStorage.setItem("theme", themeToggle.checked ? "dark" : "light");
  showToast(themeToggle.checked ? "Dark mode on" : "Light mode on", "info");
});

// Anim toggle
const animToggle = document.getElementById("animToggle");
animToggle.checked = localStorage.getItem("typingAnim") !== "off";
animToggle.addEventListener("change", () => {
  localStorage.setItem("typingAnim", animToggle.checked ? "on" : "off");
  showToast("Preference saved.", "success");
});
</script>
</body>
</html>
