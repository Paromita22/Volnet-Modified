<?php
// 1. START SESSION & INCLUDE DB
session_start();
include __DIR__ . '/../includes/db.php';

// 2. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// 3. PREPARE VARIABLES (with default values)
$user_id = $_SESSION['user_id'];
$name = "Not Set";
$email = "Not Set";
$member_since = "N/A";
$age = "N/A";
$address = "Not Provided";
$mobile = "Not Provided";
$skills = "No skills listed.";
$bio = "No bio provided.";
$profile_pic_path = null; // Initialize as null

// Contribution variables
$activity_count = 0;
$total_hours = 0;
$last_activity_date = "N/A";

// 4. FETCH VOLUNTEER DATA
$stmt = $conn->prepare(
    "SELECT u.email, u.created_at, v.volunteer_id, v.name, v.age, v.mobile, v.address, v.skills, v.bio, v.profile_pic_path
     FROM users u
     LEFT JOIN volunteer v ON u.user_id = v.user_id
     WHERE u.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $volunteer_data = $result->fetch_assoc();
    $volunteer_id = $volunteer_data['volunteer_id']; 

    // 5. ASSIGN FETCHED DATA (CORRECT LOCATION)
    $name = htmlspecialchars($volunteer_data['name'] ?? "Not Set");
    $email = htmlspecialchars($volunteer_data['email'] ?? "Not Set");
    $age = htmlspecialchars($volunteer_data['age'] ?? "N/A");
    $mobile = htmlspecialchars($volunteer_data['mobile'] ?? "Not Provided");
    $address = htmlspecialchars($volunteer_data['address'] ?? "Not Provided");
    $skills = htmlspecialchars($volunteer_data['skills'] ?? "No skills listed.");
    
    // --- MOVED THESE LINES INSIDE THE IF BLOCK ---
    $bio = htmlspecialchars($volunteer_data['bio'] ?? "No bio provided. You can add one in 'Setup Account'.");
    $profile_pic_path = $volunteer_data['profile_pic_path'] ?? null;
    // ---------------------------------------------

    if (!empty($volunteer_data['created_at'])) {
        $date = new DateTime($volunteer_data['created_at']);
        $member_since = $date->format('Y-m-d');
    }

    // --- FETCH CONTRIBUTION DATA ---
    if ($volunteer_id) {
        // ... your contribution query logic remains the same ...
        $contrib_stmt = $conn->prepare(
            "SELECT 
                COUNT(*) as activity_count, 
                SUM(ve.hours_completed) as total_hours,
                MAX(e.end_date) as last_activity
            FROM volunteer_events ve
            JOIN eventt e ON ve.event_id = e.id
            WHERE ve.volunteer_id = ? AND ve.status = 'Attended'"
        );
        $contrib_stmt->bind_param("i", $volunteer_id);
        $contrib_stmt->execute();
        $contrib_result = $contrib_stmt->get_result()->fetch_assoc();

        $activity_count = $contrib_result['activity_count'] ?? 0;
        $total_hours = number_format((float)($contrib_result['total_hours'] ?? 0), 2);
        $last_activity_date = $contrib_result['last_activity'] ?? "N/A";
        



$badge_html = "";
$status_text = "New Member"; // Default status

if ($activity_count >= 6) {
    // Gold Badge for 6+ events
    $badge_html = '<img src="/uploads/badges/gold-badge.png" alt="Gold Volunteer Badge" class="badge-img" onclick="openFullscreen(this)">';
    $status_text = "Gold";
} elseif ($activity_count >= 4) {
    // Silver Badge for 4-5 events
    $badge_html = '<img src="/uploads/badges/silver-badge.png" alt="Silver Volunteer Badge" class="badge-img" onclick="openFullscreen(this)">';
    $status_text = "Silver";
} elseif ($activity_count >= 2) {
    // Bronze Badge for 2-3 events
    $badge_html = '<img src="/uploads/badges/bronze-badge.png" alt="Bronze Volunteer Badge" class="badge-img" onclick="openFullscreen(this)">';
    $status_text = "Bronze";
} else {
    // No badge yet
    $badge_html = '<p style="color: #ccc;">No badges earned yet.</p>';
}





        $contrib_stmt->close();
    }
}

// --- ADD THIS CODE ---
$certificates = [];
if ($volunteer_id) {
    $cert_stmt = $conn->prepare("SELECT file_path FROM certificates WHERE volunteer_id = ? ORDER BY generated_at DESC");
    $cert_stmt->bind_param("i", $volunteer_id);
    $cert_stmt->execute();
    $cert_result = $cert_stmt->get_result();
    while ($cert_row = $cert_result->fetch_assoc()) {
        $certificates[] = $cert_row;
    }
    $cert_stmt->close();
}
// --- END OF ADDED CODE ---

