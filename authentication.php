<?php
session_start();
if (isset($_SESSION["user_name"])) { header("Location: dashboard.php"); exit(); }
require_once 'config.php';

$error = ""; $success = ""; $tab = "login";

if (isset($_POST["register_check"])) {
    $tab   = "register";
    $name  = trim($_POST["reg_name"]  ?? "");
    $email = trim($_POST["reg_email"] ?? "");
    $pwd   = $_POST["reg_pwd"]  ?? "";
    $cpwd  = $_POST["reg_cpwd"] ?? "";
    if (!$name || !$email || !$pwd || !$cpwd)                    { $error = "All fields are required."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))          { $error = "Invalid email address."; }
    elseif (strlen($pwd) < 6)                                    { $error = "Password must be at least 6 characters."; }
    elseif ($pwd !== $cpwd)                                      { $error = "Passwords do not match."; }
    else {
        $chk = $con->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { $error = "Email already registered."; }
        else {
            $hash = password_hash($pwd, PASSWORD_BCRYPT);
            $ins  = $con->prepare("INSERT INTO users (name, email, password, role, date_joined) VALUES (?, ?, ?, 'student', NOW())");
            $ins->bind_param("sss", $name, $email, $hash);
            if ($ins->execute()) { $success = "Account created! You can now sign in."; $tab = "login"; }
            else                 { $error = "Registration failed. Please try again."; }
        }
    }
}

if (isset($_POST["login_check"])) {
    $email = trim($_POST["email"] ?? "");
    $pwd   = $_POST["pwd"] ?? "";
    if (!$email || !$pwd) { $error = "Email and password are required."; }
    else {
        $stmt = $con->prepare("SELECT id, name, email, password, date_joined FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $stored = $row["password"]; $valid = false;
            if (password_verify($pwd, $stored))   { $valid = true; }
            elseif ($pwd === $stored) {
                $hash = password_hash($pwd, PASSWORD_BCRYPT);
                $upd  = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $hash, $row["id"]); $upd->execute();
                $valid = true;
            }
            if ($valid) {
                $_SESSION["user_name"]  = $row["name"];
                $_SESSION["user_email"] = $row["email"];
                $_SESSION["user_date"]  = $row["date_joined"];
                header("Location: dashboard.php"); exit();
            } else { $error = "Incorrect email or password."; }
        } else { $error = "Incorrect email or password."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Sign In</title>
<?php include 'theme.php'; ?>
<style>
body { display: flex; align-items: center; justify-content: center; padding: 20px; }

.auth-wrap {
  width: 100%;
  max-width: 440px;
  animation: fadeIn .5s ease both;
}

.auth-brand {
  text-align: center;
  margin-bottom: 28px;
}
.auth-brand h1 {
  font-size: 42px;
  font-weight: 900;
  background: linear-gradient(135deg, #4caf50, #00e676, #69f0ae);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -1.5px;
}
.auth-brand p { color: rgba(255,255,255,.55); font-size: 14px; margin-top: 6px; }

.auth-card {
  background: rgba(255,255,255,.1);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255,255,255,.2);
  border-radius: 24px;
  box-shadow: 0 24px 64px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.15);
  overflow: hidden;
}

.tabs {
  display: flex;
  border-bottom: 1px solid rgba(255,255,255,.12);
}
.tab-btn {
  flex: 1; padding: 16px; border: none; background: none;
  font-family: inherit; font-size: 15px; font-weight: 700;
  color: rgba(255,255,255,.45); cursor: pointer; transition: .2s;
}
.tab-btn.active {
  color: #fff;
  background: rgba(76,175,80,.12);
  border-bottom: 2px solid #4caf50;
}

.form-body { padding: 28px; }
.form-panel { display: none; }
.form-panel.active { display: block; }

.field { margin-bottom: 16px; }
.field label {
  display: block; font-size: 12px; font-weight: 700;
  color: rgba(255,255,255,.6); margin-bottom: 7px;
  text-transform: uppercase; letter-spacing: .6px;
}
.field input {
  width: 100%; padding: 13px 16px;
  background: rgba(255,255,255,.1);
  border: 1.5px solid rgba(255,255,255,.15);
  border-radius: 12px;
  font-family: inherit; font-size: 15px; color: #fff;
  outline: none; transition: .2s;
}
.field input::placeholder { color: rgba(255,255,255,.35); }
.field input:focus {
  border-color: rgba(76,175,80,.6);
  background: rgba(255,255,255,.14);
  box-shadow: 0 0 0 3px rgba(76,175,80,.15);
}

.sg-btn { width: 100%; padding: 14px; font-size: 16px; border-radius: 14px; margin-top: 4px; }
.sg-btn-primary { box-shadow: 0 6px 24px rgba(46,125,50,.45); }

.alert {
  padding: 12px 16px; border-radius: 12px;
  font-size: 14px; font-weight: 600; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.alert-error   { background: rgba(229,57,53,.15); color: #ff8a80; border: 1px solid rgba(229,57,53,.25); }
.alert-success { background: rgba(76,175,80,.15);  color: #a5d6a7; border: 1px solid rgba(76,175,80,.25); }

.auth-footer {
  text-align: center; padding: 14px;
  font-size: 12px; color: rgba(255,255,255,.25);
  border-top: 1px solid rgba(255,255,255,.07);
}
</style>
</head>
<body>
<div class="page-wrapper" style="padding:0;width:100%;display:flex;align-items:center;justify-content:center;min-height:100vh;">
<div class="auth-wrap">

  <div class="auth-brand">
    <h1>StudyGenie</h1>
    <p>AI-Powered University Learning Platform</p>
  </div>

  <div class="auth-card">
    <div class="tabs">
      <button class="tab-btn <?= $tab==='login'?'active':'' ?>" onclick="switchTab('login')">Sign In</button>
      <button class="tab-btn <?= $tab==='register'?'active':'' ?>" onclick="switchTab('register')">Create Account</button>
    </div>

    <div class="form-body">
      <?php if ($error): ?>
        <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- LOGIN -->
      <div class="form-panel <?= $tab==='login'?'active':'' ?>" id="panel-login">
        <form method="POST">
          <div class="field">
            <label>Email</label>
            <input type="email" name="email" placeholder="you@university.edu"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Password</label>
            <input type="password" name="pwd" placeholder="••••••••" required>
          </div>
          <button type="submit" name="login_check" class="sg-btn sg-btn-primary">Sign In →</button>
        </form>
      </div>

      <!-- REGISTER -->
      <div class="form-panel <?= $tab==='register'?'active':'' ?>" id="panel-register">
        <form method="POST">
          <div class="field">
            <label>Full Name</label>
            <input type="text" name="reg_name" placeholder="Your full name"
                   value="<?= htmlspecialchars($_POST['reg_name'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Email</label>
            <input type="email" name="reg_email" placeholder="you@university.edu"
                   value="<?= htmlspecialchars($_POST['reg_email'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Password</label>
            <input type="password" name="reg_pwd" placeholder="Min. 6 characters" required>
          </div>
          <div class="field">
            <label>Confirm Password</label>
            <input type="password" name="reg_cpwd" placeholder="Repeat password" required>
          </div>
          <button type="submit" name="register_check" class="sg-btn sg-btn-primary">Create Account →</button>
        </form>
      </div>
    </div>

    <div class="auth-footer">StudyGenie &copy; <?= date('Y') ?> · All rights reserved</div>
  </div>
</div>
</div>
<script>
function switchTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
  document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
  document.getElementById('panel-' + tab).classList.add('active');
}
</script>
</body>
</html>
