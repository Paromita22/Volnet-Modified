<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}


if (isset($_GET['status']) && $_GET['status'] === 'applied_successfully') {
    echo "<div class='alert alert-success'>Application submitted successfully!</div>";
} elseif (isset($_GET['error']) && $_GET['error'] === 'already_applied') {
    echo "<div class='alert alert-warning'>You have already applied to this event.</div>";
}


require_once __DIR__ . '/../includes/db.php'; // External DB connection

// Fetch counts
$volunteers = $conn->query("SELECT COUNT(*) AS total FROM volunteer")->fetch_assoc()['total'] ?? 0;
$organizations = $conn->query("SELECT COUNT(*) AS total FROM organization")->fetch_assoc()['total'] ?? 0;
$admins = $conn->query("SELECT COUNT(*) AS total FROM admin")->fetch_assoc()['total'] ?? 0;
$events = $conn->query("SELECT COUNT(*) AS total FROM eventt")->fetch_assoc()['total'] ?? 0;

// Get current user ID and role
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text'], $_POST['rating'], $_POST['reviewee_email'])) {
    $review_text = trim($_POST['review_text']);
    $rating = (int)$_POST['rating'];
    $reviewee_email = trim($_POST['reviewee_email']);

    // Lookup user by email
    $stmt = $conn->prepare("SELECT user_id, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $reviewee_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error_message = "No user found with that email.";
    } else {
        $user = $result->fetch_assoc();
        $reviewee_user_id = $user['user_id'];
        $reviewee_role = $user['role'];

        // Optional: ensure role is opposite
        $expected_role = ($current_user_role === 'volunteer') ? 'organization' : 'volunteer';
        if ($reviewee_role !== $expected_role) {
            $error_message = "You can only review users with the role: $expected_role.";
        } elseif ($current_user_id && $review_text && $rating >= 1 && $rating <= 5) {
            $insert = $conn->prepare("INSERT INTO reviews (reviewer_user_id, reviewer_role, reviewee_user_id, reviewee_role, review_text, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $insert->bind_param("isissi", $current_user_id, $current_user_role, $reviewee_user_id, $reviewee_role, $review_text, $rating);
            if ($insert->execute()) {
                $success_message = "Review submitted successfully.";
            } else {
                $error_message = "Error submitting review.";
            }
            $insert->close();
        } else {
            $error_message = "Invalid form submission.";
        }
    }
    $stmt->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/vol-home.css">
  <title>Volunteer Homepage</title>
</head>
<body>
  <span class="menu-icon" onclick="toggleSidebar()">&#9776;</span>

  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/volunteer/dashboard.php" style="margin-left: 80px;">VolNet</a>
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
           <button class="btn btn-dark" id="logout" onclick="window.location.href='/pages/homepage.php'">Log out</button>
          
        </div>
        <div class="d-flex align-items-center ms-3">
          <a href="/volunteer/profile.php" class="profile-icon" title="Your Account">
            <i class="fas fa-user-circle fa-lg"></i>
          </a>
        </div>
        
      </div>

    </nav>
  </header>
  
  <aside class="sidebar" id="sidebar">
    <a href="/volunteer/request_certificate.php">Request Certificate</a>
    <a href="/volunteer/applications.php">Application Tracking</a>
    <a href="/pages/projects.php">Explore Events</a>
    <a href="/volunteer/history.php">History</a>
    <a href="/volunteer/write_review.php" >Submit Review</a>
      <a href="/admin/organizations.php">View Organizations</a>
       <a href="/volunteer/submit_complaint.php">Submit Complaint</a>

    <a href="/auth/logout.php">Logout</a>
  </aside>


  <!-- Review Form Section
<div id="reviewFormContainer" style="display: none; padding: 20px; background-color: #f9f9f9;">
  <h3>Submit Your Review</h3>
  <form action="/volunteer/submit_review.php" method="POST">

  
    <div class="mb-3">
      <label for="reviewee_user_id" class="form-label">Select User to Review</label>
      <div class="mb-3">
  <label for="reviewee_email" class="form-label">Enter Email of User to Review</label>
  <input type="email" name="reviewee_email" class="form-control" placeholder="example@email.com" required>
</div>

    </div>




  
    <div class="mb-3">
      <label for="review_text" class="form-label">Review</label>
      <textarea name="review_text" class="form-control" rows="4" required placeholder="Write your review..."></textarea>
    </div>

   
    <div class="mb-3">
      <label for="rating" class="form-label">Rating (1–5)</label>
      <input type="number" name="rating" class="form-control" min="1" max="5" required>
    </div>

    <button type="submit" class="btn btn-primary">Submit Review</button>
  </form>
</div>
-->


    <section class="hero">
      <img src="https://media.licdn.com/dms/image/v2/D5612AQG9kyNA_gJKQA/article-cover_image-shrink_720_1280/article-cover_image-shrink_720_1280/0/1654793377068?e=2147483647&v=beta&t=keVgSKO9g8CrVokuK12RNbBVsZ7FmkH7ATSoAzZQfeg" alt="Hero Background" class="hero-bg-img">
      <div class="hero-content">
        <h1>Make a difference in your community</h1>
        <button class="apply-btn" style="background-color: rgb(226, 93, 45);" onclick="toggleSidebar()">Explore</button>
      </div>
    </section>

   

 
<section class="urgent-needs" style="margin-top: 50px; margin-left: 60px;">
 <h2>Urgent Needs</h2>
  
    <?php

$applied_events = [];

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'volunteer') {
    $user_id = $_SESSION['user_id'];

    // Fetch volunteer_id from user_id
    $stmt = $conn->prepare("SELECT volunteer_id FROM volunteer WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $volunteer_id = $row['volunteer_id'];

        // Get event IDs already applied by this volunteer
        $applied_sql = "SELECT event_id FROM applications WHERE volunteer_id = " . (int)$volunteer_id;
        $applied_result = $conn->query($applied_sql);
        if ($applied_result) {
            while ($applied_row = $applied_result->fetch_assoc()) {
                $applied_events[] = $applied_row['event_id'];
            }
        }
    }
    $stmt->close();
}


