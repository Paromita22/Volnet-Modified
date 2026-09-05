<?php
session_start();
include __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$org_id = $_SESSION['org_id'] ?? null;

if (!$org_id) {
    $stmt_o = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
    $stmt_o->bind_param("i", $user_id);
    $stmt_o->execute();
    $res_o = $stmt_o->get_result();
    if ($row_o = $res_o->fetch_assoc()) {
        $org_id = $row_o['org_id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt_o->close();
}

if (!$org_id) {
    die("Organization record not found.");
}

// --- PART 1: HANDLE PROFILE & ADDRESS UPDATE ---
if (isset($_POST['update_profile'])) {
    // Get all the data from the form
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    $mobile = $_POST['mobile'];
    $contact_email = $_POST['contact_email'];

    // --- UPDATED QUERY ---
    // Update bio, address, mobile, and contact_email
    $stmt = $conn->prepare("UPDATE organization SET bio = ?, address = ?, mobile = ?, contact_email = ? WHERE org_id = ?");
    $stmt->bind_param("ssssi", $bio, $address, $mobile, $contact_email, $org_id);
    $stmt->execute();
    $stmt->close();

    // Handle Profile Picture Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $uploadResult = upload_file_safe($_FILES['profile_pic'], 'org_pics');
        if ($uploadResult['success']) {
            $webPath = '/' . $uploadResult['path'];
            $stmt_pic = $conn->prepare("UPDATE organization SET profile_pic_path = ? WHERE org_id = ?");
            $stmt_pic->bind_param("si", $webPath, $org_id);
            $stmt_pic->execute();
            $stmt_pic->close();
        }
    }
    
    header("Location: /organization/profile.php?success=profile_updated");
    exit();
}

// --- PART 2: HANDLE ADDING A NEW PAST EVENT ---
if (isset($_POST['add_event'])) {
    $event_title = $_POST['event_title'];
    $event_description = $_POST['event_description'];
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $uploadResult = upload_file_safe($_FILES['event_image'], 'past_events');
        if ($uploadResult['success']) {
            $webPath = '/' . $uploadResult['path'];
            $stmt = $conn->prepare("INSERT INTO org_past_events (org_id, title, description, image_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $org_id, $event_title, $event_description, $webPath);
            $stmt->execute();
            $stmt->close();
            header("Location: /organization/edit_profile.php?success=event_added");
            exit();
        }
    }
    header("Location: /organization/edit_profile.php?error=event_upload_failed");
    exit();
}

// --- PART 3: HANDLE DELETING A PAST EVENT (no changes needed) ---
if (isset($_GET['delete_event'])) {
    $event_id_to_delete = $_GET['delete_event'];
    $stmt_verify = $conn->prepare("SELECT image_path FROM org_past_events WHERE id = ? AND org_id = ?");
    $stmt_verify->bind_param("ii", $event_id_to_delete, $org_id);
    $stmt_verify->execute();
    $result = $stmt_verify->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
        $stmt_delete = $conn->prepare("DELETE FROM org_past_events WHERE id = ?");
        $stmt_delete->bind_param("i", $event_id_to_delete);
        $stmt_delete->execute();
        $stmt_delete->close();
        header("Location: /organization/edit_profile.php?success=event_deleted");
        exit();
    } else {
        header("Location: /organization/edit_profile.php?error=unauthorized_delete");
        exit();
    }
}

// Default redirect if no action is matched
header("Location: /organization/profile.php");
exit();
?>