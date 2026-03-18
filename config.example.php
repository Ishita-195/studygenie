<?php
/**
 * Database Configuration
 * 
 * Copy this file to config.php and update with your credentials.
 * Do NOT commit config.php to version control.
 */

$server = "localhost";
$user = "root";
$password = "";  // Set your MySQL password if any
$db = "studygenie_db";

$con = new mysqli($server, $user, $password, $db);

if($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
