<?php
session_start();
// Security Check: Ensure the user is logged in before they can write a review.
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
    <title>Submit a Review - VolNet</title>
    
    <!-- These are the styles copied directly from request_certificate.php -->
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
            color: #9bd258; 
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
        /* Applied styles to input, textarea, and select for consistency */
        select, input, textarea, button {
            width: 100%;
            padding: 12px;
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
            border-radius: 5px;
            box-sizing: border-box; /* Important for consistent sizing */
        }
        textarea {
            resize: vertical; /* Allow users to resize the textarea vertically */
            min-height: 100px;
        }
        button { 
            background-color: #9bd258; 
            color: #000; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background-color 0.3s; 
            border: none; /* remove default button border */
        }
        button:hover { 
            background-color: #b0f068; 
        }
        .alert { 
            padding: 15px; 
            background-color: #4b4847; 
            border-left: 5px solid #9bd258; 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Write a Review</h1>
        
        <p class="alert">Share your experience with another user. Your feedback helps build a trustworthy community.</p>

        <!-- This form now submits to your original submit_review.php script -->
        <form action="/volunteer/submit_review.php" method="POST">
            <div class="form-group">
                <label for="reviewee_email">Email of the User to Review:</label>
                <input type="email" id="reviewee_email" name="reviewee_email" required placeholder="e.g., user@example.com">
            </div>

            <div class="form-group">
                <label for="rating">Rating (1 = Poor, 5 = Excellent):</label>
                <select id="rating" name="rating" required>
                    <option value="" disabled selected>Select a rating...</option>
                    <option value="5">★★★★★ (Excellent)</option>
                    <option value="4">★★★★☆ (Good)</option>
                    <option value="3">★★★☆☆ (Average)</option>
                    <option value="2">★★☆☆☆ (Fair)</option>
                    <option value="1">★☆☆☆☆ (Poor)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="review_text">Your Review:</label>
                <textarea id="review_text" name="review_text" required placeholder="Describe your collaboration, their reliability, communication, etc."></textarea>
            </div>

            <button type="submit">Submit Review</button>
        </form>
    </div>
</body>
</html>