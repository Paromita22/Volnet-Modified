<?php
session_start();

// If user is already logged in, redirect to their role dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'volunteer':
            header("Location: volunteer/dashboard.php");
            exit();
        case 'organization':
            header("Location: organization/dashboard.php");
            exit();
        case 'admin':
            header("Location: admin/dashboard.php");
            exit();
    }
}

// Otherwise go to public homepage
header("Location: pages/homepage.php");
exit();
