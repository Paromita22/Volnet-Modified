<?php
session_start();
// Security Check: Only logged-in organizations can do this.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'organization') {
    http_response_code(403);
    die("Forbidden");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['app_id']) || !isset($_POST['status'])) {
    http_response_code(400);
    die("Invalid Request");
}

require_once __DIR__ . '/../includes/db.php';

$application_id = (int)$_POST['app_id'];
$new_status = $_POST['status'];

// Validate status
if (!in_array($new_status, ['Accepted', 'Rejected'])) {
    die("Invalid status value.");
}

$user_id = $_SESSION['user_id'];
$org_id = $_SESSION['org_id'] ?? null;
if (!$org_id) {
    $stmt_o = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
    $stmt_o->bind_param("i", $user_id);
    $stmt_o->execute();
    $r = $stmt_o->get_result()->fetch_assoc();
    if ($r) {
        $org_id = $r['org_id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt_o->close();
}

$stmt = $conn->prepare("UPDATE applications SET status = ? WHERE application_id = ? AND org_id = ?");
$stmt->bind_param("sii", $new_status, $application_id, $org_id);

if ($stmt->execute()) {
    header("Location: /organization/view_applicants.php?status=updated");
} else {
    header("Location: /organization/view_applicants.php?error=update_failed");
}

$stmt->close();
$conn->close();
?>