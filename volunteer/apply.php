<?php
session_start();
// Use a dedicated db connection file for consistency
require_once __DIR__ . '/../includes/db.php';

// 1. Security & Validation
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id'])) {
    header("Location: /pages/view_events.php?error=invalid_request");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = (int)$_POST['event_id'];

// 2. Get the volunteer_id from the user_id
$stmt_vol = $conn->prepare("SELECT volunteer_id FROM volunteer WHERE user_id = ?");
$stmt_vol->bind_param("i", $user_id);
$stmt_vol->execute();
$result_vol = $stmt_vol->get_result();
if ($result_vol->num_rows === 0) {
    // This volunteer doesn't have a profile in the 'volunteer' table.
    // Redirect them to set up their account first.
    header("Location: /volunteer/edit_profile.php?error=profile_incomplete");
    exit();
}
$volunteer = $result_vol->fetch_assoc();
$volunteer_id = $volunteer['volunteer_id'];
$stmt_vol->close();

// 3. Get the org_id and deadline from the event_id
$stmt_org = $conn->prepare("SELECT org_id, deadline, end_date FROM eventt WHERE id = ?");
$stmt_org->bind_param("i", $event_id);
$stmt_org->execute();
$result_org = $stmt_org->get_result();
if ($result_org->num_rows === 0) {
    header("Location: /pages/view_events.php?error=event_not_found");
    exit();
}
$event = $result_org->fetch_assoc();
$org_id = $event['org_id'];
$stmt_org->close();

if (!$org_id) {
    echo "event_not_linked_to_org";
    exit();
}

// Check deadline
if (!empty($event['deadline']) && strtotime($event['deadline']) < strtotime(date('Y-m-d'))) {
    echo "deadline_passed";
    exit();
}


// 4. Insert the application into the database
$stmt_app = $conn->prepare("INSERT INTO applications (event_id, volunteer_id, org_id, status) VALUES (?, ?, ?, 'Pending')");
$stmt_app->bind_param("iii", $event_id, $volunteer_id, $org_id);

if ($stmt_app->execute()) {
    // Success! Redirect back to the event list.
    // The previous page URL can be passed in a hidden field for more dynamic redirection.
    echo "applied_successfully";
    exit;
    
} else {
    // Failed. Likely because they already applied (due to the UNIQUE KEY).
    // It's good practice to handle this gracefully.
    echo "already_applied";
exit;

}

$stmt_app->close();
$conn->close();
?>