// Get urgent events
$sql = "SELECT * FROM eventt WHERE is_urgent = 1 ORDER BY created_at DESC";
$result = $conn->query($sql);

// Display cards
if ($result && $result->num_rows > 0) {
    echo '<div class="container">';
    echo '<div class="row">';
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-lg-6">';
  echo '<div class="event-card">';
    echo '<div class="event-card-header">';
      echo '<h5 class="mb-0">' . htmlspecialchars($row['title']) . '</h5>';
    echo '</div>';

    echo '<div class="event-card-body" style="color:white; background-color: black;">';
      echo '<p>' . htmlspecialchars($row['description']) . '</p>';
      echo '<hr>';
      echo '<div class="details-grid">';
        echo '<div class="detail-item"><i class="fas fa-calendar-alt"></i> <strong>Date:</strong> ' . date("M j, Y", strtotime($row['start_date'])) . ' - ' . date("M j, Y", strtotime($row['end_date'])) . '</div>';
        echo '<div class="detail-item"><i class="fas fa-clock"></i> <strong>Time:</strong> ' . date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time'])) . '</div>';
        echo '<div class="detail-item"><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</div>';
        echo '<div class="detail-item"><i class="fas fa-users"></i> <strong>Volunteers Needed:</strong> ' . $row['volunteers'] . '</div>';
        echo '<div class="detail-item"><i class="fas fa-exclamation-circle"></i> <strong>Age:</strong> ' . htmlspecialchars($row['age_restriction']) . '</div>';
        echo '<div class="detail-item"><i class="fas fa-venus-mars"></i> <strong>Gender:</strong> ' . htmlspecialchars($row['gender']) . '</div>';
        echo '<div class="detail-item"><i class="fas fa-tools"></i> <strong>Skills:</strong> ' . htmlspecialchars($row['skills']) . '</div>';
        echo '<div class="detail-item"><i class="fas fa-phone"></i> <strong>Contact:</strong> ' . htmlspecialchars($row['contact']) . '</div>';
      echo '</div>';
    echo '</div>';

    echo '<div class="event-card-footer" style="background-color: black;">';
      if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer') {
          if (in_array($row['id'], $applied_events)) {
              echo '<button type="button" class="btn btn-secondary" disabled><i class="fas fa-check-circle"></i> Applied</button>';
          } else {
              echo '<button type="button" class="btn btn-success apply-now-btn" data-event-id="' . $row['id'] . '"><i class="fas fa-paper-plane"></i> Apply Now</button>';
          }
      } elseif (!isset($_SESSION['role'])) {
          echo '<a href="/auth/login.html" class="btn btn-primary">Login to Apply</a>';
      }
    echo '</div>';
  echo '</div>';
