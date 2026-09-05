<?php
session_start();
include __DIR__ . '/../includes/db.php';

require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    $image_path = '';
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $uploadResult = upload_file_safe($_FILES['image'], 'blogs');
        if ($uploadResult['success']) {
            $image_path = '/' . $uploadResult['path'];
        }
    }

    $stmt = $conn->prepare(
        "INSERT INTO blogs (user_id, title, image_path, content, created_at) VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("isss", $user_id, $title, $image_path, $content);
    $stmt->execute();
    $stmt->close();

    header("Location: /pages/blogs.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Write Blog</title>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
    }
    body {
      background-color: black;
      color: white;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    main {
      flex: 1;
      padding: 2rem 1rem;
    }

    .custom-header {
      background-color: #6db02e;
    }
    .custom-header .navbar-brand,
    .custom-header .nav-link {
      color: white !important;
    }
    .custom-header .nav-link.active {
      font-weight: bold;
      text-decoration: underline;
    }

    .form-label, .form-control, .btn {
      color: white;
    }
    .form-control {
      background-color: #222;
      border: 1px solid #444;
    }
    .form-control:focus {
      background-color: #222;
      color: white;
      border-color: #666;
    }

    .btn-primary {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }
    .btn-primary:hover {
      background-color: #0b5ed7;
    }
    .btn-secondary {
      background-color: #6c757d;
      border-color: #6c757d;
    }

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
  </style>
</head>
<body>

<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
    <a class="navbar-brand fw-bold" href="/volunteer/dashboard.php">VolNet</a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
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
          <span class="text-white">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
          <a href="/auth/logout.php" class="btn btn-outline-light">Logout</a>
        <?php else: ?>
          <button class="btn btn-dark" onclick="window.location.href='/auth/login.html'">Sign In</button>
          <button class="btn btn-dark" onclick="window.location.href='/auth/register.html'">Sign Up</button>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</header>

<main class="container">
  <h2 class="mb-4">Write a New Blog</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label" for="title">Blog Title</label>
      <input id="title" type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label" for="image">Upload Image</label>
      <input id="image" type="file" name="image" class="form-control" accept="image/*" required>
    </div>
    <div class="mb-3">
      <label class="form-label" for="content">Blog Content</label>
      <textarea id="content" name="content" class="form-control" rows="6" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary me-2">Post Blog</button>
    <a href="/pages/blogs.php" class="btn btn-secondary">Cancel</a>
  </form>
</main>

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


