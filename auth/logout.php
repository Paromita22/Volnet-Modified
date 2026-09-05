<?php
// Always start the session first
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the public homepage or login page
header("Location: /pages/homepage.php"); // or homepage.html
exit;
?>
