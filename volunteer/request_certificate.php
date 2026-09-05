<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$volunteer_id = null;
$eligible_events = [];

// Get the volunteer_id from the user_id
$stmt = $conn->prepare("SELECT volunteer_id, name FROM volunteer WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $volunteer_id = $row['volunteer_id'];
    $volunteer_name = htmlspecialchars($row['name']);
}
$stmt->close();

// If we have a volunteer_id, fetch events they attended but don't have a certificate for
if ($volunteer_id) {
    $sql = "SELECT e.id, e.title
            FROM volunteer_events ve
            JOIN eventt e ON ve.event_id = e.id
            LEFT JOIN certificates c ON ve.event_id = c.event_id AND ve.volunteer_id = c.volunteer_id
            WHERE ve.volunteer_id = ? 
              AND ve.status = 'Attended'
              AND c.certificate_id IS NULL"; // This is the magic part: only show events without a certificate

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $volunteer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eligible_events[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Certificate - VolNet</title>
    <link rel="stylesheet" href="/assets/css/orgh.css"> <!-- Re-use your existing styles -->
    <style>
        body { background-color: #000; color: #fff; }
        .container { max-width: 800px; margin: 40px auto; padding: 30px; background-color: #1a1a1a; border-radius: 8px; border: 1px solid #4b4847; }
        h1 { color: #9bd258; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 10px; font-weight: bold; }
        select, input, button {
            width: 100%;
            padding: 12px;
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
            border-radius: 5px;
        }
        button { background-color: #9bd258; color: #000; font-weight: bold; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #b0f068; }
        .alert { padding: 15px; background-color: #4b4847; border-left: 5px solid #9bd258; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Request a Certificate</h1>
        
        <?php if (!empty($eligible_events)): ?>
            <p class="alert">Select an event you attended to generate your certificate of appreciation. The certificate will be added to your profile.</p>
            <form action="/volunteer/generate_certificate.php" method="POST">
                <div class="form-group">
                    <label for="event_id">Select an Attended Event:</label>
                    <select name="event_id" id="event_id" required>
                        <?php foreach ($eligible_events as $event): ?>
                            <option value="<?php echo $event['id']; ?>">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Generate My Certificate</button>
            </form>
        <?php else: ?>
            <div class="alert">
                <strong>No eligible events found.</strong>
                <p>You can request a certificate after you have 'Attended' an event. If you have, you may have already generated a certificate for all your past events. Check your profile!</p>
            </div>
            <a href="/volunteer/profile.php" style="display: block; text-align: center; color: #9bd258;">Back to My Account</a>
        <?php endif; ?>
    </div>
</body>
</html>