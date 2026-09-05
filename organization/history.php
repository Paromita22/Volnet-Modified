<?php
session_start();

// DB connection (assuming 'includes/db.php' or defining it like history.php)
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

if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}



$org_id = null;

// First, check if a specific organization ID is passed in the URL (from a public profile view)
if (isset($_GET['org_id']) && is_numeric($_GET['org_id'])) {
    $org_id = (int)$_GET['org_id'];
} 
// If not, fall back to the session (for an organization viewing their own history from their dashboard)
else if (isset($_SESSION['role']) && $_SESSION['role'] === 'organization' && isset($_SESSION['org_id'])) {
    $org_id = $_SESSION['org_id'];
}

// If we still don't have an org_id, we can't proceed.
if (!$org_id) {
    die("Error: Could not determine which organization's history to display.");
}


$org_name = "Organization"; // Default name
$stmt_org = $conn->prepare("SELECT org_name FROM organization WHERE org_id = ?");
$stmt_org->bind_param("i", $org_id);
$stmt_org->execute();
$result_org = $stmt_org->get_result();
// CORRECTED LINE:
if ($org_data = $result_org->fetch_assoc()) { 
    $org_name = htmlspecialchars($org_data['org_name']);
}
$stmt_org->close();




$current_date = date("Y-m-d");

$past_events = [];
$current_events = [];
$upcoming_events = [];

$stmt = $conn->prepare(
    "SELECT id, title, type, start_date, end_date, location, volunteers 
     FROM eventt 
     WHERE org_id = ? 
     ORDER BY start_date ASC" // Order by ASC to easily process upcoming
);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['end_date'] < $current_date) {
        $past_events[] = $row;
    } elseif ($row['start_date'] <= $current_date && $row['end_date'] >= $current_date) {
        $current_events[] = $row;
    } else {
        $upcoming_events[] = $row;
    }
}
// Reverse past events to show most recent past events first
$past_events = array_reverse($past_events);

$stmt->close();
$conn->close();

// --- Helper function to render table rows ---
function render_event_rows($events) {
    if (empty($events)) {
        echo '<tr><td colspan="6" class="text-center">No events in this category.</td></tr>';
    } else {
        foreach ($events as $event) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($event['title']) . '</td>';
            echo '<td>' . htmlspecialchars($event['type']) . '</td>';
            echo '<td>' . date("d M, Y", strtotime($event['start_date'])) . '</td>';
            echo '<td>' . date("d M, Y", strtotime($event['end_date'])) . '</td>';
            echo '<td>' . htmlspecialchars($event['location']) . '</td>';
            echo '<td>' . htmlspecialchars($event['volunteers']) . '</td>';
            echo '</tr>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />


<!-- In org_history.php, update the page title -->
<title><?php echo $org_name; ?> - Event History - VolNet</title>







  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <!-- Re-using the same stylesheet for a consistent base theme -->
  <link rel="stylesheet" href="/assets/css/vol-home.css"/> 
  <style>
    /* Copied directly from history.php for visual consistency */
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

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success text-center" role="alert">
    Event deleted successfully.
</div>
<?php endif; ?>


<!-- Sidebar Toggle Icon -->
<span class="menu-icon" onclick="toggleSidebar()">☰</span>

<!-- SHARED HEADER - Copied from history.php, with only the account link changed -->
<header class="custom-header">
  <nav class="navbar navbar-expand-lg px-3">
   
<!-- Logo and Brand Name now point to the DYNAMIC homepage -->
<a href="<?php echo htmlspecialchars($home_page); ?>"><img src="/assets/images/logo_1.png" alt="VolNet Logo" style="height: 60px;"></a>
<a class="navbar-brand fw-bold text-white" href="<?php echo htmlspecialchars($home_page); ?>">VolNet</a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- **KEY CHANGE**: Using the same general navigation links as history.php -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
        <li class="nav-item"><a class="nav-link" href="<?php echo htmlspecialchars($home_page); ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/blogs.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/contactus.php">Contact</a></li>
      </ul>
      
      <div class="d-flex gap-3 align-items-center">
        <a href="/auth/logout.php" class="btn btn-dark">Log Out</a>
        <!-- **KEY CHANGE**: The profile link points to the ORGANIZATION account page -->
        <a href="/organization/profile.php" class="profile-icon" title="Your Account">
          <i class="fas fa-user-circle fa-lg text-white"></i>
        </a>
      </div>
    </div>
  </nav>
</header>



    



<!-- Main Content -->
<div class="main-content">
  <div class="container">
<h2 class="mb-4" style="color:white;"><?php echo $org_name; ?> - Event History</h2>
    
    <!-- CURRENT EVENTS -->
    <h2 class="mb-4" style="color:white;">🟢 Current Events</h2>
    <div class="table-responsive mb-5">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Event Title</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Location</th>
            <th>Volunteers Needed</th>
          </tr>
        </thead>
        <tbody>
          <?php render_event_rows($current_events); ?>
        </tbody>
      </table>
    </div>

    <!-- UPCOMING EVENTS -->
    <h2 class="mb-4" style="color:white;">🔜 Upcoming Events</h2>
    <div class="table-responsive mb-5">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Event Title</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Location</th>
            <th>Volunteers Needed</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php
if (empty($upcoming_events)) {
    echo '<tr><td colspan="7" class="text-center">No upcoming events.</td></tr>';
} else {
    foreach ($upcoming_events as $event) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($event['title']) . '</td>';
        echo '<td>' . htmlspecialchars($event['type']) . '</td>';
        echo '<td>' . date("d M, Y", strtotime($event['start_date'])) . '</td>';
        echo '<td>' . date("d M, Y", strtotime($event['end_date'])) . '</td>';
        echo '<td>' . htmlspecialchars($event['location']) . '</td>';
        echo '<td>' . htmlspecialchars($event['volunteers']) . '</td>';
        echo '<td>
            <form method="POST" action="/organization/delete_event.php" onsubmit="return confirm(\'Are you sure you want to delete this event?\');">
                <input type="hidden" name="event_id" value="' . $event['id'] . '">
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </form>
        </td>';
        echo '</tr>';
    }
}
?>

        </tbody>
      </table>
    </div>

    <!-- PAST EVENTS -->
    <h2 class="mb-4" style="color:white;">📅 Past Events</h2>
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Event Title</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Location</th>
            <th>Volunteers Needed</th>
          </tr>
        </thead>
        <tbody>
          <?php render_event_rows($past_events); ?>
        </tbody>
      </table>
    </div>
    
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