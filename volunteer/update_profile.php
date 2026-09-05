<?php
session_start();
include __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Security check: ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // Sanitize and retrieve POST data
    $name = trim($_POST['name']);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);
    $skills = trim($_POST['skills']);
    $bio = trim($_POST['bio']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // --- START: FILE UPLOAD LOGIC ---
    $profile_pic_path_to_db = null;

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = upload_file_safe($_FILES['profile_pic'], 'profiles');
        if ($uploadResult['success']) {
            $profile_pic_path_to_db = '/' . $uploadResult['path'];
        } else {
            header("Location: /volunteer/edit_profile.php?error=fileuploadfailed");
            exit();
        }
    }
    // --- END: FILE UPLOAD LOGIC ---


    // Update the 'users' table (for email) - this part is fine
    if ($email) {
        $stmt_user = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        $stmt_user->bind_param("si", $email, $user_id);
        if (!$stmt_user->execute()) {
            header("Location: /volunteer/edit_profile.php?error=emailupdatefailed");
            $stmt_user->close();
            $conn->close();
            exit();
        }
        $stmt_user->close();
    }
    
    // --- START: MODIFIED DATABASE UPDATE FOR 'volunteer' TABLE ---

    // Prepare the SQL query based on whether a new picture was uploaded
    if ($profile_pic_path_to_db) {
        // If a new picture was uploaded, update the profile_pic_path column
        $sql = "UPDATE volunteer SET name = ?, age = ?, mobile = ?, address = ?, skills = ?, bio = ?, profile_pic_path = ? WHERE user_id = ?";
        $stmt_volunteer = $conn->prepare($sql);
        $stmt_volunteer->bind_param("sisssssi", $name, $age, $mobile, $address, $skills, $bio, $profile_pic_path_to_db, $user_id);
    } else {
        // If no new picture, update everything else BUT the picture path
        $sql = "UPDATE volunteer SET name = ?, age = ?, mobile = ?, address = ?, skills = ?, bio = ? WHERE user_id = ?";
        $stmt_volunteer = $conn->prepare($sql);
        $stmt_volunteer->bind_param("sissssi", $name, $age, $mobile, $address, $skills, $bio, $user_id);
    }
    
    // Execute and redirect
    if ($stmt_volunteer->execute()) {
        header("Location: /volunteer/profile.php?success=profileupdated");
    } else {
        // Provide more specific error for debugging if you want
        // error_log("Volunteer update failed: " . $stmt_volunteer->error);
        header("Location: /volunteer/edit_profile.php?error=updatefailed");
    }
    
    $stmt_volunteer->close();
    $conn->close();

    // --- END: MODIFIED DATABASE UPDATE ---

} else {
    // If not a POST request, redirect to the account page
    header("Location: /volunteer/profile.php");
    exit();
}
?>