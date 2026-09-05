<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$home_url = '/pages/homepage.php'; // Default URL for users who are not logged in

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'volunteer':
            $home_url = '/volunteer/dashboard.php';
            break;
        case 'organization':
            $home_url = '/organization/dashboard.php';
            break;
        case 'admin':
            $home_url = '/admin/dashboard.php';
            break;
    }
}



$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';
if (!$type) {
    // It's better to redirect or show a proper message within the layout
    // For now, we'll exit, but integrating this into the main body is ideal.
    echo "<h2>No event type selected.</h2>"; exit;
}

// --- NEW LOGIC: Check which events the user has already applied for ---
$applied_events = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer') {
    // First, get volunteer_id from user_id
    $stmt_vol = $conn->prepare("SELECT volunteer_id FROM volunteer WHERE user_id = ?");
    $stmt_vol->bind_param("i", $_SESSION['user_id']);
    $stmt_vol->execute();
    $result_vol = $stmt_vol->get_result();
    if($volunteer_data = $result_vol->fetch_assoc()) {
        $volunteer_id = $volunteer_data['volunteer_id'];
        
        // Now, get all event_ids they've applied to
        $stmt_app = $conn->prepare("SELECT event_id FROM applications WHERE volunteer_id = ?");
        $stmt_app->bind_param("i", $volunteer_id);
        $stmt_app->execute();
        $result_app = $stmt_app->get_result();
        while ($row_app = $result_app->fetch_assoc()) {
            $applied_events[] = $row_app['event_id'];
        }
        $stmt_app->close();
    }
    $stmt_vol->close();
}
// --- END NEW LOGIC ---

$sql = "SELECT * FROM eventt WHERE type = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <title><?php echo htmlspecialchars($type); ?> Events - VolNet</title>
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
  <style>
    /* 
     *  ============== FIXES & PAGE STYLES ==============
     */

    /* 1. FIX BACKGROUND COLOR & TEXT COLOR */
    body { 
        background-color:rgb(0, 0, 0); /* This ensures the light grey background */
    
    }
    .main-content {
      padding: 20px;
      margin-left: 0;
      transition: margin-left 0.3s ease;
      color: #333; /* This ensures text in the main area is dark */
    }
    .sidebar.active ~ .main-content { 
      margin-left: 250px; 
    }
    
    /* Card Styles */
    .event-card {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        color: #333; /* Ensures text inside the card is dark */
    }
    .event-card-header {
        background-color: #449f1a; /* Using a green from your theme */
        color: white; /* Header text remains white */
        padding: 15px 20px;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .event-card-body { 
        padding: 20px; 
        flex-grow: 1; 
    }
    .event-card-footer {
        background-color: #f8f9fa;
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        text-align: right;
    }
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .detail-item { 
        font-size: 0.9rem; 
    }
    .detail-item i { 
        color: #449f1a;
        margin-right: 8px; 
        width: 20px; 
        text-align: center;
    }

    /* 2. MAKE FOOTER SMALLER */
    .volunteer-footer {
        padding: 25px 20px 15px; /* Reduced vertical padding */
    }
    .footer-section h3 {
        font-size: 1rem; /* Slightly smaller heading */
        margin-bottom: 10px;
    }
    .footer-section p,
    .footer-section ul li a {
        font-size: 0.85rem; /* Smaller paragraph/link text (~13.6px) */
    }
    .footer-section ul li {
        margin: 5px 0; /* Tighter list item spacing */
    }
    .social-icons i {
        font-size: 20px; /* Smaller social icons */
    }
    .footer-bottom {
        font-size: 0.8rem; /* Smaller copyright text (~12.8px) */
        margin-top: 20px; /* Reduced top margin */
        padding-top: 15px;
    }
    #ce{
      color:rgb(255, 255, 255);
    }
      .mt-5{
      color:rgb(255, 255, 255);
    }
      .mb-4{
      color:rgb(255, 255, 255);
    }
  </style>
</head>
<body>

<!-- Your Provided Header -->

<header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/pages/homepage.php" style="margin-left: 25px;">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"><i class="fa-solid fa-bars"></i></button>
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
            <li class="nav-item">
            <!--<a class="nav-link" href="/pages/homepage.php">Home</a>-->
            <a class="nav-link" href="<?php echo $home_url; ?>">Home</a>
          </li>
          <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="/pages/blogs.php">Blog</a></li>
          <li class="nav-item"><a class="nav-link" href="/pages/contactus.php">Contact</a></li>
        </ul>
        <div class="d-flex align-items-center gap-3">
          <?php if (isset($_SESSION['user_id'])): ?>
            <span class="text-white">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="/auth/logout.php" class="btn btn-outline-light">Logout</a>
            <a href="/volunteer/profile.php" class="profile-icon" title="Your Account"><i class="fas fa-user-circle fa-lg text-white"></i></a>
          <?php else: ?>
            <button class="btn btn-dark" onclick="window.location.href='/auth/login.html'">Sign In</button>
            <button class="btn btn-dark" onclick="window.location.href='/auth/register.html'">Sign Up</button>
          <?php endif; ?>
        </div>
      </div>
    </nav>
