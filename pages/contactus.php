<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      display: flex;
      flex-direction: column;
      font-family: 'Segoe UI', sans-serif;
      background: url('https://media.istockphoto.com/id/2205572960/photo/portrait-of-mature-volunteer-woman-outdoors.webp?a=1&b=1&s=612x612&w=0&k=20&c=ZJnZd7zCWHKSJEpXGQCnIIjH-6kSreiY1s0KD453wgg=') no-repeat center center/cover;
      color: white;
    }

    header {
      background: linear-gradient(to right, #449f1a, #9bd258);
      padding: 10px 20px;
      position: sticky;
      top: 0;
      z-index: 100;
      animation: fadeInDown 0.8s ease-in-out;
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 28px;
    }

    .nav-link {
      font-weight: bold;
      font-size: 18px;
      color: black !important;
    }

    .nav-link:hover {
      color: white !important;
      text-decoration: underline;
    }

    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .contact-section {
      width: 100%;
      max-width: 600px;
      height: 400px;
      margin: 20px auto;
      padding: 20px;
      border-radius: 15px;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
      z-index: 1;

      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .contact-card {
      width: 100%;
      height: 100%;
      padding: 20px;
      font-size: 14px;
      border-radius: 10px;

      background: rgba(31, 29, 29, 0.6);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);

      box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
      color: white;

      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .contact-card button {
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 15px;
      background-color: #449f1a;
      border: none;
      color: white;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }

    .contact-card button:hover {
      background-color: #6db636;
    }

    .contact-info {
      margin-top: 30px;
      text-align: center;
    }

    .contact-info p {
      margin: 10px 0;
    }

    .volunteer-footer {
      background-color: rgba(0, 0, 0, 0.6);
      color: #fff;
      padding: 10px 10px;
      font-size: 11px;
    }

    .footer-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      max-width: 900px;
      margin: auto;
      gap: 20px;
    }

    .footer-section {
      flex: 1 1 180px;
    }

    .footer-section h3 {
      margin-bottom: 5px;
      font-size: 13px;
      color: #00f2fe;
    }

    .footer-section p,
    .footer-section ul,
    .footer-section a {
      font-size: 11px;
      line-height: 1.4;
      color: #ccc;
      text-decoration: none;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
    }

    .footer-section ul li {
      margin-bottom: 4px;
    }

    .social-icons a {
      margin-right: 10px;
      font-size: 16px;
      color: #00f2fe;
      transition: color 0.3s;
    }

    .social-icons a:hover {
      color: #4facfe;
    }

    .footer-bottom {
      text-align: center;
      margin-top: 10px;
      font-size: 10px;
      color: #aaa;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand text-white" href="/pages/homepage.php">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <i class="fa-solid fa-bars"></i>
      </button>

     <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item">
            <a class="nav-link" href="/pages/homepage.php">Home</a>
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

  <!-- Main Content -->
  <main>
    <div class="contact-section">
      <div class="contact-card">
        <h2 style="margin-top: 40px;">Got questions for us?</h2>
        <p>Send us a message and one of our volunteer travel specialists will be in touch.</p>
        <button>Message Us</button>
        <div class="contact-info">
          <p><strong>Speak with a volunteer travel specialist</strong></p>
          <p>📧 temp@gmail.com</p>
          <p>📞 +8801721919494</p>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
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

</body>
</html>

