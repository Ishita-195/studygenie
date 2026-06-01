<?php
session_start();
error_log("[LOGOUT_REQUESTED] User logging out");
session_unset();
session_destroy();
error_log("[REDIRECT_TO_AUTH] Logout complete, redirecting to login page");
header("Location: authentication.php");
exit();
?>
