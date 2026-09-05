<?php
// view_org_profile.php
session_start();
include __DIR__ . '/../includes/db.php';

// Allow any logged-in user to view the profile.
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// Check for and get the organization ID from the URL
if (!isset($_GET['org_id']) || !is_numeric($_GET['org_id'])) {
    die("Error: Organization ID not specified or invalid.");
}
$org_id = (int)$_GET['org_id'];

// --- Initialize variables ---
$profile_found = false;
$org_name = "Not Found";
$profile_pic_path = null;
$bio = "No information provided.";
$address = "No information provided.";
$org_user_id = null; // Needed for fetching reviews
$past_events = [];
$reviews = [];
$event_count = 0;
$mobile = "No information provided.";
$contact_email = "No information provided.";

// --- Fetch all data for this organization ---

// 1. Fetch Organization Details
$stmt = $conn->prepare(
    "SELECT o.user_id, o.org_name, o.profile_pic_path, o.bio, o.address, o.mobile, o.contact_email 
     FROM organization o 
     WHERE o.org_id = ?"
);




$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();

if ($org_data = $result->fetch_assoc()) {
    $profile_found = true;
    $org_user_id = $org_data['user_id'];
    


 $org_name = htmlspecialchars($org_data['org_name'] ?? "Organization Name Not Set");
    $profile_pic_path = $org_data['profile_pic_path'] ?? null;
    $bio = !empty($org_data['bio']) ? nl2br(htmlspecialchars($org_data['bio'])) : "No bio provided.";
    $address = htmlspecialchars($org_data['address'] ?? "No address provided.");
    $mobile = htmlspecialchars($org_data['mobile'] ?? "No mobile number provided.");
    $contact_email = htmlspecialchars($org_data['contact_email'] ?? "No contact email provided.");

}
$stmt->close();
// --- ADD THIS NEW BLOCK RIGHT HERE ---
// NEW: Fetch Contribution Stats (only if profile was found)
if ($profile_found) {
    $stmt_stats = $conn->prepare("SELECT COUNT(id) as event_count FROM eventt WHERE org_id = ?");
    $stmt_stats->bind_param("i", $org_id);
    $stmt_stats->execute();
    $stats_result = $stmt_stats->get_result()->fetch_assoc();
    $event_count = $stats_result['event_count'] ?? 0;
    $stmt_stats->close();
}
// --- END OF NEW BLOCK ---

// 2. Fetch Past Events (only if profile was found)
if ($profile_found) {
    $stmt_events = $conn->prepare("SELECT title, description, image_path FROM org_past_events WHERE org_id = ? ORDER BY created_at DESC");
    $stmt_events->bind_param("i", $org_id);
    $stmt_events->execute();
    $result_events = $stmt_events->get_result();
    while ($row = $result_events->fetch_assoc()) {
        $past_events[] = $row;
    }
    $stmt_events->close();
}

