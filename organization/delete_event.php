<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'organization') {
    die("Unauthorized access.");
}

$org_id = $_SESSION['org_id'] ?? null;
if (!$org_id) {
    $stmt_o = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
    $stmt_o->bind_param("i", $_SESSION['user_id']);
    $stmt_o->execute();
    $r = $stmt_o->get_result()->fetch_assoc();
    if ($r) {
        $org_id = $r['org_id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt_o->close();
}

if (!$org_id) {
    die("Organization record not found.");
}
$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

if (!$event_id) {
    die("Invalid event ID.");
}

// Check if the event belongs to this organization and is upcoming
$stmt = $conn->prepare("SELECT start_date FROM eventt WHERE id = ? AND org_id = ?");
$stmt->bind_param("ii", $event_id, $org_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found or does not belong to you.");
}

$event = $result->fetch_assoc();
$today = date("Y-m-d");

if ($event['start_date'] <= $today) {
    die("Only upcoming events can be deleted.");
}

$stmt->close();

// Delete the event — related data will be removed due to ON DELETE CASCADE
$stmt_del = $conn->prepare("DELETE FROM eventt WHERE id = ? AND org_id = ?");
$stmt_del->bind_param("ii", $event_id, $org_id);
$stmt_del->execute();
$stmt_del->close();

$conn->close();
header("Location: /organization/history.php?deleted=1");
exit();
?>
