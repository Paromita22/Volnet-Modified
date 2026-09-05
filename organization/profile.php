<?php
// 1. START SESSION & INCLUDE DB
session_start();
include __DIR__ . '/../includes/db.php'; 

// 2. VERIFY USER IS LOGGED IN AS AN ORGANIZATION
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// 3. PREPARE & FETCH DATA
$user_id = $_SESSION['user_id'];
$org_id = null; 

// Get the org_id from the user_id
$stmt_get_org_id = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
$stmt_get_org_id->bind_param("i", $user_id);
$stmt_get_org_id->execute();
$result_get_org_id = $stmt_get_org_id->get_result();

if ($org_row = $result_get_org_id->fetch_assoc()) {
    $org_id = $org_row['org_id'];
    $_SESSION['org_id'] = $org_id; 
}
$stmt_get_org_id->close();

if ($org_id === null) {
    header("Location: /organization/edit_profile.php?notice=complete_profile");
    exit();
}

// --- MODIFIED QUERY TO FETCH MORE DETAILS ---
// Fetches bio, address, mobile, and contact_email from the organization table
$stmt = $conn->prepare(
    "SELECT org_name, profile_pic_path, bio, address, mobile, contact_email 
     FROM organization 
     WHERE org_id = ?"
);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
$org_data = $result->fetch_assoc();
$stmt->close();

// Assign to variables for easy use in HTML, with defaults
$org_name = htmlspecialchars($org_data['org_name'] ?? "Organization Name Not Set");
$profile_pic_path = $org_data['profile_pic_path'] ?? null;
$bio = !empty($org_data['bio']) ? nl2br(htmlspecialchars($org_data['bio'])) : "No bio provided. Go to 'Setup Account' to add one!";
$address = htmlspecialchars($org_data['address'] ?? "No address provided.");
$mobile = htmlspecialchars($org_data['mobile'] ?? "No mobile number provided.");
$contact_email = htmlspecialchars($org_data['contact_email'] ?? "No contact email provided.");

// --- NEW: FETCH CONTRIBUTION STATS ---
$event_count = 0;
$stmt_stats = $conn->prepare("SELECT COUNT(id) as event_count FROM eventt WHERE org_id = ?");
$stmt_stats->bind_param("i", $org_id);
$stmt_stats->execute();
$stats_result = $stmt_stats->get_result()->fetch_assoc();
$event_count = $stats_result['event_count'] ?? 0;
$stmt_stats->close();