echo '</div>';

    }
    echo '</div></div>';
} else {
    echo "<p class='text-white'>No urgent needs at the moment.</p>";
}

?>


</section>
<br><br>



<section class="statistics">
    <h2>Our Impact</h2>
    <div class="stats-grid">
        <div class="stat-item">
            <h5>Volunteers</h5>
            <p><?php echo $volunteers; ?></p>
        </div>
        <div class="stat-item">
            <h5>Organizations</h5>
            <p><?php echo $organizations; ?></p>
        </div>
        <div class="stat-item">
            <h5>Admins</h5>
            <p><?php echo $admins; ?></p>
        </div>
        <div class="stat-item">
            <h5>Events</h5>
            <p><?php echo $events; ?></p>
        </div>
    </div>
</section>





    
      <section class="info-video">
        <div class="video-box">
          <iframe src="https://www.youtube.com/embed/2szQhR4oZtA?si=201pIkUjdk5xH6LQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
        
        <div class="description">
          <h3>✨ Be the Change You Wish to See</h3>
          <p>Join hands with us to make a real difference.</p>
          <p> Every great movement begins with one choice — the decision to care, to show up, and to act. Whether you have an hour or a day, your time and energy can spark change in someone’s life. When you volunteer, you’re not just giving back — you’re building a better world, one act of kindness at a time.</p>
          <p><strong> Ready to create impact?</strong>
            Be the reason someone believes in good today.</p>
        </div>
      </section>

    <section class="certificate">
    <div class="certi-container">
  <h2>Certifications & Badges</h2>
  <div class="badge-container">
 
  <img src="https://vspot.s3.amazonaws.com/sign-up/printables/Vol+Appreciation+Cert+1.png" alt="Volunteer Certificate" class="certificate-img-thumbnail" onclick="openFullscreen(this)">
    
    <img src="/uploads/badges/gold-badge.png" alt="Gold Volunteer Badge" class="badge-img" onclick="openFullscreen(this)">
<img src="/uploads/badges/silver-badge.png" alt="Silver Volunteer Badge" class="badge-img" onclick="openFullscreen(this)">
<img src="/uploads/badges/bronze-badge.png" alt="Bronze Volunteer Badge" class="badge-img" onclick="openFullscreen(this)">
   
     <img src="https://i.pinimg.com/736x/0a/91/8a/0a918aeee434428bfc419483d947f0e7.jpg" alt="Volunteer Certificate" class="certificate-img-thumbnail" onclick="openFullscreen(this)">
  
  </div>
</div>
  </section>


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
      



  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }
  </script>

  
<div id="fullscreenModal" onclick="this.style.display='none'">
  <img id="fullscreenImg" src="" alt="Full Size Certificate">
</div>

<!--<script>
  function openFullscreen(img) {
    const modal = document.getElementById('fullscreenModal');
    const modalImg = document.getElementById('fullscreenImg');
    modalImg.src = img.src;
    modal.style.display = 'block';
  }
</script>-->


<script>
  function openFullscreen(imgElement) {
    const fullscreenModal = document.getElementById('fullscreenModal');
    const fullscreenImg = document.getElementById('fullscreenImg');
    fullscreenImg.src = imgElement.src;
    fullscreenModal.style.display = 'flex'; // Use flex to center the image
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