$stmt->close();
//$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  
  <title>Volunteer Account</title>
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
  <style>
    /* Custom styles for the Volunteer Account page */
    body {
      background-color: #000000; /* From blogs.css / org_h.html theme */
      color: #ffffff; /* From blogs.css / org_h.html theme */
    }
    .custom-header {
      position: sticky;
      z-index: 98; /* Below the .menu-icon which is at 100 */
      background-color: #000; /* Or match your theme */
    }

    .account-container {
      padding: 30px 20px;
      margin-left: 0; /* Adjusted for sidebar, default */
    }

    /* Adjust content margin when sidebar is active */
    .sidebar.active + .account-container {
      margin-left: 250px; /* Width of the sidebar */
      transition: margin-left 0.3s ease;
    }

    .volunteer-info-card, .section-card {
      background-color: #1a1a1a; /* Slightly lighter dark for cards */
      border: 1px solid #4b4847; /* Border from blogs.css .card */
      border-radius: 8px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .profile-pic {
      width: 120px;
      height: 120px;
      background-color: #4b4847;
      border-radius: 50%;
      margin: 0 auto 20px auto;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 50px;
      color: #ccc;
      border: 2px solid #9bd258;
    }

    .volunteer-info-card h4, .section-card h3 {
      color: #9bd258; /* Greenish yellow color from theme */
      margin-bottom: 20px;
      font-weight: bold;
    }

    .volunteer-info-card p, .section-card p {
      margin-bottom: 8px;
      font-size: 16px;
    }
    
    .volunteer-info-card span, .section-card span {
        font-weight: bold;
        color: #ddd;
    }

    .contributions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .contribution-item {
      background-color: #333;
      padding: 15px;
      border-radius: 5px;
      text-align: center;
      border: 1px solid #4b4847;
    }

    .contribution-item h5 {
      color: #9bd258;
      font-size: 20px;
      margin-bottom: 5px;
    }

    .badge-container {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 15px;
    }

    .badge-placeholder {
      width: 80px;
      height: 80px;
      background-color: #4b4847;
      border-radius: 5px;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #ccc;
      font-size: 14px;
      border: 1px dashed #9bd258;
    }

    .badge-silver {
      background: linear-gradient(to right, #d5d2d2, rgb(80, 78, 78)); /* Silver gradient */
      color: #333;
      font-weight: bold;
      border: 2px solid #9bd258;
      width: 100px; /* Larger for specific badge */
      height: 100px;
      border-radius: 50%; /* Circle for Silver badge */
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
    }

    /* Sidebar adjustments */
    .sidebar {
      position: fixed;
      top: 0;
      left: -250px; /* Hidden by default */
      width: 250px;
      height: 100%;
      background-color: #222;
      padding-top: 70px;
      transition: left 0.3s ease;
      z-index: 99;
      box-shadow: 2px 0 5px rgba(0,0,0,0.5);
    }

    .sidebar.active {
      left: 0;
    }

    .sidebar a {
      padding: 15px 20px;
      text-decoration: none;
      font-size: 18px;
      color: #ffffff;
      display: block;
      transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
      background-color: #444;
      color: #9bd258;
    }

    .menu-icon {
      position: fixed;
      top: 15px;
      left: 20px;
      font-size: 30px;
      color: #ffffff;
      cursor: pointer;
      z-index: 100;
    }

    .certificate-thumb {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 5px;
      cursor: pointer;
      border: 1px dashed #9bd258;
      transition: transform 0.2s ease;
    }

    .certificate-thumb:hover {
      transform: scale(1.05);
    }

    /* Modal style */
    #fullscreenModal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.85);
    }

    #fullscreenModal img {
      margin: auto;
      display: block;
      max-width: 90%;
      max-height: 90%;
      margin-top: 5%;
      border: 4px solid #9bd258;
      border-radius: 10px;
      box-shadow: 0 0 20px #9bd258;
    }

    #fullscreenModal:active {
      display: none;
    }
/* NEW: Styles for actual profile image */
    .profile-pic {
        width: 120px;
        height: 120px;
        background-color: #4b4847;
        border-radius: 50%;
        margin: 0 auto 20px auto;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 50px;
        color: #ccc;
        border: 2px solid #9bd258;
        overflow: hidden; /* Crucial for image cropping */
    }
    .profile-pic img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Ensures the image covers the circular area */
    }
  
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .account-container {
        padding: 20px 10px;
        margin-left: 0;
      }
      .sidebar.active + .account-container {
        margin-left: 0; /* On small screens, content doesn't shift */
      }
      .contributions-grid {
        grid-template-columns: 1fr; /* Stack on smaller screens */
      }
    }
    /* Add this to your CSS file if you haven't already */
