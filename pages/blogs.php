<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Fetch blogs
$sql = "SELECT b.*, u.username FROM blogs b JOIN users u ON b.user_id = u.user_id ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Blogs</title>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel="stylesheet" href="/assets/css/blogs.css" />
  <style>
    /* Body and layout */
    html, body {
      height: 100%;
      margin: 0;
      background-color: black;
      color: white;
    }
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main {
      flex: 1;
      padding-top: 20px;
      padding-bottom: 120px; /* space to avoid footer overlap */
    }

    /* Header with green background #6db02e */
    .custom-header {
      background-color: #6db02e;
    }
    .custom-header .navbar-brand,
    .custom-header .nav-link {
      color: white;
    }
    .custom-header .nav-link.active {
      font-weight: bold;
      text-decoration: underline;
    }
    .custom-header .navbar-toggler {
      border: none;
    }

    /* Footer same dark style */
    footer.volunteer-footer {
      background-color: #222;
      color: #eee;
      padding: 20px 0;
      font-size: 14px;
    }
    .footer-content {
      max-width: 1140px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: space-between;
    }
    .footer-section {
      flex: 1 1 200px;
    }
    .footer-section h3 {
      margin-bottom: 15px;
      color: #eee;
      font-size: 1.25rem;
    }
    .footer-section ul {
      list-style: none;
      padding: 0;
    }
    .footer-section ul li {
      margin-bottom: 8px;
    }
    .footer-section ul li a {
      color: #eee;
      text-decoration: none;
    }
    .footer-section ul li a:hover {
      text-decoration: underline;
    }
    .social-icons a {
      color: #eee;
      margin-right: 15px;
      font-size: 1.4rem;
      transition: color 0.3s;
    }
    .social-icons a:hover {
      color: #0d6efd;
    }
    .footer-bottom {
      text-align: center;
      padding-top: 10px;
      font-size: 0.9rem;
      border-top: 1px solid #444;
      margin-top: 20px;
    }

    /* Blog cards */
    .card {
      background-color: #111;
      border: 1px solid #444;
      color: white;
    }
    .card .card-title {
      color: white;
    }
    .card .btn-primary {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }
    .card .btn-primary:hover {
      background-color: #084298;
      border-color: #084298;
    }
    .card-img-top {
      height: 180px;
      object-fit: cover;
    }
  </style>
</head>
<body>

<!-- Header -->
<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
    <a class="navbar-brand fw-bold text-white" href="/pages/homepage.php">VolNet</a>
    <button
      class="navbar-toggler bg-light"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#navbarContent"
      aria-controls="navbarContent"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
        <li class="nav-item"><a class="nav-link" href="/pages/homepage.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
        <li class="nav-item"><a class="nav-link active" href="/pages/blogs.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/contactus.php">Contact</a></li>
      </ul>
      <form class="d-flex me-3" role="search" action="/pages/search.php" method="GET">
        <input class="form-control me-2" type="search" name="q" placeholder="Search..." aria-label="Search" required>
        <button class="btn btn-outline-dark bg-white" type="submit"><i class="fa fa-search"></i></button>
      </form>
      <div class="d-flex align-items-center gap-3">
        <?php if (isset($_SESSION['user_id'])): ?>
          <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
          <a href="/auth/logout.php" class="btn btn-outline-light">Logout</a>
        <?php else: ?>
          <button class="btn btn-dark" onclick="window.location.href='/auth/login.html'">Sign In</button>
          <button class="btn btn-dark" onclick="window.location.href='/auth/register.html'">Sign Up</button>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>

<!-- Main Blog Section -->
<main class="container my-5">
  <h1 class="text-center mb-4">VolNet Community Blogs</h1>

  <!-- Write Blog Button -->
  <div class="d-flex justify-content-end mb-3">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer'): ?>
      <a href="/volunteer/write_blog.php" class="btn btn-success">Write Blog</a>
    <?php endif; ?>
  </div>

  <!-- Blog Cards Grid -->
  <div class="container mt-4">
    <div class="row g-4">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php 
            $blog_img = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : '/assets/images/hero_bg.jpg';
          ?>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-secondary" style="background-color: #1e1e1e;">
              <img src="<?php echo $blog_img; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['title']); ?>" style="height: 180px; object-fit: cover;" />
              <div class="card-body text-center d-flex flex-column justify-content-between">
                <h5 class="card-title text-white"><?php echo htmlspecialchars($row['title']); ?></h5>
                <a href="/pages/blog_details.php?id=<?php echo $row['blog_id']; ?>" class="btn btn-primary mt-2">View Details</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center">No blogs available.</p>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Footer -->
<footer class="volunteer-footer mt-5">
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
</body>
</html>
