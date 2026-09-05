<?php
// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include DB connection
include __DIR__ . '/../includes/db.php'; // Ensure db.php is in the correct path

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect data from the form
    $role = $_POST['user_type'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate Date of Birth and Minimum Age (Must be at least 13 years old)
    $dobDate = date_create($dob);
    $todayDate = date_create('today');
    if (!$dobDate || $dobDate >= $todayDate) {
        die("<h1>Registration Failed</h1><p>Invalid Date of Birth. Date of birth cannot be today or in the future.</p>");
    }
    $calcAge = date_diff($dobDate, $todayDate)->y;
    if ($calcAge < 13 || $calcAge > 100) {
        die("<h1>Registration Failed</h1><p>Invalid Date of Birth. You must be at least 13 years old to register.</p>");
    }

    // ================== START: SERVER-SIDE DUPLICATE CHECK ==================
    // 1. Check for duplicate email in the 'users' table
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        $conn->close();
        // Stop the script and show a user-friendly error.
        die("<h1>Registration Failed</h1><p>The email address '<strong>" . htmlspecialchars($email) . "</strong>' is already registered. Please use your browser's back button and try a different email address.</p>");
    }
    $stmt->close();

    // 2. Check for duplicate mobile number across all relevant tables
    $mobile_exists = false;
    $tables = ['volunteer', 'organization', 'admin'];
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT user_id FROM $table WHERE mobile = ?");
        if ($stmt) {
            $stmt->bind_param("s", $mobile);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $mobile_exists = true;
                $stmt->close();
                break; // Found one, no need to check other tables
            }
            $stmt->close();
        }
    }
    
    if ($mobile_exists) {
        $conn->close();
        // Stop the script and show a user-friendly error.
        die("<h1>Registration Failed</h1><p>The mobile number '<strong>" . htmlspecialchars($mobile) . "</strong>' is already registered. Please use your browser's back button and try a different mobile number.</p>");
    }
    // =================== END: SERVER-SIDE DUPLICATE CHECK ===================

    // If we get here, the email and mobile are not duplicates. Proceed with insertion.

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  
    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
    if (!$stmt->execute()) {
        die("Error inserting into users table: " . $stmt->error);
    }

    // Get the inserted user_id
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Insert into role-specific table
    if ($role == "volunteer") {
        $stmt = $conn->prepare("INSERT INTO volunteer (user_id, name, age, skills, gender, mobile, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $name = $firstName . " " . $lastName;
        $age = date_diff(date_create($dob), date_create('today'))->y;
        $skills = "";
        $stmt->bind_param("issssss", $user_id, $name, $age, $skills, $gender, $mobile, $address);

    } elseif ($role == "organization") {
        $stmt = $conn->prepare("INSERT INTO organization (user_id, org_name, contact_email, description, mobile, address) VALUES (?, ?, ?, ?, ?, ?)");
        $org_name = $firstName . " " . $lastName;
        $desc = $address;
        $stmt->bind_param("isssss", $user_id, $org_name, $email, $desc, $mobile, $address);
    } elseif ($role == "admin") {
        $stmt = $conn->prepare("INSERT INTO admin (user_id, full_name, privileges, mobile) VALUES (?, ?, ?, ?)");
        $full_name = $firstName . " " . $lastName;
        $privileges = "full";
        $stmt->bind_param("isss", $user_id, $full_name, $privileges, $mobile);
    }

    // Execute the role-specific insert
    if (isset($stmt) && $stmt->execute()) {
        // Redirect after successful registration
        header("Location: /auth/login.html?registration=success");
        exit();
    } else {
        echo "Error inserting into role-specific table: " . (isset($stmt) ? $stmt->error : "Statement not prepared for role.");
    }

    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();

} else {
    echo "Invalid request method.";
}
?>