<?php
session_start();
// Security Check: Ensure the user is logged in before they can submit a complaint.
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Complaint - VolNet</title>
    
    <!-- Styles for the page -->
    <style>
        body { 
            background-color: #000; 
            color: #fff; 
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container { 
            max-width: 800px; 
            margin: 40px auto; 
            padding: 30px; 
            background-color: #1a1a1a; 
            border-radius: 8px; 
            border: 1px solid #4b4847; 
        }
        h1 { 
            color: #d25858; /* Changed color to reflect 'complaint' theme */
            text-align: center; 
            margin-bottom: 30px; 
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: bold; 
        }
        input, textarea, button {
            width: 100%;
            padding: 12px;
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
            border-radius: 5px;
            box-sizing: border-box; 
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        button { 
            background-color: #d25858; 
            color: #000; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background-color 0.3s; 
            border: none;
        }
        button:hover { 
            background-color: #e06b6b; 
        }
        .alert { 
            padding: 15px; 
            background-color: #4b4847; 
            border-left: 5px solid #d25858; 
            margin-bottom: 20px; 
        }
        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #28a745;
            color: white;
        }
        .error {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submit a Complaint</h1>
        
        <p class="alert">If you've had a negative experience with a user, please detail it below. Our admin team will review your submission.</p>

        <?php
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo '<p class="message success">Complaint submitted successfully. Our team will review it shortly.</p>';
        }
        if (isset($_GET['error'])) {
            $errorMessage = "An unknown error occurred.";
            if ($_GET['error'] == 'notfound') {
                $errorMessage = "The user with the provided email was not found.";
            } elseif ($_GET['error'] == 'self') {
                 $errorMessage = "You cannot file a complaint against yourself.";
            }
            echo '<p class="message error">' . htmlspecialchars($errorMessage) . '</p>';
        }
        ?>

        <form action="/admin/process_complaint.php" method="POST">
            <div class="form-group">
                <label for="accused_email">Email of the User to Complain About:</label>
                <input type="email" id="accused_email" name="accused_email" required placeholder="e.g., user@example.com">
            </div>

            <div class="form-group">
                <label for="complaint_text">Details of the Complaint:</label>
                <textarea id="complaint_text" name="complaint_text" required placeholder="Please provide specific details about the incident, including dates, what happened, and why you are submitting this complaint."></textarea>
            </div>

            <button type="submit">Submit Complaint</button>
        </form>
    </div>
</body>
</html>