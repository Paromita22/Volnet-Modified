<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}
require_once __DIR__ . '/../includes/db.php';

// Fetch counts from your tables
$volunteers = $conn->query("SELECT COUNT(*) AS total FROM volunteer")->fetch_assoc()['total'] ?? 0;
$organizations = $conn->query("SELECT COUNT(*) AS total FROM organization")->fetch_assoc()['total'] ?? 0;
$admins = $conn->query("SELECT COUNT(*) AS total FROM admin")->fetch_assoc()['total'] ?? 0;
$total_events = $conn->query("SELECT COUNT(*) AS total FROM eventt")->fetch_assoc()['total'] ?? 0; // Added event count

// Fetch all complaints
$complaints_sql = "
    SELECT 
        c.complaint_id, 
        c.complaint_text, 
        c.complaint_date, 
        c.status,
        complainant.email AS complainant_email,
        complainant.role AS complainant_role,
        accused.email AS accused_email,
        accused.role AS accused_role
    FROM 
        complaints c
    JOIN 
        users complainant ON c.complainant_user_id = complainant.user_id
    LEFT JOIN 
        users accused ON c.accused_user_id = accused.user_id
    ORDER BY 
        c.complaint_date DESC";
$complaints_result = $conn->query($complaints_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  
  <title>VolNet Admin Dashboard</title>
  <link rel="stylesheet" href="/assets/css/admin.css"/> 
</head>
<body>

<span class="menu-icon" onclick="toggleSidebar()">☰</span>

<aside class="sidebar" id="sidebar">
    <a href="/admin/volunteers.php">View Volunteers</a>
    <a href="/admin/organizations.php">View Organizations</a>
    <a href="#">Manage Blogs</a>
    <a href="#">System Settings</a>
    <a href="/auth/logout.php">Logout</a>
</aside>
    

  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/admin/dashboard.php" style="margin-left: 80px;">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
      </button>
  
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item">
            <a class="nav-link" href="/admin/dashboard.php">Home</a>
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
  
        <div class="d-flex gap-2">
           <button class="btn btn-dark" id="logout" onclick="window.location.href='/pages/homepage.php'">Log out</button>
          
    
        
      </div>

    </nav>
  </header>

<div class="main-content-area">
   
    <!-- ======================= -->
    <!-- NEW KEY METRICS SECTION -->
    <!-- ======================= -->
    <section class="admin-dashboard-section mt-5">
        <div class="container">
            <h2 class="section-title">Key Metrics</h2>
            <div class="row g-4">
                <!-- Volunteers -->
                <div class="col-xl-3 col-md-6">
                    <div class="metric-card card-volunteers">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-users metric-icon"></i>
                            <div>
                                <h5 class="card-title">Total Volunteers</h5>
                                <p class="metric-number" data-target="<?php echo $volunteers; ?>">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Organizations -->
                <div class="col-xl-3 col-md-6">
                    <div class="metric-card card-orgs">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-building metric-icon"></i>
                            <div>
                                <h5 class="card-title">Total Organizations</h5>
                                <p class="metric-number" data-target="<?php echo $organizations; ?>">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Events -->
                <div class="col-xl-3 col-md-6">
                    <div class="metric-card card-events">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-calendar-alt metric-icon"></i>
                            <div>
                                <h5 class="card-title">Total Events</h5>
                                <p class="metric-number" data-target="<?php echo $total_events; ?>">0</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Admins -->
                <div class="col-xl-3 col-md-6">
                    <div class="metric-card card-admins">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-user-shield metric-icon"></i>
                            <div>
                                <h5 class="card-title">Total Admins</h5>
                                <p class="metric-number" data-target="<?php echo $admins; ?>">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================== -->
    <!-- NEW COMPLAINT MANAGEMENT SECTION -->
    <!-- ============================== -->
    <section class="admin-dashboard-section mt-5">
        <div class="container">
            <h2 class="section-title">Complaint Management</h2>
            <div class="card bg-dark border-secondary">
                <div class="card-header">
                    <h3 class="card-title-h3 mb-0">User Complaints</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle complaints-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Complainant</th>
                                    <th>Accused</th>
                                    <th>Complaint</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($complaints_result && $complaints_result->num_rows > 0): ?>
                                    <?php while ($row = $complaints_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($row['complaint_id']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row['complainant_email']); ?>
                                                <span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($row['complainant_role'])); ?></span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['accused_email']); ?>
                                                <span class="badge bg-warning text-dark"><?php echo ucfirst(htmlspecialchars($row['accused_role'])); ?></span>
                                            </td>
                                            <td class="complaint-text"><?php echo htmlspecialchars($row['complaint_text']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($row['complaint_date'])); ?></td>
                                            <td>
                                                <span class="badge status-badge status-<?php echo strtolower(htmlspecialchars($row['status'])); ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                           <td>
    <div class="dropdown">
        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $row['complaint_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
            Manage
        </button>
        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMenuButton<?php echo $row['complaint_id']; ?>">
            <!-- The href now points to our handler script with parameters -->
            <li><a class="dropdown-item" href="/admin/manage_complaint.php?action=update_status&status=In+Review&id=<?php echo $row['complaint_id']; ?>">Set to 'In Review'</a></li>
            <li><a class="dropdown-item" href="/admin/manage_complaint.php?action=update_status&status=Resolved&id=<?php echo $row['complaint_id']; ?>">Set to 'Resolved'</a></li>
            <li><a class="dropdown-item" href="/admin/manage_complaint.php?action=update_status&status=Dismissed&id=<?php echo $row['complaint_id']; ?>">Set to 'Dismissed'</a></li>
            <li><hr class="dropdown-divider"></li>
            <!-- Added a JavaScript confirmation before deleting -->
            <li><a class="dropdown-item text-danger" href="/admin/manage_complaint.php?action=delete&id=<?php echo $row['complaint_id']; ?>" onclick="return confirm('Are you sure you want to permanently delete this complaint?');">Delete Complaint</a></li>
        </ul>
    </div>
</td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No complaints found. Good job!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
</div> 
<footer class="volunteer-footer">
    <!-- Your existing footer code here -->
</footer>
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
      const mainContent = document.querySelector('.main-content-area');
      if (sidebar.classList.contains('active')) {
        mainContent.style.marginLeft = '250px'; 
      } else {
        mainContent.style.marginLeft = '0';
      }
    }

    // Counter-up animation script
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.metric-number');
        const animationDuration = 2000; // Duration in milliseconds

        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let startTime = null;

            const animate = (timestamp) => {
                if (!startTime) startTime = timestamp;
                const progress = timestamp - startTime;
                const currentNumber = Math.min(Math.floor((progress / animationDuration) * target), target);
                
                counter.innerText = currentNumber.toLocaleString(); // Add commas to numbers

                if (progress < animationDuration) {
                    requestAnimationFrame(animate);
                } else {
                    counter.innerText = target.toLocaleString(); // Ensure it ends on the exact number
                }
            };
            requestAnimationFrame(animate);
        });
    });
</script>

</body>
</html>