</header>



<!-- Main Content Area -->
<div class="main-content">
<div class="container-fluid">
  <h2 class="mb-4">Events for: <?php echo htmlspecialchars($type); ?></h2>

  <?php
  $past_events = $current_events = $upcoming_events = [];
  $current_date = date("Y-m-d");

  while ($row = $result->fetch_assoc()) {
      if ($row['end_date'] < $current_date) {
          $past_events[] = $row;
      } elseif ($row['start_date'] <= $current_date && $row['end_date'] >= $current_date) {
          $current_events[] = $row;
      } else {
          $upcoming_events[] = $row;
      }
  }

  function render_event_cards($events, $applied_events) {
      if (count($events) === 0) {
          echo '<div class="alert alert-warning">No events found in this category.</div>';
          return;
      }

      echo '<div class="row">';
      foreach ($events as $row):
  ?>
    <div class="col-lg-6">
      <div class="event-card">
        <div class="event-card-header">
          <h5 class="mb-0"><?php echo htmlspecialchars($row['title']); ?></h5>
        </div>
        <div class="event-card-body" style="color:white; background-color: black;">
          <p><?php echo htmlspecialchars($row['description']); ?></p>
          <hr>
          <div class="details-grid">
            <div class="detail-item"><i class="fas fa-calendar-alt"></i> <strong>Date:</strong> <?php echo date("M j, Y", strtotime($row['start_date'])); ?> - <?php echo date("M j, Y", strtotime($row['end_date'])); ?></div>
            <div class="detail-item"><i class="fas fa-clock"></i> <strong>Time:</strong> <?php echo date("g:i A", strtotime($row['start_time'])); ?> - <?php echo date("g:i A", strtotime($row['end_time'])); ?></div>
            <div class="detail-item"><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></div>
            <div class="detail-item"><i class="fas fa-users"></i> <strong>Volunteers Needed:</strong> <?php echo $row['volunteers']; ?></div>
            <div class="detail-item"><i class="fas fa-exclamation-circle"></i> <strong>Age:</strong> <?php echo htmlspecialchars($row['age_restriction']); ?></div>
            <div class="detail-item"><i class="fas fa-venus-mars"></i> <strong>Gender:</strong> <?php echo htmlspecialchars($row['gender']); ?></div>
            <div class="detail-item"><i class="fas fa-tools"></i> <strong>Skills:</strong> <?php echo htmlspecialchars($row['skills']); ?></div>
            <div class="detail-item"><i class="fas fa-phone"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></div>
          </div>
        </div>
        <div class="event-card-footer" style="background-color: black;">
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer'): ?>
            <?php if (in_array($row['id'], $applied_events)): ?>
              <button type="button" class="btn btn-secondary" disabled><i class="fas fa-check-circle"></i> Applied</button>
            <?php else: ?>
              <button type="button" class="btn btn-success apply-now-btn" data-event-id="<?php echo $row['id']; ?>"><i class="fas fa-paper-plane"></i> Apply Now</button>
            <?php endif; ?>
          <?php elseif (!isset($_SESSION['role'])): ?>
            <a href="/auth/login.html" class="btn btn-primary">Login to Apply</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; echo '</div>'; } ?>

  <h3 class="mt-4" id="ce">🟢 Current Events</h3>
  <?php render_event_cards($current_events, $applied_events); ?>

  <h3 class="mt-5">🔜 Upcoming Events</h3>
  <?php render_event_cards($upcoming_events, $applied_events); ?>

  <h3 class="mt-5">📅 Past Events</h3>
  <?php render_event_cards($past_events, $applied_events); ?>
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
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.querySelector('.main-content');
      sidebar.classList.toggle('active');
      mainContent.style.marginLeft = sidebar.classList.contains('active') ? '250px' : '0';
    }
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.apply-now-btn').forEach(button => {
        button.addEventListener('click', function () {
            const eventId = this.getAttribute('data-event-id');
            const btn = this;

            fetch('apply.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'event_id=' + encodeURIComponent(eventId)
            })
            .then(response => response.text())
            .then(text => {
                if (text.includes("applied_successfully")) {
                    btn.classList.remove("btn-success");
                    btn.classList.add("btn-secondary");
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Applied';
                    btn.disabled = true;
                } else if (text.includes("already_applied")) {
                    btn.classList.remove("btn-success");
                    btn.classList.add("btn-secondary");
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Already Applied';
                    btn.disabled = true;
                } else {
                    alert("Unexpected response: " + text);
                }
            })
            .catch(err => alert("Error applying: " + err));
        });
    });
});
</script>



</body>
</html>
<?php
$stmt->close();
$conn->close();
?>