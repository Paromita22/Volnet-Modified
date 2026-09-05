<?php
// 1. START SESSION & INCLUDE DB
session_start();
include __DIR__ . '/../includes/db.php'; // Make sure this path is correct

// 2. SECURITY & INPUT CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

if (!isset($_GET['vid']) || !is_numeric($_GET['vid'])) {
    die("Error: Volunteer ID not specified or invalid.");
}

// 3. PREPARE VARIABLES
$volunteer_id_to_view = (int)$_GET['vid'];
$profile_found = false;

// Initialize all variables with default values
$name = "Not Found";
$email = "N/A";
$member_since = "N/A";
$age = "N/A";
$address = "N/A";
$mobile = "N/A";
$skills = "N/A";
$bio = "N/A";
$profile_pic_path = null;
$volunteer_user_id = null; // We need this for fetching reviews

// Contribution & Badge variables
$activity_count = 0;
$total_hours = 0;
$last_activity_date = "N/A";
$badge_html = '<p style="color: #ccc;">No badges earned yet.</p>';
$status_text = "New Member";

// Data arrays
$certificates = [];
$reviews = [];

// 4. FETCH ALL VOLUNTEER DATA
// MODIFIED QUERY: Added profile_pic_path and user_id
$stmt = $conn->prepare(
    "SELECT u.user_id, u.email, u.created_at, v.name, v.age, v.mobile, v.address, v.skills, v.bio, v.profile_pic_path 
     FROM volunteer v
     JOIN users u ON v.user_id = u.user_id 
     WHERE v.volunteer_id = ?"
);
$stmt->bind_param("i", $volunteer_id_to_view);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile_found = true;
    $volunteer_data = $result->fetch_assoc();
    $volunteer_user_id = $volunteer_data['user_id']; // Store the user_id

    // 5. ASSIGN FETCHED DATA
    $name = htmlspecialchars($volunteer_data['name'] ?? "Not Set");
    $email = htmlspecialchars($volunteer_data['email'] ?? "Not Set");
    $age = htmlspecialchars($volunteer_data['age'] ?? "N/A");
    $mobile = htmlspecialchars($volunteer_data['mobile'] ?? "Not Provided");
    $address = htmlspecialchars($volunteer_data['address'] ?? "Not Provided");
    $skills = htmlspecialchars($volunteer_data['skills'] ?? "No skills listed.");
    $bio = htmlspecialchars($volunteer_data['bio'] ?? "No bio provided.");
    $profile_pic_path = $volunteer_data['profile_pic_path'] ?? null;

    if (!empty($volunteer_data['created_at'])) {
        $member_since = (new DateTime($volunteer_data['created_at']))->format('Y-m-d');
    }

    // 6. FETCH CONTRIBUTION DATA
    $contrib_stmt = $conn->prepare(
        "SELECT COUNT(*) as activity_count, SUM(ve.hours_completed) as total_hours, MAX(e.end_date) as last_activity
        FROM volunteer_events ve
        JOIN eventt e ON ve.event_id = e.id
        WHERE ve.volunteer_id = ? AND ve.status = 'Attended'"
    );
    $contrib_stmt->bind_param("i", $volunteer_id_to_view);
    $contrib_stmt->execute();
    $contrib_result = $contrib_stmt->get_result()->fetch_assoc();

    $activity_count = $contrib_result['activity_count'] ?? 0;
    $total_hours = number_format((float)($contrib_result['total_hours'] ?? 0), 2);
    if (!empty($contrib_result['last_activity'])) {
        $last_activity_date = date('Y-m-d', strtotime($contrib_result['last_activity']));
    }
    $contrib_stmt->close();

    // 7. DETERMINE BADGE AND STATUS (Copied from volun_acc.php)
    if ($activity_count >= 6) {
        $badge_html = '<img src="/uploads/badges/gold-badge.png" alt="Gold Badge" class="badge-img" onclick="openFullscreen(this)">';
        $status_text = "Gold";
    } elseif ($activity_count >= 4) {
        $badge_html = '<img src="/uploads/badges/silver-badge.png" alt="Silver Badge" class="badge-img" onclick="openFullscreen(this)">';
        $status_text = "Silver";
    } elseif ($activity_count >= 2) {
        $badge_html = '<img src="/uploads/badges/bronze-badge.png" alt="Bronze Badge" class="badge-img" onclick="openFullscreen(this)">';
        $status_text = "Bronze";
    }

    // 8. FETCH CERTIFICATES
    $cert_stmt = $conn->prepare("SELECT file_path FROM certificates WHERE volunteer_id = ? ORDER BY generated_at DESC");
    $cert_stmt->bind_param("i", $volunteer_id_to_view);
    $cert_stmt->execute();
    $cert_result = $cert_stmt->get_result();
    while ($cert_row = $cert_result->fetch_assoc()) {
        $certificates[] = $cert_row;
    }
    $cert_stmt->close();
    
    // 9. FETCH REVIEWS (using the volunteer's user_id)
    if ($volunteer_user_id) {
        $review_stmt = $conn->prepare("
            SELECT r.review_text, r.rating, r.created_at, o.org_name
            FROM reviews r
            JOIN organization o ON r.reviewer_user_id = o.user_id
            WHERE r.reviewee_user_id = ? AND r.reviewee_role = 'volunteer'
            ORDER BY r.created_at DESC
        ");
        $review_stmt->bind_param("i", $volunteer_user_id);
        $review_stmt->execute();
        $review_result = $review_stmt->get_result();
        while ($review_row = $review_result->fetch_assoc()) {
            $reviews[] = $review_row;
        }
        $review_stmt->close();
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  
  <title>Volunteer Profile</title> <!-- CHANGED: Title -->
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
  <style>
    /* Custom styles for the Volunteer Profile page */
    body {
      background-color: #000000;
      color: #ffffff;
    }
    .custom-header {
      position: sticky;
      z-index: 98;
      background-color: #000;
    }

    .account-container {
      padding: 30px 20px;
    }

    .volunteer-info-card, .section-card {
      background-color: #1a1a1a;
      border: 1px solid #4b4847;
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
/* ADD THESE TWO RULES */

.profile-pic {
    overflow: hidden; /* This crops anything outside the circle */
}
.profile-pic img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* This makes the image fill the circle without distortion */
}

/* --- The rest of your CSS continues below --- */
    .volunteer-info-card h4, .section-card h3 {
      color: #9bd258;
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
      background: linear-gradient(to right, #d5d2d2, rgb(80, 78, 78));
      color: #333;
      font-weight: bold;
      border: 2px solid #9bd258;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 18px;
    }

    /* REMOVED: All sidebar-related CSS */
    
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
    #fullscreenModal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.85); }
    #fullscreenModal img { margin: auto; display: block; max-width: 90%; max-height: 90%; margin-top: 5%; border: 4px solid #9bd258; border-radius: 10px; box-shadow: 0 0 20px #9bd258; }
    #fullscreenModal:active { display: none; }

    @media (max-width: 768px) {
      .account-container { padding: 20px 10px; }
      .contributions-grid { grid-template-columns: 1fr; }
    }
    .badge-img {
  width: 100px;
  height: 100px;
  object-fit: contain;
  cursor: pointer;
}
  </style>
</head>
<body>

<!-- REMOVED: menu-icon and sidebar HTML -->

<header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/admin/dashboard.php" style="margin-left: 20px;">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
      </button>
  
    <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item"> <a class="nav-link" href="/volunteer/dashboard.php">Home</a> </li>
          <li class="nav-item"> <a class="nav-link" href="/pages/projects.php">Projects</a> </li>
          <li class="nav-item"> <a class="nav-link" href="/pages/blogs.php">Blog</a> </li>
          <li class="nav-item"> <a class="nav-link " href="/pages/contactus.php">Contact</a> </li>
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

    <?php if ($profile_found): ?>
    <h1 class="text-center mb-5" style="color: #9bd258; font-weight: bold;">Volunteer Profile</h1> <!-- CHANGED: Heading -->

    <div class="row">
        <!-- START: Left Column -->
        <div class="col-lg-4 col-md-5">
            <div class="volunteer-info-card text-center">
              <div class="profile-pic">
    <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile Picture" style="width:100%; height:100%; object-fit:cover;">
    <?php else: ?>
        <i class="fas fa-user"></i>
    <?php endif; ?>
</div>
                <h4><?php echo $name; ?></h4>
                <p>Member Since: <span><?php echo $member_since; ?></span></p>
               <p>Status: <span><?php echo $status_text; ?></span></p>
                <p>Email: <span><?php echo $email; ?></span></p>
                <p>Age: <span><?php echo $age; ?></span></p>
                <p>Address: <span><?php echo $address; ?></span></p>
                <p>Mobile: <span><?php echo $mobile; ?></span></p>
            </div>
        </div>
        <!-- END: Left Column -->

        <!-- START: Right Column -->
        <div class="col-lg-8 col-md-7">
            <!-- Contributions Section -->
            <div class="section-card">
                <h3>Contributions</h3>
                <div class="contributions-grid">
                    <div class="contribution-item"><h5>Activities</h5><p><?php echo $activity_count; ?></p></div>
                    <div class="contribution-item"><h5>Hours</h5><p><?php echo $total_hours; ?></p></div>
                    <div class="contribution-item"><h5>Last Activity</h5><p><?php echo $last_activity_date; ?></p></div>
                    <!-- WRAP THE DIV IN AN <a> TAG AND ADD THE LINK -->
<a href="/volunteer/history.php?vid=<?php echo $volunteer_id_to_view; ?>" style="text-decoration: none; color: inherit;">
    <div class="contribution-item" style="cursor:pointer;">
        <h5>Past Activity</h5>
        <p>View All</p>
    </div>
</a>
                </div>
            </div>

            <!-- Overview Section -->
            <div class="section-card">
                <h3>Overview</h3>
                <div class="row">
                    <div class="col-md-6">
                        <!-- CHANGED: Neutral message for bio -->
                        <p>Bio: <span><?php echo nl2br($bio); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p>Skills: <span><?php echo $skills; ?></span></p>
                    </div>
                </div>
            </div>

          <div class="section-card">
    <h3>Certifications & Badges</h3>
    <div class="badge-container">
        <!-- Display Generated Certificates -->
        <?php if (!empty($certificates)): ?>
            <?php foreach ($certificates as $cert): ?>
                <img src="<?php echo htmlspecialchars($cert['file_path']); ?>" alt="Certificate" class="certificate-thumb" onclick="openFullscreen(this)">
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #ccc;">No certificates earned yet.</p>
        <?php endif; ?>
        
        <hr style="width:100%; border-color: #444; margin: 15px 0;">

        <!-- Display Badges -->
        <?php echo $badge_html; ?>
    </div>
</div>

         <div class="section-card">
    <h3>Received Reviews / Ratings</h3>
    <?php if (!empty($reviews)): ?>
        <?php foreach($reviews as $review): 
            $rating = (int)$review['rating'];
            $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
        ?>
            <div class="review-item" style="border-bottom: 1px solid #444; padding-bottom: 15px; margin-bottom: 15px;">
                <p style="margin-bottom: 5px;"><strong>From:</strong> <?php echo htmlspecialchars($review['org_name']); ?></p>
                <p style="margin-bottom: 5px;"><strong>Rating:</strong> <span style="color: gold;"><?php echo $stars; ?></span></p>
                <blockquote style="margin: 0; padding-left: 10px; border-left: 3px solid #9bd258; color: #ddd;"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></blockquote>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>
</div>
        <!-- END: Right Column -->
    </div>
    
    <?php else: ?>
        <!-- Display this block if the volunteer ID was not found in the database -->
        <div class="text-center mt-5">
            <h1 style="color: #9bd258;">Profile Not Found</h1>
            <p>The volunteer profile you are looking for does not exist or could not be found.</p>
            <a href="javascript:history.back()" class="btn mt-3" style="background-color: #9bd258; color: #000; font-weight: bold;">Go Back</a>
        </div>
    <?php endif; ?>

</div>

<footer class="volunteer-footer">
    <div class="footer-content">
      <div class="footer-section about"><h3>About Us</h3><p>We're a community of changemakers dedicated to making the world a better place through volunteering and compassion.</p></div>
      <div class="footer-section links"><h3>Quick Links</h3><ul><li><a href="#">Events</a></li><li><a href="#">Join Us</a></li><li><a href="#">Contact</a></li></ul></div>
      <div class="footer-section contact"><h3>Contact</h3><p>Email: volnet@gmail.com</p><p>Phone: +123 456 7890</p><p>Address: 123 Hope Street, Kindness City</p></div>
      <div class="footer-section social"><h3>Follow Us</h3><div class="social-icons"><a href="#"><i class="fa-brands fa-square-facebook"></i></a><a href="#"><i class="fa-brands fa-instagram"></i></a><a href="#"><i class="fa-brands fa-twitter"></i></a></div></div>
    </div>
    <div class="footer-bottom">© 2025 VolNet | Made with ❤️ for a better tomorrow</div>
</footer>
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>



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

</body>
</html>