<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reviewer_id = $_SESSION['user_id'] ?? null;
    $reviewer_role = $_SESSION['role'] ?? null;
    $reviewee_email = $_POST['reviewee_email'] ?? null;
    $review_text = trim($_POST['review_text'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);

    // Basic validation
    if (!$reviewer_id || !$reviewer_role || !$reviewee_email || !$review_text || $rating < 1 || $rating > 5) {
        die("Invalid input. Please fill out all fields.");
    }

    // Fetch reviewee's user ID and role using email
    $stmt = $conn->prepare("SELECT user_id, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $reviewee_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No user found with the provided email.");
    }

    $user = $result->fetch_assoc();
    $reviewee_id = $user['user_id'];
    $reviewee_role = $user['role'];

    $stmt->close();

    // Prevent self-reviews
    if ($reviewer_id == $reviewee_id) {
        die("<h1>Review Failed</h1><p>You cannot submit a review for yourself.</p>");
    }

    // Insert review into database
    $insert = $conn->prepare("INSERT INTO reviews (reviewer_user_id, reviewer_role, reviewee_user_id, reviewee_role, review_text, rating, created_at)
                              VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $insert->bind_param("isissi", $reviewer_id, $reviewer_role, $reviewee_id, $reviewee_role, $review_text, $rating);

    if ($insert->execute()) {
        $insert->close();
        $conn->close();
        header("Location: /volunteer/dashboard.php?review=success");
        exit();
    } else {
        echo "Error: " . $insert->error;
    }

    $insert->close();
    $conn->close();
}
?>
