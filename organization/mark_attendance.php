<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}
// Check if event_id is provided
if (!isset($_GET['event_id'])) {
    header("Location: /organization/manage_events.php?error=noeventid");
    exit();
}

include __DIR__ . '/../includes/db.php';
$event_id = $_GET['event_id'];

// Get Org ID and verify they own this event
$org_id = $_SESSION['org_id'] ?? null;
if (!$org_id) {
    $stmt_o = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
    $stmt_o->bind_param("i", $_SESSION['user_id']);
    $stmt_o->execute();
    $r = $stmt_o->get_result()->fetch_assoc();
    if ($r) {
        $org_id = $r['org_id'];
        $_SESSION['org_id'] = $org_id;
    }
    $stmt_o->close();
}
$stmt_verify = $conn->prepare("SELECT title FROM eventt WHERE id = ? AND org_id = ?");
$stmt_verify->bind_param("ii", $event_id, $org_id);
$stmt_verify->execute();
$result_verify = $stmt_verify->get_result();
if ($result_verify->num_rows == 0) {
    header("Location: /organization/manage_events.php?error=accessdenied"); // Org does not own this event
    exit();
}
$event_data = $result_verify->fetch_assoc();
$event_title = $event_data['title'];
$stmt_verify->close();

// Fetch ACCEPTED applicants for this event
$sql = "SELECT v.volunteer_id, v.name 
        FROM applications a
        JOIN volunteer v ON a.volunteer_id = v.volunteer_id
        WHERE a.event_id = ? AND a.status = 'Accepted'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
?>











<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mark Attendance for <?php echo htmlspecialchars($event_title); ?></title>
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
    <a href="/organization/manage_events.php">« Back to My Events</a>
    <h2 class="my-4">Mark Attendance: <span style="color:#9bd258;"><?php echo htmlspecialchars($event_title); ?></span></h2>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success">Attendance recorded successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
        <div class="alert alert-danger">An error occurred. Please try again.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <!--<thead class="table-dark">
                <tr>
                    <th>Volunteer Name</th>
                    <th>Hours Completed</th>
                    <th>Action</th>
                </tr>
            </thead>-->
            <thead class="table-dark">
    <tr>
        <th>Volunteer Name</th>
        <th style="width: 60%;">Action</th> <!-- Combined the two columns -->
    </tr>
</thead>
            <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($volunteer = $result->fetch_assoc()): ?>
            <?php
            // Check if this volunteer's attendance has already been recorded
            $stmt_check = $conn->prepare("SELECT id FROM volunteer_events WHERE volunteer_id = ? AND event_id = ? AND status = 'Attended'");
            $stmt_check->bind_param("ii", $volunteer['volunteer_id'], $event_id);
            $stmt_check->execute();
            $is_recorded = $stmt_check->get_result()->num_rows > 0;
            $stmt_check->close();
            ?>
            <tr>
                <td><?php echo htmlspecialchars($volunteer['name']); ?></td>

                <td>
                    <!-- View Profile Button -->
                    <a href="/admin/view_volunteer_profile.php?vid=<?php echo $volunteer['volunteer_id']; ?>" 
                       class="btn btn-info btn-sm me-2" 
                       title="View Profile"
                       >
                        <i class="fas fa-user"></i> View Profile
                    </a>

                    <?php if ($is_recorded): ?>
                        <span class="badge bg-success d-inline-block mt-1">Attendance Confirmed</span>
                    <?php else: ?>
                        <!-- Form for marking attendance -->
                        <form action="/organization/process_attendance.php" method="POST" class="d-inline-flex align-items-center gap-2">
                            <input type="hidden" name="volunteer_id" value="<?php echo $volunteer['volunteer_id']; ?>">
                            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                            
                            <input type="number" name="hours_completed" class="form-control form-control-sm" style="width: 150px;" placeholder="Hours Completed" step="0.5" min="0" required>
                            
                            <button type="submit" class="btn btn-manage btn-sm">Confirm Attendance</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="2" class="text-center">No volunteers were accepted for this event, or all have been processed.</td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>
<script>
    function toggleSidebar() { document.getElementById('sidebar').classList.toggle('active'); }
</script>
<?php
$stmt->close();
$conn->close();
?>
</body>
</html>