.badge-img {
  width: 100px;
  height: 100px;
  object-fit: contain;
  cursor: pointer;
  transition: transform 0.2s ease;
}
.badge-img:hover {
  transform: scale(1.1);
}
  
/* STRONGER CSS RULE FOR STAR RATING */
.review-item .star-rating {
    color: gold !important; /* The !important gives it maximum priority */
    font-size: 18px;
    letter-spacing: 1px;
}
  </style>
</head>


<body>
<!-- ADD THIS PHP BLOCK FOR MESSAGES -->
<?php if (isset($_GET['cert_success'])): ?>
    <div class="alert alert-success" style="position: absolute; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1000; background-color: #9bd258; color: black; padding: 15px; border-radius: 5px;">
        Certificate generated successfully! It has been added to your profile.
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
     <div class="alert alert-danger" style="position: absolute; top: 80px; left: 50%; transform: translateX(-50%); z-index: 1000; background-color: #d9534f; color: white; padding: 15px; border-radius: 5px;">
        An error occurred: <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>
<!-- END OF ADDED BLOCK -->



<span class="menu-icon" onclick="toggleSidebar()">☰</span>

<aside class="sidebar" id="sidebar">
  
    <a href="/volunteer/request_certificate.php">Request Certificate</a>
    <a href="/volunteer/history.php">History</a>
    <a href="/pages/projects.php">Explore Events</a>
    <a href="/admin/organizations.php">View Organizations</a>
    <a href="/volunteer/applications.php">Application Tracking</a>
    <a href="/volunteer/write_review.php">Submit Review</a>
    <a href="/volunteer/edit_profile.php">Setup account</a>
     <a href="/volunteer/submit_complaint.php">Submit Complaint</a>
</aside>

<header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/volunteer/dashboard.php" style="margin-left: 40px;">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
      </button>
  
    <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item">
            <a class="nav-link" href="/volunteer/dashboard.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/pages/projects.php">Projects</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/pages/blogs.php">Blog</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " href="/pages/contactus.php">Contact</a>
          </li>
        </ul>
  
        <form class="d-flex me-3" role="search" action="/pages/search.php" method="GET">
          <input class="form-control me-2" type="search" name="q" placeholder="Search..." aria-label="Search" required>
          <button class="btn btn-outline-dark bg-white" type="submit"><i class="fa fa-search"></i></button>
        </form>
  
        <div class="d-flex gap-2">
          <button class="btn btn-dark" id="logout" onclick="window.location.href='/auth/logout.php'">Log out</button>
          </div>
      </div>
    </nav>
</header>

<div class="container account-container">
    <h1 class="text-center mb-5" style="color: #9bd258; font-weight: bold;">Volunteer Account</h1>

    <div class="row">
        <!-- START: Left Column -->
        <div class="col-lg-4 col-md-5">
            <div class="volunteer-info-card text-center">
                <div class="profile-pic">
                    <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <!-- DYNAMIC DATA STARTS HERE -->
                <h4><?php echo $name; ?></h4>
                <p>Member Since: <span><?php echo $member_since; ?></span></p>
                
<p>Status: <span><?php echo $status_text; ?></span></p>
                <p>Email: <span><?php echo $email; ?></span></p>
                <p>Age: <span><?php echo $age; ?></span></p>
                <p>Address: <span><?php echo $address; ?></span></p>
                <p>Mobile: <span><?php echo $mobile; ?></span></p>
                <!-- DYNAMIC DATA ENDS HERE -->
            </div>
        </div>
        <!-- END: Left Column -->

        <!-- START: Right Column (ALL SECTIONS ARE NOW INSIDE THIS DIV) -->
        <div class="col-lg-8 col-md-7">
            <!-- Contributions Section -->
            <div class="section-card">
                <h3>Contributions</h3>
                <div class="contributions-grid">
                    <div class="contribution-item">
                        <h5>Activities</h5>
                        <p><?php echo $activity_count; ?></p>
                    </div>
                    <div class="contribution-item">
                        <h5>Hours</h5>
                        <p><?php echo $total_hours; ?></p>
                    </div>
                    <div class="contribution-item">
                        <h5>Last Activity</h5>
                        <p><?php echo $last_activity_date; ?></p>
                    </div>
                    <div class="contribution-item" style="cursor:pointer;" onclick="window.location.href='/volunteer/history.php'">
                        <h5>Past Activity</h5>
                        <p>View All</p>
                    </div>
                </div>
            </div>

            <!-- Overview Section -->
            <div class="section-card">
                <h3>Overview</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p>Bio: <span><?php echo nl2br($bio); ?></span></p> <!-- nl2br preserves line breaks -->
                    </div>
                    <div class="col-md-6">
                        <p>Skills: <span><?php echo $skills; ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Certifications Section -->
            <div class="section-card">
                <h3>Certifications / Badges</h3>
                <!-- This is your NEW code -->
