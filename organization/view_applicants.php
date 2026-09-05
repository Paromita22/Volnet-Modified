<?php
session_start();
// 1. Security Check: Only logged-in organizations can view this.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'organization') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

require_once __DIR__ . '/../includes/db.php';

// 2. Get the Organization's ID
$org_id = null;
$stmt_org = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
$stmt_org->bind_param("i", $_SESSION['user_id']);
$stmt_org->execute();
$result_org = $stmt_org->get_result();
if($org_data = $result_org->fetch_assoc()){
    $org_id = $org_data['org_id'];
}
$stmt_org->close();

if (!$org_id) {
    die("Organization profile not found."); 
}

// 3. Fetch all applications for this organization's events
$sql = "SELECT 
            a.application_id, a.status, a.application_date,
            v.volunteer_id, v.name AS volunteer_name,
            e.title AS event_title
        FROM applications a
        JOIN volunteer v ON a.volunteer_id = v.volunteer_id
        JOIN eventt e ON a.event_id = e.id
        WHERE a.org_id = ?
        ORDER BY a.application_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Applicants - VolNet</title>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <!-- Link to your main stylesheet -->
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
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

    /* Optional: A little spacing for the action buttons */
    .table td form {
        margin-right: 4px;
    }




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

<!-- Menu Icon for Sidebar -->
<span class="menu-icon" onclick="toggleSidebar()">☰</span>

<!-- SHARED HEADER -->
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
<!-- ORGANIZATION SIDEBAR -->

<aside class="sidebar" id="sidebar">
    <a href="/organization/create_event.php">Create Event</a>
    <a href="#">Available Volunteers</a>
    <a href="/organization/view_applicants.php">View Applications</a>
    <a href="/organization/manage_events.php">Manage Event Attendance</a>
    <a href="#" onclick="toggleReviewForm()">Submit Review</a>
    <a href="/auth/logout.php">Logout</a>
</aside>

<!-- Main Page Content -->
<div class="main-content">
    <div class="container">
        <h2 class="mb-4">Volunteer Applications</h2>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="alert alert-success">Application status updated successfully!</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Event Title</th>
                        <th>Applicant Name</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['volunteer_name']); ?></td>
                            <td><?php echo date("M j, Y, g:i A", strtotime($row['application_date'])); ?></td>
                            <td>
                                <span class="badge status-<?php echo strtolower(htmlspecialchars($row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/view_volunteer_profile.php?vid=<?php echo $row['volunteer_id']; ?>" class="btn btn-info btn-sm" title="View Profile">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                
                                <?php if ($row['status'] == 'Pending'): ?>
                                <form action="/organization/update_application_status.php" method="POST" class="d-inline">
                                    <input type="hidden" name="app_id" value="<?php echo $row['application_id']; ?>">
                                    <input type="hidden" name="status" value="Accepted">
                                    <button type="submit" class="btn btn-success btn-sm" title="Accept"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="/organization/update_application_status.php" method="POST" class="d-inline">
                                    <input type="hidden" name="app_id" value="<?php echo $row['application_id']; ?>">
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Reject"><i class="fas fa-times"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No applications received yet.</td>
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