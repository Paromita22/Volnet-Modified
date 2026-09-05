<?php
session_start();

// Security Checks
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /organization/manage_events.php");
    exit();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

include __DIR__ . '/../includes/db.php';

// Validate and retrieve POST data
$volunteer_id = filter_input(INPUT_POST, 'volunteer_id', FILTER_VALIDATE_INT);
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$hours_completed = filter_input(INPUT_POST, 'hours_completed', FILTER_VALIDATE_FLOAT);

if (!$volunteer_id || !$event_id || $hours_completed === false) {
    header("Location: /organization/mark_attendance.php?event_id=$event_id&status=error");
    exit();
}

// Final check: Does the logged-in org own this event?
// ==================== THE FIX: Also select the event's end_date ====================
$org_id = $_SESSION['org_id'];
$stmt_verify = $conn->prepare("SELECT id, end_date FROM eventt WHERE id = ? AND org_id = ?");
$stmt_verify->bind_param("ii", $event_id, $org_id);
$stmt_verify->execute();
$result_verify = $stmt_verify->get_result();

if ($result_verify->num_rows == 0) {
    header("Location: /organization/manage_events.php?error=accessdenied");
    exit();
}

// ==================== THE FIX: Get the end_date from the query result ====================
$event_data = $result_verify->fetch_assoc();
$event_end_date = $event_data['end_date']; // <-- NEW: Store the event's end date
$stmt_verify->close();


// ==================== THE FIX: Insert the record with the correct attendance_date ====================
$sql = "INSERT INTO volunteer_events (volunteer_id, event_id, status, hours_completed, attendance_date) VALUES (?, ?, 'Attended', ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind all four parameters: volunteer_id, event_id, hours_completed, and the new attendance_date
    $stmt->bind_param("iids", $volunteer_id, $event_id, $hours_completed, $event_end_date);
    
    if ($stmt->execute()) {
        // Success
        header("Location: /organization/mark_attendance.php?event_id=$event_id&status=success");
    } else {
        // Failure
        // You might get an error here if you try to mark the same person twice. The page logic prevents this.
        header("Location: /organization/mark_attendance.php?event_id=$event_id&status=error");
    }
    $stmt->close();
} else {
    // SQL preparation failed
    header("Location: /organization/mark_attendance.php?event_id=$event_id&status=error");
}

$conn->close();
exit();
?>