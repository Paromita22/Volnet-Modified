<?php
session_start();
// Security Check: Only logged-in organizations can view this.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

include __DIR__ . '/../includes/db.php';

// Get the Organization's ID from their user_id
$org_id = null;
$stmt_org = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
$stmt_org->bind_param("i", $_SESSION['user_id']);
$stmt_org->execute();
$result_org = $stmt_org->get_result();
if($org_data = $result_org->fetch_assoc()){
    $org_id = $org_data['org_id'];
    // THE FIX: Save the org_id to the session for the next page
    $_SESSION['org_id'] = $org_id; 
}
$stmt_org->close();

if (!$org_id) {
    die("Organization profile not found. Please complete your profile setup."); 
}

// Fetch all events created by this organization
$events = [];
$stmt = $conn->prepare("SELECT id, title, end_date FROM eventt WHERE org_id = ? ORDER BY end_date DESC");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Event Attendance - VolNet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
  <style>
    body { background-color: #000; color: #fff; }
    .content-container { padding: 40px 20px; margin-top: 80px; }
    .btn-manage { background-color: #9bd258; color: #000; font-weight: bold; border: none; }
    .btn-manage:hover { background-color: #b0f068; }

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

    #sign-in, #sign-up {
      padding: 5px 10px;
      background-color: #ffffff;
      color: #6db02e;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }

    #sign-in:hover, #sign-up:hover {
      background-color: #000000;
      color: #6db02e;
      font-weight: bold;
    }

  </style>
</head>
<body>


<span class="menu-icon" onclick="toggleSidebar()">☰</span>
<!-- START: NEW HEADER -->
<header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
        <a href="/organization/dashboard.php"><img src="/assets/images/logo_1.png" alt="VolNet Logo" style="height: 60px;"></a>
        <a class="navbar-brand fw-bold text-white" href="/organization/dashboard.php">VolNet</a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
    
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                <li class="nav-item"><a class="nav-link" href="/organization/dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/blogs.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link " href="/pages/contactus.php">Contact</a></li>
            </ul>
            <div class="d-flex gap-3 align-items-center">
                <a href="/auth/logout.php" class="btn btn-dark" id="sign-in">Log Out</a>
                <a href="/organization/profile.php" class="profile-icon" title="Your Account"><i class="fas fa-user-circle fa-lg text-white"></i></a>
            </div>
        </div>
    </nav>
</header>
<!-- END: NEW HEADER -->
<aside class="sidebar" id="sidebar">
    <a href="/organization/create_event.php">Create Event</a>
    <a href="#">Available Volunteers</a>
    <a href="/organization/view_applicants.php">View Applications</a>
    <a href="/organization/manage_events.php">Manage Event Attendance</a>
    <a href="#" onclick="toggleReviewForm()">Submit Review</a>
    <a href="/auth/logout.php">Logout</a>
</aside>

<div class="container content-container">
    <h2 class="mb-4">Manage Event Attendance</h2>
    <p>Select an event to mark which volunteers attended and record their hours.</p>

    <div class="table-responsive">
      <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Event Title</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo date("F j, Y", strtotime($event['end_date'])); ?></td>
                        <td>
                            <a href="/organization/mark_attendance.php?event_id=<?php echo $event['id']; ?>" class="btn btn-manage btn-sm">
                                Mark Attendance
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">You have not created any events yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleSidebar() { document.getElementById('sidebar').classList.toggle('active'); }
</script>
</body>
</html>