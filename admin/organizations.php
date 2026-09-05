<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}
include __DIR__ . '/../includes/db.php';
// Determine the home page based on user role
$home_page = '/pages/homepage.php'; // Default fallback
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'volunteer':
            $home_page = '/volunteer/dashboard.php';
            break;
        case 'organization':
            $home_page = '/organization/dashboard.php';
            break;
        case 'admin':
            $home_page = '/admin/dashboard.php';
            break;
        default:
            $home_page = '/pages/homepage.php'; // Fallback for unhandled roles
            break;
    }
}
// Fetch organizations
$sql = "SELECT org_id, user_id, org_name, contact_email, mobile, address FROM organization ORDER BY org_name";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View Organizations - Admin</title>
  <link rel="stylesheet" href="/assets/css/admin.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <style>
    /* Sticky footer layout */
    html,body {
      height: 100%;
      margin: 0;
    }
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: #121212;
    }

    /* Main content grows to fill available space */
    .main-content-area {
      flex: 1 0 auto;
      margin-left: 0; /* default, sidebar toggle changes this */
      transition: margin-left 0.3s ease;
    }




    /* Smaller footer style */
    .volunteer-footer {
      flex-shrink: 0;
      padding: 10px 20px;
      font-size: 0.85rem;
      background-color: #222;
      color: #ddd;
    }
    .volunteer-footer .footer-content {
      padding-bottom: 10px;
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: space-between;
    }
    .volunteer-footer .footer-section {
      margin-bottom: 10px;
      flex: 1 1 200px;
    }
    .volunteer-footer .footer-section h3 {
      font-size: 1rem;
      margin-bottom: 8px;
      color: #fff;
    }
    .volunteer-footer .footer-section p,
    .volunteer-footer .footer-section ul li {
      font-size: 0.85rem;
      margin-bottom: 4px;
      color: #ccc;
    }
    .volunteer-footer .footer-section ul {
      padding-left: 0;
      list-style: none;
    }
    .volunteer-footer .footer-section ul li a {
      color: #ccc;
      text-decoration: none;
    }
    .volunteer-footer .footer-section ul li a:hover {
      text-decoration: underline;
    }
    .volunteer-footer .footer-bottom {
      padding: 5px 0;
      font-size: 0.75rem;
      text-align: center;
      color: #bbb;
      border-top: 1px solid #444;
      margin-top: 10px;
    }
    .volunteer-footer .social-icons a {
      font-size: 1.2rem;
      margin-right: 8px;
      color: #ccc;
      transition: color 0.3s ease;
    }
    .volunteer-footer .social-icons a:hover {
      color: #fff;
    }
  </style>
</head>
<body>

<span class="menu-icon" onclick="toggleSidebar()">&#9776;</span>

<aside class="sidebar" id="sidebar">
  <a href="/admin/volunteers.php">View Volunteers</a>
  <a href="/admin/organizations.php">View Organizations</a>
  <a href="/auth/logout.php">Logout</a>
</aside>

<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
    <a class="navbar-brand fw-bold text-white" href="/admin/dashboard.php" style="margin-left: 25px;">VolNet</a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">

        <li class="nav-item"><a class="nav-link text-white" href="<?php echo $home_page; ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/pages/projects.php">Projects</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/pages/blogs.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="/pages/contactus.php">Contact</a></li>
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

<div class="main-content-area">
  <div class="container mt-5">
  <h2 class="mb-4" style="color: white;">Organizations List</h2>

    <table class="table table-dark table-striped table-hover">
      <thead>
        <tr>
          <th>Organization Name</th>
          <th>Contact Email</th>
          <th>Mobile</th>
          <th>Address</th>
          <th>Profile</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
          while ($org = $result->fetch_assoc()) {
            $status = "Active";
            echo "<tr>";
            echo "<td>" . htmlspecialchars($org['org_name']) . "</td>";
            echo "<td>" . htmlspecialchars($org['contact_email']) . "</td>";
            echo "<td>" . htmlspecialchars($org['mobile']) . "</td>";
            echo "<td>" . htmlspecialchars($org['address']) . "</td>";
            echo "<td><a href='/admin/view_org_profile.php?org_id=" . $org['org_id'] . "' class='btn btn-sm btn-primary'>View Profile</a></td>";
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='6'>No organizations found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
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
    sidebar.classList.toggle('active');
    const mainContent = document.querySelector('.main-content-area');
    mainContent.style.marginLeft = sidebar.classList.contains('active') ? '250px' : '0';
  }
</script>
</body>

</html>

<?php $conn->close(); ?>

