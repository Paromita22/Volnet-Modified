<?php
// Set header to return JSON
header('Content-Type: application/json');

// Include DB connection
include __DIR__ . '/../includes/db.php'; // Make sure this path is correct

$response = [
    'emailExists' => false,
    'mobileExists' => false
];

// Check if email and mobile were sent
if (isset($_POST['email']) && isset($_POST['mobile'])) {
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];

    // 1. Check for duplicate email in the 'users' table
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $response['emailExists'] = true;
        }
        $stmt->close();
    }

    // 2. Check for duplicate mobile number across all relevant tables
    if (!$response['emailExists']) { // No need to check mobile if email already failed
        $tables = ['volunteer', 'organization', 'admin'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("SELECT user_id FROM $table WHERE mobile = ?");
            if ($stmt) {
                $stmt->bind_param("s", $mobile);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $response['mobileExists'] = true;
                    $stmt->close();
                    break; // Found one, no need to check other tables
                }
                $stmt->close();
            }
        }
    }

    $conn->close();
}

// Return the response as JSON
echo json_encode($response);
exit();
?>