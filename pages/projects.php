<?php session_start(); 
// This block determines the correct homepage URL based on the user's role.
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <title>Projects Page</title>
  <link rel="stylesheet" href="/assets/css/projects.css">
</head>
<body>

  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <!--<a class="navbar-brand fw-bold text-white" href="/pages/homepage.php">VolNet</a>-->
      <a class="navbar-brand fw-bold text-white" href="<?php echo $home_url; ?>">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
      </button>
  <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item">
            <!--<a class="nav-link" href="/pages/homepage.php">Home</a>-->
            <a class="nav-link" href="<?php echo $home_url; ?>">Home</a>
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
  
        <div class="d-flex align-items-center gap-3">
  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- User is LOGGED IN -->
    <span class="text-white">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
    <a href="/auth/logout.php" class="btn btn-outline-light">Logout</a>
  <?php else: ?>
    <!-- User is NOT LOGGED IN -->
    <button class="btn btn-dark" id="sign-in" onclick="window.location.href='/auth/login.html'">Sign In</button>
    <button class="btn btn-dark" id="sign-up"  onclick="window.location.href='/auth/register.html'">Sign Up</button>
  <?php endif; ?>
</div>
      </div>
    </nav>
  </header>
  
  

<div class="container">
  
  <div class="row g-4 justify-content-center">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div id="first-card" class="card" style="width: 18rem;">
        <img src="https://unboundproject.org/rubaiya-ahmad/rubaiyas-team/?auto=format,compress" class="card-img-top" alt="Project Image">
        <div class="card-body">
          <h5 class="card-title">Animal Care</h5>
          <a href="/pages/view_events.php?type=Animal%20Care" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card" style="width: 18rem;">
        <img src="https://www.arabnews.com/sites/default/files/styles/n_670_395/public/2024/08/31/4519250-1590338605.jpg?itok=4sa2hoCX?auto=format,compress" class="card-img-top" alt="Project Image">
        <div class="card-body">
          <h5 class="card-title">Climate action</h5>
          <a href="/pages/view_events.php?type=climate%20Action" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card" style="width: 18rem;">
        <img src="https://ehospice.com/wp-content/uploads/2021/02/Volunteer-Mahmuda-Akter-Panna-is-talking-with-patient-Kulsum.jpg?auto=format,compress" class="card-img-top" alt="Project Image">
        <div class="card-body">
          <h5 class="card-title">Elderly Care</h5>
          <a href="/pages/view_events.php?type=Elderly%20Care" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card" style="width: 18rem;">
        <img src="https://ivhq.imgix.net/images/projects/india-kerala/volunteer-abroad-in-india-ivhq-kerala-womens-empowerment.jpg?auto=format,compress" class="card-img-top" alt="Project Image">
        <div class="card-body">
          <h5 class="card-title">Women Empowerment</h5>
          <a href="/pages/view_events.php?type=Women%20Empowerment" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div id="first-card" class="card" style="width: 18rem;">
        <img src="https://www.vsointernational.org/sites/default/files/styles/600x400/public/2020-02/Bangladesh_volunteer%20supervisor%20Muslima%20Zannat%20at%20safe%20space%20education%20centre_Rohingya%20camp_RS65541.JPG?h=56d0ca2e&itok=KyMR15LL" class="card-img-top" alt="Project Image">
        <div class="card-body">
          <h5 class="card-title">Child Care</h5>
          <a href="/pages/view_events.php?type=Child%20Care" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card" style="width: 18rem;">
        <img src="https://www.pathwaybd.org/images/physically-challenged/Physically_Challenged-pathway_3.jpg?auto=format,compress" class="card-img-top" alt="Project Image">
        <div class="card-body">
          <h5 class="card-title">Special needs Care</h5>
          <a href="/pages/view_events.php?type=Special%20Needs%20Care" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
        <!-- START: New card for "Others" category -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card" style="width: 18rem;">
        <img src="https://www.caringnetwork.com/wp-content/uploads/2023/01/10-Benefits-of-Volunteering-Your-Time-Regularly-2880w.webp" class="card-img-top" alt="Other Projects">
        <div class="card-body">
          <h5 class="card-title">Others</h5>
          <a href="/pages/view_events.php?type=Others" class="btn btn-primary">View Events</a>
        </div>
      </div>
    </div>
    <!-- END: New card for "Others" category -->
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
</body>
</html>