<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}
require_once __DIR__ . '/../includes/db.php';

// Fetch counts from your tables
$volunteers = $conn->query("SELECT COUNT(*) AS total FROM volunteer")->fetch_assoc()['total'] ?? 0;
$organizations = $conn->query("SELECT COUNT(*) AS total FROM organization")->fetch_assoc()['total'] ?? 0;
$admins = $conn->query("SELECT COUNT(*) AS total FROM admin")->fetch_assoc()['total'] ?? 0;
$events = $conn->query("SELECT COUNT(*) AS total FROM eventt")->fetch_assoc()['total'] ?? 0;

// Close connection (optional, but good practice)
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <title>Org Home</title>
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
</head>
<body>

<span class="menu-icon" onclick="toggleSidebar()">&#9776;</span>




   
  
  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/organization/dashboard.php" style="margin-left: 25px;">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
      </button>
  
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item">
            <a class="nav-link" href="/organization/dashboard.php">Home</a>
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
          <a href="/organization/profile.php" class="profile-icon" title="Your Account">
            <i class="fas fa-user-circle fa-lg"></i>
          </a>
        </div>
        
      </div>
    </nav>
  </header>

  


  <!--header tag er niche add korbi:-->

  <aside class="sidebar" id="sidebar">
    <a href="/organization/create_event.php">Create Event</a>
    <a href="/organization/history.php">History</a>
    <a href="/admin/volunteers.php">Available Volunters</a>
    <a href="/organization/view_applicants.php">View Applications</a>
        <a href="/organization/manage_events.php">Manage Event Attendance</a>
    <a href="/volunteer/write_review.php" >Submit Review</a>
     <a href="/volunteer/submit_complaint.php">Submit Complaint</a>
    <a href="/pages/homepage.php">Logout</a>


     
    
  </aside>


  <!-- 
  <div id="reviewFormContainer" style="display: none; padding: 20px; background-color: #f9f9f9; color: black;">

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
    <img src="/assets/images/hero_bg.jpg" alt="Hero Background" class="hero-bg-img">
    <div class="hero-content">
      <h1>Make a difference in your community</h1>
      <button class="apply-btn" id="explore-btn">Explore</button>
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

  <section class="sample-projects">
    <div class="sample-projects-heading">
      <h2>Sample Projects</h2>
    </div>
    <div class="row">
      <div class="col-sm-3">
        <div id="first-card" class="card" style="width: 18rem;">
          <img src="https://ehospice.com/wp-content/uploads/2021/02/Volunteer-Mahmuda-Akter-Panna-is-talking-with-patient-Kulsum.jpg" class="card-img-top" alt="Project Image">
          <div class="card-body">
            <h5 class="card-title">Elderly Care</h5>
            
            <a href="/pages/view_events.php?type=Elderly%20Care" class="btn btn-primary">View Events</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card" style="width: 18rem;">
          <img src="https://unboundproject.org/rubaiya-ahmad/rubaiyas-team/" class="card-img-top" alt="Project Image">
          <div class="card-body">
            <h5 class="card-title">Animal Care</h5>
            
            <a href="/pages/view_events.php?type=Animal%20Care" class="btn btn-primary">View Events</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card" style="width: 18rem;">
          <img src="https://www.vsointernational.org/sites/default/files/styles/600x400/public/2020-02/Bangladesh_volunteer%20supervisor%20Muslima%20Zannat%20at%20safe%20space%20education%20centre_Rohingya%20camp_RS65541.JPG?h=56d0ca2e&itok=KyMR15LL" class="card-img-top" alt="Project Image">
          <div class="card-body">
            <h5 class="card-title">Child Care</h5>
           
            <a href="/pages/view_events.php?type=Child%20Care" class="btn btn-primary">View Events</a>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card" style="width: 18rem;">
          <img src="https://www.arabnews.com/sites/default/files/styles/n_670_395/public/2024/08/31/4519250-1590338605.jpg?itok=4sa2hoCX" class="card-img-top" alt="Project Image">
          <div class="card-body">
            <h5 class="card-title">Climate Action</h5>
            
            <a href="/pages/view_events.php?type=climate%20Action" class="btn btn-primary">View Events</a>
          </div>
        </div>
      </div>
    </div>  
    <div class="sample-projects-see-more">
    <a href="/pages/projects.php" class="btn btn-primary">See All</a>
    </div>
  </section>



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





  
<section class="inspiration">
    <h2>Inspirational Volunteering Stories</h2>
    <div class="row">
      <div class="col-sm-6">
        <img src="https://i0.wp.com/obhizatrik.org/wp-content/uploads/2024/07/IMG-20240702-WA0014-1.jpg?fit=1600%2C1201&ssl=1" alt="Story 1" class="story-img">
      </div>
  
      <div class="col-sm-6">
        <p>🌿 <b>Growing Together</b> <br>
          On a sunny morning, a group of volunteers gathered at the local park to breathe life back into the land. Among them was Daniel, volunteering for the first time. As he dug into the soil and planted young trees alongside others, he realized something beautiful — it wasn't just about greenery, it was about community. Strangers became teammates, laughter filled the air, and with each sapling, they planted hope for a greener future.</p>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <img src="https://newseu.cgtn.com/news/2023-11-09/The-Gazan-charity-helping-animals-on-the-frontline-1ozI9cBogQo/img/5f37273f69394674b3f2a748a86c35c6/5f37273f69394674b3f2a748a86c35c6.png">
      </div>
  
      <div class="col-sm-6">
        <p>🐾 <b>A Friend Named Max </b><br>
          When Atik arrived at the animal shelter, he was paired with Max — a timid rescue dog who had lost trust in people. Sitting quietly on the grass, he let Max approach in his own time. A gentle pat turned into tail wags, and before long, Max was leaning into him with comfort. In that moment, Atik felt it — the incredible power of patience, kindness, and showing up. He didn’t just help a dog; he gave him a reason to trust again.
          </p>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <img src="https://ehospice.com/wp-content/uploads/2021/02/Volunteer-Mahmuda-Akter-Panna-is-talking-with-patient-Kulsum.jpg" alt="Story 1" class="story-img">
      </div>
  
      <div class="col-sm-6">
        <p>🌟 <b>Hearts in Harmony</b> <br>
          In a quiet village, a group of volunteers came together to bring comfort and companionship to the elderly. Among them was Aisha, a young volunteer with a heart full of compassion. As she spent time reading stories, sharing meals, and simply listening, she saw smiles return to faces that had long forgotten joy. One elderly woman, Maria, whispered, “You’ve brought sunshine into our days.” It was in those simple, human moments that Aisha realized the power of presence — that sometimes, just being there is the greatest gift of all.</p>
      </div>
    </div>
     
    <div class="sample-projects-see-more">
      <a href="#" class="btn btn-primary">See More...</a>
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