// 3. Fetch Reviews (only if we found the organization's user_id)
if ($org_user_id) {
    $review_stmt = $conn->prepare("
      SELECT r.review_text, r.rating, r.created_at, u.email AS volunteer_email
      FROM reviews r
      JOIN users u ON r.reviewer_user_id = u.user_id
      WHERE r.reviewee_user_id = ? AND r.reviewee_role = 'organization'
      ORDER BY r.created_at DESC
    ");
    $review_stmt->bind_param("i", $org_user_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result();
    while ($review = $review_result->fetch_assoc()) {
        $reviews[] = $review;
    }
    $review_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile - <?php echo $org_name; ?></title>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/org_acc.css"/>
  <style>
      /* Reusing styles from org_acc.php for consistency */
      .organizer-logo .profile-icon {
          width: 200px; height: 200px; border-radius: 50%; background-color: #e9ecef;
          display: flex; align-items: center; justify-content: center; font-size: 80px;
          color: #6c757d; border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin: 0 auto 15px auto;
      }
      .organizer-logo img.profile-img { width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
      .address-info { text-align: center; margin-top: 10px; color: #555; font-style: italic; }
      .inspiration .row { margin-bottom: 2rem; }
      .section-card { background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-bottom: 25px; }
      .section-card h3 { color: #333; margin-bottom: 20px; font-weight: bold; }
      .review-item { border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; }
      .star-rating { color: gold; }
        <style>
      /* Main body and header styles for a dark theme */
      body {
          background-color: #000000;
          color: #ffffff;
      }
      .custom-header {
          position: sticky;
          top: 0;
          z-index: 98;
          background-color: #000;
      }
      .custom-header a {
          color: #ffffff !important; /* Force white text in header */
      }
      .container.account-container {
          padding: 30px 20px;
      }

      /* Card styles from volunteer profile for consistency */
      .section-card {
          background-color: #1a1a1a;
          border: 1px solid #4b4847;
          border-radius: 8px;
          padding: 25px;
          margin-bottom: 25px;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }
      .section-card h3 {
          color: #9bd258;
          margin-bottom: 20px;
          font-weight: bold;
      }
      .section-card p, .section-card blockquote {
          color: #ddd;
      }
      .section-card strong {
        color: #fff;
      }

      /* Styles for the organizer-specific sections, adapted for dark theme */
      .organizer-section { padding: 30px 20px; background-color: #1a1a1a; margin-bottom: 25px; border-radius: 8px; border: 1px solid #4b4847; }
      .org-name { color: #9bd258; font-size: 2rem; font-weight: bold; text-align: center; margin-bottom: 15px; }
      .organizer-logo .profile-icon {
          width: 150px; height: 150px; border-radius: 50%; background-color: #4b4847;
          display: flex; align-items: center; justify-content: center; font-size: 60px;
          color: #ccc; border: 3px solid #9bd258; margin: 0 auto 15px auto;
      }
      .organizer-logo img.profile-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #9bd258; }
      .address-info { text-align: center; margin-top: 10px; color: #ccc; }
      .organizer-about h2 { color: #9bd258; text-align: center; margin-bottom: 15px; }
      .organizer-about p { color: #ddd; }

      /* Past Events section styling for dark theme */
      .inspiration { padding: 30px 20px; }
      .inspiration h2 { text-align: center; color: #9bd258; margin-bottom: 2rem; }
      .inspiration .row { margin-bottom: 2rem; align-items: center; }
      .story-img { width: 100%; border-radius: 8px; border: 1px solid #4b4847; }
      .inspiration p { color: #ddd; }
      .inspiration b { color: #9bd258; }
      /* Find the <style> tag in view_org_profile.php and add these rules inside it */
.contributions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.contribution-item {
    background-color: #333; /* Darker item background */
    padding: 20px;
    border-radius: 5px;
    text-align: center;
    border: 1px solid #4b4847;
}
.contribution-item h5 {
    color: #9bd258;
    font-size: 24px;
    margin-bottom: 5px;
    font-weight: bold;
}
.contribution-item p {
    margin: 0;
    font-size: 16px;
    color: #ddd; /* Lighter text for dark background */
}
.contact-info-container {
    text-align: center;
    margin-top: 15px;
}
.contact-info-container p {
    color: #ccc;
    font-style: italic;
    margin-bottom: 5px;
}
.contact-info-container i {
    margin-right: 8px;
    color: #9bd258;
}
 
  </style>
</head>
<body>
  
  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
        <a class="navbar-brand fw-bold text-dark" href="/admin/dashboard.php">VolNet</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <!-- A simple back button is useful here -->
                <li class="nav-item"><a class="nav-link" href="javascript:history.back()">Back</a></li>
            </ul>
        </div>
    </nav>
  </header>
  
  <?php if ($profile_found): ?>
  <section class="organizer-section">
    <div class="organizer-container">
        <div class="organizer-logo">
            <div class="org-name"><?php echo $org_name; ?></div>
            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                <img src="<?php echo $profile_pic_path; ?>" alt="<?php echo $org_name; ?> Logo" class="profile-img" />
            <?php else: ?>
                <div class="profile-icon"><i class="fas fa-building"></i></div>
            <?php endif; ?>
            
            <!-- NEW: Enhanced Contact Info Block -->
            <div class="contact-info-container">
                <p><i class="fas fa-map-marker-alt"></i> <?php echo $address; ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo $contact_email; ?></p>
                <p><i class="fas fa-phone"></i> <?php echo $mobile; ?></p>
            </div>
        </div>

        <div class="organizer-about">
            <h2>About Organization</h2>
            <p><?php echo $bio; ?></p>
        </div>
    </div>
</section>

<!--
    ADD THIS ENTIRE NEW SECTION right after the organizer-section
    and BEFORE the "Past Events Portfolio" section.
-->
<section class="container my-4">
    <div class="section-card">
        <h3>Contribution Summary</h3>
        <div class="contributions-grid">
            <div class="contribution-item">
                <h5><?php echo $event_count; ?></h5>
                <p>Total Events Hosted</p>
            </div>
            <!-- This is the card you are asking about -->
            <div class="contribution-item" style="cursor:pointer;" onclick="window.location.href='/organization/history.php?org_id=<?php echo $org_id; ?>'">
                <h5>View All</h5>
                <p>Event History</p>
            </div>
        </div>
    </div>
</section>


  <section class="inspiration">
    <h2>Past Events Portfolio</h2>
    <?php if (empty($past_events)): ?>
        <div class="text-center p-5"><p>This organization has not added any past events to their portfolio yet.</p></div>
    <?php else: ?>
        <?php foreach ($past_events as $key => $event): ?>
            <div class="row align-items-center">
                <div class="col-sm-6 <?php echo ($key % 2 != 0) ? 'order-sm-2' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="story-img">
                </div>
                <div class="col-sm-6 <?php echo ($key % 2 != 0) ? 'order-sm-1' : ''; ?>">
                    <p><b><?php echo htmlspecialchars($event['title']); ?></b><br><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </section>


<div class="container account-container">
    <div class="section-card">
        <h3>Received Reviews / Ratings</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach($reviews as $review): 
                $rating = (int)$review['rating'];
                $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
            ?>
                <div class="review-item" style="border-bottom: 1px solid #444; padding-bottom: 15px; margin-bottom: 15px;">
                    <p style="margin-bottom: 5px;"><strong>From:</strong> <?php echo htmlspecialchars($review['volunteer_email']); ?></p>
                    <p style="margin-bottom: 5px;"><strong>Rating:</strong> <span style="color: gold;"><?php echo $stars; ?></span></p>
                    <blockquote style="margin: 0; padding-left: 10px; border-left: 3px solid #9bd258; color: #ddd;"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></blockquote>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </div>
</div>
  
  <?php else: ?>
    <div class="container text-center my-5">
        <h1>Organization Not Found</h1>
        <p>The profile you are looking for does not exist.</p>
        <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
    </div>
  <?php endif; ?>

</body>
</html>