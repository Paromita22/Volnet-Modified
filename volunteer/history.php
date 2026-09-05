<?php
session_start();

// Broader security check: Any logged-in user can view history.
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// DB connection
require_once __DIR__ . '/../includes/db.php';
// --- Dynamic Home Page URL ---
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
    }
}
// --- End Dynamic URL ---
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// --- NEW LOGIC TO GET VOLUNTEER ID ---
$volunteer_id = null;

// First, check if a specific volunteer ID is passed in the URL (from an admin/org view)
if (isset($_GET['vid']) && is_numeric($_GET['vid'])) {
    $volunteer_id = (int)$_GET['vid'];
} 
// If not, fall back to the session (for a volunteer viewing their own history)
else if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer') {
    $stmt_get_id = $conn->prepare("SELECT volunteer_id FROM volunteer WHERE user_id = ?");
    $stmt_get_id->bind_param("i", $_SESSION['user_id']);
    $stmt_get_id->execute();
    $result_get_id = $stmt_get_id->get_result();
    if ($row_id = $result_get_id->fetch_assoc()) {
        $volunteer_id = $row_id['volunteer_id'];
    }
    $stmt_get_id->close();
}
// --- END OF NEW LOGIC ---

// If we still don't have a volunteer_id, we can't proceed.
if (!$volunteer_id) {
    die("Error: Could not determine which volunteer's history to display.");
}

// The rest of the script remains the same, as it's already using the $volunteer_id variable.
$sql = "SELECT ve.hours_completed, ve.attendance_date,
               e.title, e.type, e.start_date, e.end_date, e.location, e.org_id,
               o.org_name
        FROM volunteer_events ve
        JOIN eventt e ON ve.event_id = e.id
        JOIN organization o ON e.org_id = o.org_id
        WHERE ve.volunteer_id = ? AND ve.status = 'Attended'
        ORDER BY e.start_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Volunteer History - VolNet</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/vol-home.css"/>
  <style>
   
   .main-content { 
      padding: 40px 20px; 
      transition: margin-left .3s; 
      margin-top: 80px; /* To prevent content from hiding behind the sticky header */
    }
    .sidebar.active ~ .main-content { margin-left: 250px; }
    .table {
        --bs-table-color: white;             /* Sets text color for standard cells */
        --bs-table-striped-color: white;     /* Sets text color for striped cells */
    }
  
    .table td, .table th {
        color: white;
    }
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

<!-- Sidebar Toggle Icon -->
<span class="menu-icon" onclick="toggleSidebar()">☰</span>

<!-- SHARED HEADER - UPDATED FOR VOLUNTEER -->
<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
   <!-- Logo and Brand Name now point to the DYNAMIC homepage -->
<a href="<?php echo htmlspecialchars($home_page); ?>"><img src="/assets/images/logo_1.png" alt="VolNet Logo" style="height: 60px;"></a>
<a class="navbar-brand fw-bold text-white" href="<?php echo htmlspecialchars($home_page); ?>">VolNet</a>
    
    <!-- Toggler remains the same -->
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Navigation links from your FIRST header -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
       
        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($home_page); ?>">Home</a></li>
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


<!-- Main Content -->
<div class="main-content">
  <div class="container">
    <h2 class="mb-4" style="color:white;">Attended Events</h2>

    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Event Title</th>
            <th>Event Type</th>
            <th>Organization Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Location</th>
            <th>Hours Completed</th>
            
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['org_name']); ?></td>
                <td><?php echo $row['start_date']; ?></td>
                <td><?php echo $row['end_date']; ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo $row['hours_completed']; ?></td>
                
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">No attended events yet.</td>
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
