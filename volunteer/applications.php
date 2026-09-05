<?php
session_start();
// 1. Security Check: Only logged-in volunteers can view this.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

require_once __DIR__ . '/../includes/db.php';

// 2. Get the Volunteer's ID
$volunteer_id = null;
$stmt_vol = $conn->prepare("SELECT volunteer_id FROM volunteer WHERE user_id = ?");
$stmt_vol->bind_param("i", $_SESSION['user_id']);
$stmt_vol->execute();
$result_vol = $stmt_vol->get_result();
if($vol_data = $result_vol->fetch_assoc()){
    $volunteer_id = $vol_data['volunteer_id'];
}
$stmt_vol->close();

if (!$volunteer_id) {
    die("Volunteer profile not found.");
}

// 3. Fetch all applications for this volunteer
$sql = "SELECT 
            a.status, a.application_date,
            e.title AS event_title,
            o.org_name
        FROM applications a
        JOIN eventt e ON a.event_id = e.id
        JOIN organization o ON a.org_id = o.org_id
        WHERE a.volunteer_id = ?
        ORDER BY a.application_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Application Tracking - VolNet</title>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <!-- Link to your main stylesheet -->
  <link rel="stylesheet" href="/assets/css/orgh.css"/> 
  <!-- Inside your <head> tag -->
<style>
    /* Custom styles for this page */
    .main-content { 
      padding: 40px 20px; 
      transition: margin-left .3s; 
      margin-top: 80px; /* To prevent content from hiding behind the sticky header */
    }
    .sidebar.active ~ .main-content { 
      margin-left: 250px; 
    }
    
    /*
     *  FIX: Make all table text pure white.
     *  We override Bootstrap's CSS variables for table colors.
    */
    .table {
        --bs-table-color: white;             /* Sets text color for standard cells */
        --bs-table-striped-color: white;     /* Sets text color for striped cells */
    }

    /* Improve striped table visibility on dark background */
    .table-striped>tbody>tr:nth-of-type(odd)>* {
        --bs-table-accent-bg: rgba(255, 255, 255, 0.05); /* A subtle white tint for odd rows */
    }
    
    /* Status badge colors */
    .status-pending { background-color: #ffc107; color: #333; }
    .status-accepted { background-color: #28a745; color: white; }
    .status-rejected { background-color: #dc3545; color: white; }




        /*
     *  THE KEY FIX: A more specific rule to force all table text to be white,
     *  overriding any conflicting styles from orgh.css.
    */
    .table td, .table th {
        color: white;
    }

    /* Your requested header CSS */
    .custom-header {
      background:    linear-gradient(to right,transparent,rgb(94, 203, 43));
      position: sticky;
      top: 0;
      z-index: 100;
      animation: fadeInDown 0.8s ease-in-out;
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
    }

    header {
      background: transparent;
      padding: 10px 20px;
      position: sticky;
      top: 0;
      z-index: 100;
      animation: fadeInDown 0.8s ease-in-out;
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
    }

    .nav-link {
      text-decoration: none;
      color: rgb(255, 255, 255); 
      transition: color 0.3s ease;
      font-weight: bold;
      font-size: 20px;
    }

    .nav-link:hover {
      color: #6db02e; 
      text-decoration: underline;
    }
</style>
</head>
<body>

<!-- Menu Icon for Sidebar -->
<span class="menu-icon" onclick="toggleSidebar()">☰</span>

<!-- SHARED HEADER 
<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
    <a class="navbar-brand fw-bold text-white" href="/volunteer/dashboard.php" style="margin-left: 25px;">VolNet</a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
        <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>
      <form class="d-flex me-3" role="search" action="/pages/search.php" method="GET">
          <input class="form-control me-2" type="search" name="q" placeholder="Search..." aria-label="Search" required>
          <button class="btn btn-outline-dark bg-white" type="submit"><i class="fa fa-search"></i></button>
        </form>
      <div class="d-flex align-items-center">
        <a href="/auth/logout.php" class="btn btn-dark">Log out</a>
        <a href="/volunteer/profile.php" class="profile-icon ms-3" title="Your Account">
          <i class="fas fa-user-circle fa-lg"></i>
        </a>
      </div>
    </div>
  </nav>
</header>-->


<!-- SHARED HEADER - UPDATED FOR VOLUNTEER -->
<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
    <!-- Logo and Brand Name from the second example -->
    <a href="#"><img src="/assets/images/logo_1.png" alt="VolNet Logo" style="height: 60px;"></a>
    <a class="navbar-brand fw-bold text-white" href="/volunteer/dashboard.php">VolNet</a>
    
    <!-- Toggler remains the same -->
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Navigation links from your FIRST header -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
        <li class="nav-item"><a class="nav-link" href="/volunteer/dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/blogs.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/contactus.php">Contact</a></li>
      </ul>
      
      <!-- Right-side actions using the structure from the SECOND header -->
      <div class="d-flex gap-3 align-items-center">
        <a href="/auth/logout.php" class="btn btn-dark">Log Out</a>
        <!-- The profile link points to the VOLUNTEER account page -->
        <a href="/volunteer/profile.php" class="profile-icon" title="Your Account">
          <i class="fas fa-user-circle fa-lg text-white"></i>
        </a>
      </div>
    </div>
  </nav>
</header>

<!-- YOUR REQUESTED VOLUNTEER SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <a href="/volunteer/applications.php">Application Tracking</a>
  <a href="/pages/projects.php">Explore Events</a>
  <a href="/volunteer/history.php">History</a>
  <a href="#">Submit Review</a>
  <a href="/auth/logout.php">Logout</a>
</aside>

<!-- Main Page Content -->
<div class="main-content">
    <div class="container">
        <h2 class="mb-4">My Applications</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Event Title</th>
                        <th>Organization</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['org_name']); ?></td>
                            <td><?php echo date("M j, Y", strtotime($row['application_date'])); ?></td>
                            <td>
                                <span class="badge status-<?php echo strtolower(htmlspecialchars($row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">You have not applied to any events yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }
</script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>