<div class="badge-container">
    <!-- Display Generated Certificates -->
    <?php if (!empty($certificates)): ?>
        <?php foreach ($certificates as $cert): ?>
            <img src="<?php echo htmlspecialchars($cert['file_path']); ?>" alt="Volunteer Certificate" class="certificate-thumb" onclick="openFullscreen(this)">
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #ccc;">No certificates earned yet. <a href="/volunteer/request_certificate.php" style="color: #9bd258;">Request one now!</a></p>
    <?php endif; ?>
    
    <hr style="width:100%; border-color: #444;"> <!-- A separator -->

    <!-- Display Badges -->
    <?php echo $badge_html; ?>
</div>
   

</div>
            </div>

            <!-- Reviews Section -->
<section class="container" style="margin-top: 30px;">
  <div class="section-card">
    <h3>Received Reviews / Ratings</h3>

    <?php
    //include __DIR__ . '/../includes/db.php'; // Ensure DB is included
    $vol_user_id = $_SESSION['user_id']; // Volunteer’s user_id from session

    $review_stmt = $conn->prepare("
      SELECT r.review_text, r.rating, r.created_at, o.org_name
      FROM reviews r
      JOIN organization o ON r.reviewer_user_id = o.user_id
      WHERE r.reviewee_user_id = ? 
        AND r.reviewee_role = 'volunteer' 
        AND r.reviewer_role = 'organization'
      ORDER BY r.created_at DESC
    ");
    $review_stmt->bind_param("i", $vol_user_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result();

    if ($review_result->num_rows > 0) {
        while ($review = $review_result->fetch_assoc()) {
            $rating = (int)$review['rating'];
            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); // filled and empty stars

            echo "<div class='review-item'>";
            echo "<p><strong>From Organization:</strong> " . htmlspecialchars($review['org_name']) . "</p>";
            echo "<p><strong>Rating:</strong> <span class='star-rating'>$stars</span></p>";
            echo "<p> <strong>Review: </strong>" . nl2br(htmlspecialchars($review['review_text'])) . "</p>";
            echo "<p><em>Posted on: " . htmlspecialchars($review['created_at']) . "</em></p>";
            echo "</div><hr>";
        }
    } else {
        echo "<p>No reviews yet. Be the first to get one from an organization!</p>";
    }

    $review_stmt->close();
    ?>
  </div>
</section>
        <!-- END: Right Column -->
    </div>
</div>

  <footer class="volunteer-footer">
    <div class="footer-content">
      <div class="footer-section about">
        <h3>About Us</h3>
        <p>We're a community of changemakers dedicated to making the world a better place through volunteering and compassion.</p>
      </div>
  
      <div class="footer-section links">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="#">Events</a></li>
          <li><a href="#">Join Us</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </div>
  
      <div class="footer-section contact">
        <h3>Contact</h3>
        <p>Email: volnet@gmail.com</p>
        <p>Phone: +123 456 7890</p>
        <p>Address: 123 Hope Street, Kindness City</p>
      </div>
  
      <div class="footer-section social">
        <h3>Follow Us</h3>
        <div class="social-icons">
          <a href="#"><i class="fa-brands fa-square-facebook"></i></a>
          <a href="#"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
    </div>
  
    <div class="footer-bottom">
      &copy; 2025 VolNet | Made with ❤️ for a better tomorrow
    </div>
  </footer>
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
      // Optional: Adjust content margin when sidebar is active
      const accountContainer = document.querySelector('.account-container');
      if (sidebar.classList.contains('active')) {
        accountContainer.style.marginLeft = '250px';
      } else {
        accountContainer.style.marginLeft = '0';
      }
    }
     document.addEventListener('DOMContentLoaded', function () {
    const exploreBtn = document.getElementById('explore-btn');
    if (exploreBtn) { // Check if the element exists to prevent errors on this page
        exploreBtn.addEventListener('click', toggleSidebar);
    }
  });
</script>

<!-- Fullscreen Modal -->
<div id="fullscreenModal" onclick="this.style.display='none'">
  <img id="fullscreenImg" src="" alt="Full Size Certificate">
</div>

<script>
  function openFullscreen(img) {
    const modal = document.getElementById('fullscreenModal');
    const modalImg = document.getElementById('fullscreenImg');
    modalImg.src = img.src;
    modal.style.display = 'block';
  }
</script>
<!--
<script>
  function toggleReviewForm() {
    const form = document.getElementById("reviewFormContainer");
    form.style.display = form.style.display === "none" ? "block" : "none";
  }
</script>
-->
</body>

<?php
  // Finally, close the database connection
  $conn->close();
?>
</html>
