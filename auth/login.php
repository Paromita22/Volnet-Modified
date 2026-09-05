<?php
// Enable error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start a session to manage user login state
session_start();

// Include DB connection
include __DIR__ . '/../includes/db.php'; // Adjust path if necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['user_type']; // Get the role from the form
    

    // Prepare a statement to fetch user data based on email and role
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_type'] = $user['role'];

            // Fetch role-specific IDs into session
            if ($user['role'] === 'volunteer') {
                $stmt_v = $conn->prepare("SELECT volunteer_id, name FROM volunteer WHERE user_id = ?");
                $stmt_v->bind_param("i", $user['user_id']);
                $stmt_v->execute();
                $res_v = $stmt_v->get_result();
                if ($row_v = $res_v->fetch_assoc()) {
                    $_SESSION['volunteer_id'] = $row_v['volunteer_id'];
                    $_SESSION['name'] = $row_v['name'];
                }
                $stmt_v->close();
            } elseif ($user['role'] === 'organization') {
                $stmt_o = $conn->prepare("SELECT org_id, org_name FROM organization WHERE user_id = ?");
                $stmt_o->bind_param("i", $user['user_id']);
                $stmt_o->execute();
                $res_o = $stmt_o->get_result();
                if ($row_o = $res_o->fetch_assoc()) {
                    $_SESSION['org_id'] = $row_o['org_id'];
                    $_SESSION['org_name'] = $row_o['org_name'];
                }
                $stmt_o->close();
            } elseif ($user['role'] === 'admin') {
                $stmt_a = $conn->prepare("SELECT admin_id, full_name FROM admin WHERE user_id = ?");
                $stmt_a->bind_param("i", $user['user_id']);
                $stmt_a->execute();
                $res_a = $stmt_a->get_result();
                if ($row_a = $res_a->fetch_assoc()) {
                    $_SESSION['admin_id'] = $row_a['admin_id'];
                    $_SESSION['full_name'] = $row_a['full_name'];
                }
                $stmt_a->close();
            }

            // Redirect based on user role to the specified homepages
            switch ($user['role']) {
                case 'volunteer':
                    header("Location: /volunteer/dashboard.php");
                    break;
                case 'organization':
                    header("Location: /organization/dashboard.php");
                    break;
                case 'admin':
                    header("Location: /admin/dashboard.php");
                    break;
                default:
                    // Fallback if role is not recognized or not specifically handled
                    header("Location: /pages/homepage.php?login=success"); // Your normal homepage
            }
            exit(); // Important: Always exit after a header redirect
        } else {
            // Invalid password
            header("Location: /auth/login.html?error=invalid_password");
            exit();
        }
    } else {
        // No user found with that email and role combination
        header("Location: /auth/login.html?error=user_not_found");
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // If accessed directly without POST request
    header("Location: /auth/login.html");
    exit();
}
?>




