<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie - Authentication</title>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', sans-serif;
    }

    body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #7fc8a9, #4caf50);
    }

    .container {
        width: 380px;
        background: #ffffff;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        position: relative;
    }

    .brand {
        text-align: center;
        margin-bottom: 15px;
    }

    .brand h1 {
        font-size: 28px;
        color:#2e7d32;
    }

    .brand span {
        color: #4caf50;
        font-weight: bold;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #4caf50;
    }

    .input-group {
        margin-bottom: 15px;
    }

    .input-group label {
        font-size: 14px;
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    .input-group input,
    .input-group select {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid #ccc;
        outline: none;
        transition: 0.3s;
    }

    .input-group input:focus,
    .input-group select:focus {
        border-color: #3a7d6d;
    }

    .btn {
        width: 100%;
        padding: 12px;
        background: #4caf50;
        border: none;
        border-radius: 10px;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn:hover {
        background: #4caf50;
    }

    .link {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }

    .link a {
        color: #3a7d6d;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
    }

    .error {
        background: #ffe0e0;
        color: #c0392b;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: none;
        font-size: 14px;
    }

    .success {
        text-align: center;
        padding: 20px;
        display: none;
    }

    .success h3 {
        color: #2ecc71;
        margin-bottom: 10px;
    }

    .loader {
        display: none;
        text-align: center;
        margin-top: 10px;
    }

    .spinner {
        border: 4px solid #eee;
        border-top: 4px solid #3a7d6d;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        100% { transform: rotate(360deg); }
    }

    .hidden {
        display: none;
    }
</style>
</head>

<body>

<div class="container">

    <!-- Branding -->
    <div class="brand">
        <h1>Study<span>Genie</span></h1>
    </div>

    <?php 
        $showSuccess = false;
        if(isset($_GET['success'])){
            $showSuccess = true;
        }
    ?>

<!-- LOGIN FORM -->
<div id="loginForm">
        <h2>Login</h2>

        <div class="error" id="loginError">Wrong email or password!</div>

        <div class="input-group">
            <form action="" method="post">
                <label>Email</label>
                <input type="email" id="loginEmail" placeholder="Enter your email" name="email">
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" id="loginPassword" placeholder="Enter your password" name="pwd">
        </div>

        <input type="submit" class="btn" name="login_check" value="Login">
        </form>

        <div class="loader" id="loginLoader">
            <div class="spinner"></div>
        </div>

        <div class="link">
            Don't have an account? 
            <a onclick="showSignup()">Create one</a>
        </div>
</div>

<!-- SIGNUP FORM -->
<div id="signupForm" class="hidden">

        <h2>Sign Up</h2>

        <div class="error" id="signupError">Passwords do not match!</div>

        <form method="post">

            <div class="input-group">
                <label>Name</label>
                <input type="text" placeholder="Enter your name" name="name">
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" placeholder="Enter your email" name="email">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" placeholder="Enter password" name="pwd">
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" placeholder="Confirm password" name="con_pwd">
            </div>

            <div class="input-group">
                <label>Role</label>
                <select name="role">
                    <option value="Student">Student</option>
                    <option value="Faculty">Faculty</option>
                </select>
            </div>

            <input type="submit" class="btn" name="reg_check" value="Create Account">
        </form>

        <div class="loader" id="signupLoader">
            <div class="spinner"></div>
        </div>

        <div class="link">
            Already have an account? 
            <a onclick="showLogin()">Login</a>
        </div>

</div>

<?php

if(isset($_POST['login_check'])){

    include 'config.php';

    $email = $_POST['email'];
    $pwd = $_POST['pwd'];

    $sql = "SELECT * from users where email='$email' and password='$pwd'";
    $result = $con->query($sql);

    if($result->num_rows>0){

        $row = $result->fetch_assoc();

        $_SESSION["user_name"] = $row["name"];
        $_SESSION["user_email"] = $row["email"];
        $_SESSION["user_date"] = $row["date_joined"];

        echo "<script>window.location='dashboard.php';</script>";
    }

}

if(isset($_POST['reg_check'])){

    include 'config.php';

    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $con_pwd = $_POST['con_pwd'];
    $name = $_POST['name'];
    $role = $_POST['role'];

    if($pwd==$con_pwd){

        $sql="INSERT INTO users(name,email,password,role)
        VALUES('$name','$email','$pwd','$role')";

        if($con->query($sql)){

            $showSuccess=true;
        }

    } else {

        echo "<script>alert('Passwords do not match!');</script>";

    }

}

?>

<!-- SUCCESS SCREEN -->
<div class="success" id="successScreen"
style="display: <?php echo ($showSuccess) ? 'block' : 'none'; ?>;">

<h3>🎉 Welcome to StudyGenie!</h3>
<p>Your account has been created successfully.</p>

<button class="btn" onclick="showLogin()">Go to Login</button>

</div>

</div>

<script>

function showSignup(){
document.getElementById("loginForm").classList.add("hidden");
document.getElementById("signupForm").classList.remove("hidden");
}

function showLogin(){
document.getElementById("signupForm").classList.add("hidden");
document.getElementById("successScreen").style.display="none";
document.getElementById("loginForm").classList.remove("hidden");
}

</script>

</body>
</html>