</div>



<div class="how-to-volunteer-container">
    <h2 id="howtoh2">Step by step guide</h2>
    <div class="steps-wrapper">
        <div class="step-item">
            <div class="step-circle">1</div>
            <div class="step-content">
                <h3>Step 1: Sign Up & Create Your Profile</h3>
                <p>Register on VolNet to get started. Tell us about your projects, requirements so we can match you with the perfect candidates.</p>
            </div>
        </div>
        <div class="step-line"></div>

        <div class="step-item">
            <div class="step-circle">2</div>
            <div class="step-content">
                <h3>Step 2: Post needs and projects to find volunteers</h3>
                <p>Create events and post about your projects' details and volunteers would apply</p>
            </div>
        </div>
        <div class="step-line"></div>

        <div class="step-item">
            <div class="step-circle">3</div>
            <div class="step-content">
                <h3>Step 3: Choose from several candidates</h3>
                <p>Once you receive applications, you can view deatiled profiles, choose and accept for your preferred candidates</p>
            </div>
        </div>
        <div class="step-line"></div>

        <div class="step-item">
            <div class="step-circle">4</div>
            <div class="step-content">
                <h3>Step 4: Track Your Impact </h3>
                <p>Monitor your projects and your volunteers' work and make an impact</p>
            </div>
        </div>
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
    }
     document.addEventListener('DOMContentLoaded', function () {
    const exploreBtn = document.getElementById('explore-btn');
    exploreBtn.addEventListener('click', toggleSidebar);
  });
  </script>

<script>
  // Add user account icon
  document.addEventListener("DOMContentLoaded", () => {
    const accIcon = document.createElement("a");
    accIcon.href = "/organization/profile.php";
    accIcon.innerHTML = `<i class="fas fa-user-circle fa-2x" style="color: black;"></i>`;
    accIcon.style.marginLeft = "15px";
    document.querySelector(".auth").appendChild(accIcon);
  });

  // Your existing scripts
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
  }
  
  document.addEventListener('DOMContentLoaded', function () {
    const exploreBtn = document.getElementById('explore-btn');
    exploreBtn.addEventListener('click', toggleSidebar);
  });
</script>
<!--
<script>
  function toggleReviewForm() {
    const form = document.getElementById('reviewFormContainer');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  }
</script>
-->
</body>
</html>