// Fetch Past Events Portfolio
$past_events = [];
$stmt_events = $conn->prepare("SELECT title, description, image_path FROM org_past_events WHERE org_id = ? ORDER BY created_at DESC");
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>VolNet - <?php echo $org_name; ?></title>
  <link rel="stylesheet" href="/assets/css/org_acc.css"/>
  <style>
      /* Style for default profile icon */
      .organizer-logo .profile-icon {
          width: 200px; height: 200px; border-radius: 50%; background-color: #e9ecef;
          display: flex; align-items: center; justify-content: center; font-size: 80px;
          color: #6c757d; border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);
          margin: 0 auto 15px auto;
      }
      .organizer-logo img.profile-img {
          width: 200px; height: 200px; border-radius: 50%; object-fit: cover; 
          border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
      .contact-info { margin-bottom: 5px; font-style: italic; }
      .inspiration .row { margin-bottom: 2rem; }

      /* Styles for the new sections - derived from volun_acc.php for consistency */
      .section-card {
        background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
        padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      }
      .section-card h3 {
        color: #343a40; margin-bottom: 20px; font-weight: bold;
        border-bottom: 2px solid #0d6efd; padding-bottom: 10px;
      }
      .contributions-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px; margin-bottom: 20px;
      }
      .contribution-item {
        background-color:rgb(0, 0, 0); padding: 20px; border-radius: 5px; text-align: center;
        border: 1px solid #ced4da;
      }
      .contribution-item h5 { color:#9bd258;; font-size: 24px; margin-bottom: 5px; font-weight: bold; }
      .contribution-item p { margin: 0; font-size: 16px; color: #495057; }
      .contribution-item.link-card { cursor: pointer; transition: transform 0.2s; }
      .contribution-item.link-card:hover { transform: translateY(-5px); background-color: #dde4ed; }
      
      .review-item {
        background-color: #fff; padding: 15px; border-radius: 5px;
        margin-bottom: 15px; border: 1px solid #e9ecef;
      }
      .review-item strong { color: #9bd258; }
      .star-rating { font-size: 18px; color: gold; letter-spacing: 2px; }
      
      /* Styles for the new sections - derived from volun_acc.php for consistency */
      .section-card {
        background-color: #1a1a1a; /* Slightly lighter dark for cards */
        border: 1px solid #4b4847; /* Border from blogs.css .card */
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        color: #ffffff; /* Ensure text is visible on dark background */
      }
      .section-card h3 {
        color: #9bd258; /* Greenish yellow color from theme */
        margin-bottom: 20px;
        font-weight: bold;
      }
      .review-item {
        background-color: #333;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        border: 1px solid #4b4847;
      }
      .review-item strong {
        color: #9bd258;
      }
      .review-item .rating {
        color: gold;
      }
      .star-rating {
    font-size: 18px;
    color: gold;
    letter-spacing: 2px;
}
#t{
   color:rgb(255, 255, 255);
}
#tt{
   color:rgb(255, 255, 255);
}
  </style>
</head>
<body>
  <!-- Header & Sidebar remain unchanged -->
  <span class="menu-icon" onclick="toggleSidebar()">☰</span>
  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/organization/dashboard.php" style="margin-left: 25px;">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"><i class="fa-solid fa-bars"></i></button>
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item"><a class="nav-link" href="/organization/dashboard.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="/pages/blogs.php">Blog</a></li>
          <li class="nav-item"><a class="nav-link" href="/pages/contactus.php">Contact</a></li>
        </ul>
        <div class="d-flex gap-2"><button class="btn btn-dark" id="logout" onclick="window.location.href='/auth/logout.php'">Log out</button></div>
      </div>
    </nav>
  </header>
   <aside class="sidebar" id="sidebar">
    <a href="/organization/create_event.php">Create Event</a>
    <a href="/organization/history.php">History</a>
    <a href="/admin/volunteers.php">Available Volunteers</a>
    <a href="/organization/view_applicants.php">View Applications</a>
    <a href="/volunteer/write_review.php">Submit Review</a>
    <a href="/organization/manage_events.php">Manage Event Attendance</a>
    <a href="/organization/edit_profile.php">Setup/Edit Account</a>
     <a href="/volunteer/submit_complaint.php">Submit Complaint</a>
    <a href="/auth/logout.php">Logout</a>
  </aside>

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
            <p class="contact-info"><i class="fas fa-map-marker-alt"></i> <?php echo $address; ?></p>
            <p class="contact-info"><i class="fas fa-envelope"></i> <?php echo $contact_email; ?></p>
            <p class="contact-info"><i class="fas fa-phone"></i> <?php echo $mobile; ?></p>
        </div>
      </div>

      <div class="organizer-about">
        <h2>About Organization</h2>
        <p><?php echo $bio; ?></p>
      </div>
    </div>
  </section>

  <!-- NEW: Contribution Summary Section -->
  <section class="container my-4">
    <div class="section-card">
        <h3>Contribution Summary</h3>
        <div class="contributions-grid">
            <div class="contribution-item">
                <h5><?php echo $event_count; ?></h5>
                <p id="t">Total Events</p>
            </div>
            <div class="contribution-item link-card" onclick="window.location.href='/organization/history.php'">
                <h5>View All</h5>
                <p id="tt">Event History</p>
            </div>
        </div>
    </div>
  </section>

  <section class="inspiration">
    <h2>Some Past Events</h2>
    <!-- This section remains the same -->
    <?php if (empty($past_events)): ?>
        <div class="text-center p-5"><p>No past events have been added yet.</p><p>You can add them from the <a href="/organization/edit_profile.php">'Setup/Edit Account'</a> page to showcase your work!</p></div>
    <?php else: ?>
        <?php foreach ($past_events as $key => $event): ?>
            <div class="row align-items-center"><div class="col-sm-6 <?php echo $key % 2 != 0 ? 'order-sm-2' : ''; ?>"><img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="story-img"></div><div class="col-sm-6 <?php echo $key % 2 != 0 ? 'order-sm-1' : ''; ?>"><p><b><?php echo htmlspecialchars($event['title']); ?></b> <br><?php echo nl2br(htmlspecialchars($event['description'])); ?></p></div></div>
        <?php endforeach; ?>
    <?php endif; ?>
  </section>

 
<!-- Reviews Section -->
<section class="container" style="margin-top: 30px;">
  <div class="section-card">
    <h3>Received Reviews / Ratings</h3>

    <?php
    include __DIR__ . '/../includes/db.php'; // Ensure DB is included

    $org_user_id = $_SESSION['user_id'];

    $review_stmt = $conn->prepare("
      SELECT r.review_text, r.rating, r.created_at, u.email AS volunteer_email
      FROM reviews r
      JOIN users u ON r.reviewer_user_id = u.user_id
      WHERE r.reviewee_user_id = ? 
        AND r.reviewee_role = 'organization' 
        AND r.reviewer_role = 'volunteer'
      ORDER BY r.created_at DESC
    ");
    $review_stmt->bind_param("i", $org_user_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result();

    if ($review_result->num_rows > 0) {
        while ($review = $review_result->fetch_assoc()) {
            $rating = (int)$review['rating'];
            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); // filled and empty stars

            echo "<div class='review-item'>";
            echo "<p><strong>From Volunteer:</strong> " . htmlspecialchars($review['volunteer_email']) . "</p>";
            echo "<p><strong>Rating:</strong> <span class='star-rating'>$stars</span></p>";
            echo "<p><strong>Review: </strong>" . nl2br(htmlspecialchars($review['review_text'])) . "</p>";
            echo "<p><em>Posted on: " . htmlspecialchars($review['created_at']) . "</em></p>";
            echo "</div><hr>";
        }
    } else {
        echo "<p>No reviews yet. Encourage volunteers to leave a review!</p>";
    }

    $review_stmt->close();
    ?>
  </div>
</section>


  <!-- Footer & Scripts remain unchanged -->
  <footer class="volunteer-footer">
    <div class="footer-content"><div class="footer-section about"><h3>About Us</h3><p>We're a community of changemakers dedicated to making the world a better place through volunteering and compassion.</p></div><div class="footer-section links"><h3>Quick Links</h3><ul><li><a href="#">Events</a></li><li><a href="#">Join Us</a></li><li><a href="#">Contact</a></li></ul></div><div class="footer-section contact"><h3>Contact</h3><p>Email: volnet@gmail.com</p><p>Phone: +123 456 7890</p><p>Address: 123 Hope Street, Kindness City</p></div><div class="footer-section social"><h3>Follow Us</h3><div class="social-icons"><a href="#"><i class="fa-brands fa-square-facebook"></i></a><a href="#"><i class="fa-brands fa-instagram"></i></a><a href="#"><i class="fa-brands fa-twitter"></i></a></div></div></div><div class="footer-bottom">© 2025 VolNet | Made with ❤️ for a better tomorrow</div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>function toggleSidebar() { document.getElementById('sidebar').classList.toggle('active'); }</script>
</body>
</html>