<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complainant_user_id = $_SESSION['user_id'];
    $complainant_role = $_SESSION['role'];
    $accused_email = trim($_POST['accused_email']);
    $complaint_text = trim($_POST['complaint_text']);

    // Find the accused user by email
    $stmt = $conn->prepare("SELECT user_id, role, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $accused_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // User not found
        header("Location: /volunteer/submit_complaint.php?error=notfound");
        exit();
    }
    
    $accused_user = $result->fetch_assoc();
    $accused_user_id = $accused_user['user_id'];
    $accused_role = $accused_user['role'];

    // Prevent users from complaining about themselves
    if ($complainant_user_id == $accused_user_id) {
        header("Location: /volunteer/submit_complaint.php?error=self");
        exit();
    }
    
    // Insert the complaint into the database
    $insert_stmt = $conn->prepare(
        "INSERT INTO complaints (complainant_user_id, complainant_role, accused_user_id, accused_role, complaint_text, complaint_date) VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $insert_stmt->bind_param("isiss", $complainant_user_id, $complainant_role, $accused_user_id, $accused_role, $complaint_text);

    if ($insert_stmt->execute()) {
        // Success
        header("Location: /volunteer/submit_complaint.php?status=success");
    } else {
        // Database error
        header("Location: /volunteer/submit_complaint.php?error=dberror");
    }

    $stmt->close();
    $insert_stmt->close();
    $conn->close();
    exit();

} else {
    // Redirect if not a POST request
    header("Location: /volunteer/submit_complaint.php");
    exit();
}
?>