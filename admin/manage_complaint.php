<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// Check if action and id are provided in the URL
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: /admin/dashboard.php?error=InvalidAction");
    exit();
}

$action = $_GET['action'];
$complaint_id = (int)$_GET['id']; // Cast to integer for security

// Database connection setup
require_once __DIR__ . '/../includes/db.php';

// Use a switch statement to handle different actions
switch ($action) {
    case 'update_status':
        if (!isset($_GET['status'])) {
            header("Location: /admin/dashboard.php?error=MissingStatus");
            exit();
        }
        $new_status = $_GET['status'];
        // Validate the new status to ensure it's one of the allowed values
        $allowed_statuses = ['Pending', 'In Review', 'Resolved', 'Dismissed'];
        if (!in_array($new_status, $allowed_statuses)) {
            header("Location: /admin/dashboard.php?error=InvalidStatus");
            exit();
        }

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?");
        $stmt->bind_param("si", $new_status, $complaint_id);
        $stmt->execute();
        $stmt->close();
        break;

    case 'delete':
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("DELETE FROM complaints WHERE complaint_id = ?");
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $stmt->close();
        break;

    default:
        // If the action is not recognized, do nothing and redirect
        header("Location: /admin/dashboard.php?error=UnknownAction");
        exit();
}

$conn->close();

// Redirect back to the admin homepage to see the changes
header("Location: /admin/dashboard.php?success=ActionCompleted");
exit();

?>