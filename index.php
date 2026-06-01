<?php
session_start();
error_log("[AUTH_PAGE_REQUESTED] Root index.php accessed");

if (isset($_SESSION["user_name"])) {
    error_log("[REDIRECT_TO_DASHBOARD] User already logged in, sending to dashboard");
    header("Location: dashboard.php");
    exit();
} else {
    error_log("[REDIRECT_TO_AUTH] User not logged in, sending to authentication page");
    header("Location: authentication.php");
    exit();
}
?>
