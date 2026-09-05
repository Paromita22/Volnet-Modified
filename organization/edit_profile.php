<?php
// 1. START SESSION & INCLUDE DB
session_start();
include __DIR__ . '/../includes/db.php';

// 2. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// 3. FETCH CURRENT DATA TO PRE-FILL THE FORM
$user_id = $_SESSION['user_id'];
$org_id = $_SESSION['org_id'];

// --- UPDATED QUERY TO FETCH ALL EDITABLE FIELDS ---
$stmt = $conn->prepare(
    "SELECT org_name, bio, address, mobile, contact_email FROM organization WHERE org_id = ?"
);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$org_name = htmlspecialchars($result['org_name'] ?? 'Your Organization');
$bio = htmlspecialchars($result['bio'] ?? '');
$address = htmlspecialchars($result['address'] ?? '');
$mobile = htmlspecialchars($result['mobile'] ?? '');
$contact_email = htmlspecialchars($result['contact_email'] ?? '');
$stmt->close();

// Fetch existing past events
$past_events = [];
$stmt_events = $conn->prepare("SELECT id, title, description FROM org_past_events WHERE org_id = ? ORDER BY created_at DESC");
$stmt_events->bind_param("i", $org_id);
$stmt_events->execute();
$result_events = $stmt_events->get_result();
while ($row = $result_events->fetch_assoc()) {
    $past_events[] = $row;
}
$stmt_events->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Setup Organization Account</title>
  <link rel="stylesheet" href="/assets/css/org_acc.css"/>
  <style>
   
      
        
      .form-card h3 { color:rgb(243, 239, 239); margin-bottom: 20px; font-weight: bold; border-bottom: 2px solid #b0f068; padding-bottom: 10px;}
      .btn-save { background-color: #b0f068; color: #fff; }
      .btn-save:hover { background-color: #b0f068; }
      .past-event-item { border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
      .delete-btn { color: #dc3545; text-decoration: none; font-weight: bold; }
  </style>
</head>
<body>

<div class="container my-5">
    <h1 class="text-center mb-5" style="color: #b0f068; font-weight: bold;">Setup Your Organization Profile</h1>

    <form action="/organization/update_profile.php" method="POST" enctype="multipart/form-data" class="form-card">
        <h3>Profile Information</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="profile_pic" class="form-label">Profile Picture / Logo</label>
                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">
                <small class="form-text text-muted">Upload a new picture to replace the current one.</small>
            </div>
            <div class="col-md-6 mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo $address; ?>">
            </div>
            <!-- NEW FIELDS -->
            <div class="col-md-6 mb-3">
                <label for="mobile" class="form-label">Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo $mobile; ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="contact_email" class="form-label">Public Contact Email</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo $contact_email; ?>">
            </div>
            <!-- END NEW FIELDS -->
            <div class="col-12 mb-3">
                <label for="bio" class="form-label">About Your Organization (Bio)</label>
                <textarea class="form-control" id="bio" name="bio" rows="5"><?php echo $bio; ?></textarea>
            </div>
        </div>
        <div class="text-end">
            <button type="submit" name="update_profile" class="btn btn-save">Save Profile Info</button>
        </div>
    </form>

    <!-- The rest of the page (Manage Past Events) remains the same -->
    <div class="form-card">
        <h3>Manage Past Events</h3>
        <div class="mb-4">
            <h5>Your Current Past Events:</h5>
            <?php if (empty($past_events)): ?>
                <p>You haven't added any past events yet.</p>
            <?php else: ?>
                <?php foreach ($past_events as $event): ?>
                <div class="past-event-item d-flex justify-content-between align-items-center">
                    <div><strong><?php echo htmlspecialchars($event['title']); ?></strong><p class="mb-0 small"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p></div>
                    <a href="/organization/update_profile.php?delete_event=<?php echo $event['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <hr>
        <h5 class="mt-4">Add a New Past Event</h5>
        <form action="/organization/update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3"><label for="event_title" class="form-label">Event Title</label><input type="text" class="form-control" id="event_title" name="event_title" required></div>
            <div class="mb-3"><label for="event_description" class="form-label">Event Description</label><textarea class="form-control" id="event_description" name="event_description" rows="3" required></textarea></div>
            <div class="mb-3"><label for="event_image" class="form-label">Event Image</label><input type="file" class="form-control" id="event_image" name="event_image" accept="image/*" required></div>
            <div class="text-end"><button type="submit" name="add_event" class="btn btn-success">Add New Event</button></div>
        </form>
    </div>
</div>

<footer class="volunteer-footer">
    <!-- Footer code here... -->
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>