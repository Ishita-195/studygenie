<?php
session_unset();
session_destroy();
header("Location: authentication.php");
exit();
?>
