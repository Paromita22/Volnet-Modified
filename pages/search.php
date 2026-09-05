<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$query = trim($_GET['q'] ?? '');
$events = [];
$blogs = [];
$organizations = [];

if (!empty($query)) {
    $search_term = "%" . $query . "%";

    // 1. Search Events
    $sql_events = "SELECT e.*, o.org_name 
                   FROM eventt e 
                   LEFT JOIN organization o ON e.org_id = o.org_id 
                   WHERE e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR e.type LIKE ? OR e.skills LIKE ?
                   ORDER BY e.created_at DESC";
    $stmt_e = $conn->prepare($sql_events);
    if ($stmt_e) {
        $stmt_e->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt_e->execute();
        $res_e = $stmt_e->get_result();
        while ($row = $res_e->fetch_assoc()) {
            $events[] = $row;
        }
        $stmt_e->close();
    }

    // 2. Search Blogs
    $sql_blogs = "SELECT b.*, u.username 
                  FROM blogs b 
                  LEFT JOIN users u ON b.user_id = u.user_id 
                  WHERE b.title LIKE ? OR b.content LIKE ? OR u.username LIKE ?
                  ORDER BY b.created_at DESC";
    $stmt_b = $conn->prepare($sql_blogs);
    if ($stmt_b) {
        $stmt_b->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt_b->execute();
        $res_b = $stmt_b->get_result();
        while ($row = $res_b->fetch_assoc()) {
            $blogs[] = $row;
        }
        $stmt_b->close();
    }

    // 3. Search Organizations
    $sql_org = "SELECT * FROM organization 
                WHERE org_name LIKE ? OR description LIKE ? OR address LIKE ?
                ORDER BY org_id DESC";
    $stmt_o = $conn->prepare($sql_org);
    if ($stmt_o) {
        $stmt_o->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt_o->execute();
        $res_o = $stmt_o->get_result();
        while ($row = $res_o->fetch_assoc()) {
            $organizations[] = $row;
        }
        $stmt_o->close();
    }
}

$total_results = count($events) + count($blogs) + count($organizations);

// Home navigation depending on session
$home_url = '/pages/homepage.php';
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'volunteer': $home_url = '/volunteer/dashboard.php'; break;
        case 'organization': $home_url = '/organization/dashboard.php'; break;
        case 'admin': $home_url = '/admin/dashboard.php'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results - VolNet</title>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/homepage.css">
  <style>
    body { background-color: #121212; color: #fff; }
    .search-header-box { background: linear-gradient(135deg, #1e1e1e 0%, #2a2a2a 100%); padding: 40px 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #333; }
    .result-card { background-color: #1e1e1e; border: 1px solid #333; border-radius: 10px; transition: transform 0.2s, box-shadow 0.2s; }
    .result-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.5); border-color: #449f1a; }
    .badge-category { background-color: #449f1a; color: #fff; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; }
    .search-input-lg { background-color: #2a2a2a; border: 1px solid #444; color: #fff; font-size: 1.1rem; }
    .search-input-lg:focus { background-color: #333; border-color: #449f1a; color: #fff; box-shadow: 0 0 0 0.25rem rgba(68, 159, 26, 0.25); }
  </style>
</head>
<body>

<!-- Header -->
<header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
        <a class="navbar-brand fw-bold text-white" href="<?php echo $home_url; ?>" style="margin-left: 25px;">
            <img src="/assets/images/logo_1.png" alt="VolNet Logo" style="height: 35px; margin-right: 8px;">VolNet
        </a>
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                <li class="nav-item"><a class="nav-link" href="<?php echo $home_url; ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/projects.php">Projects</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/blogs.php">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/contactus.php">Contact</a></li>
            </ul>
            <form class="d-flex me-3" role="search" action="/pages/search.php" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($query); ?>" required>
                <button class="btn btn-outline-dark bg-white" type="submit"><i class="fa fa-search"></i></button>
            </form>
            <div class="d-flex gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/auth/logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <button class="btn btn-dark" onclick="window.location.href='/auth/login.html'">Sign In</button>
                    <button class="btn btn-dark" onclick="window.location.href='/auth/register.html'">Sign Up</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<div class="container my-5">
  <!-- Search Form Banner -->
  <div class="search-header-box text-center">
    <h2 class="mb-3">Search VolNet</h2>
    <form action="/pages/search.php" method="GET" class="col-md-8 mx-auto d-flex gap-2">
      <input type="search" name="q" class="form-control search-input-lg" placeholder="Search events, causes, blogs, organizations..." value="<?php echo htmlspecialchars($query); ?>" required autofocus>
      <button type="submit" class="btn btn-success px-4"><i class="fa fa-search me-1"></i> Search</button>
    </form>
    <?php if (!empty($query)): ?>
      <p class="text-muted mt-3 mb-0">Found <strong><?php echo $total_results; ?></strong> results for "<em><?php echo htmlspecialchars($query); ?></em>"</p>
    <?php endif; ?>
  </div>

  <?php if (empty($query)): ?>
    <div class="text-center py-5">
      <i class="fa-solid fa-magnifying-glass fa-3x text-muted mb-3"></i>
      <h4>Enter a search term above to find events, stories, and organizations.</h4>
    </div>
  <?php elseif ($total_results === 0): ?>
    <div class="alert alert-dark text-center py-4 border-secondary">
      <i class="fa-regular fa-face-frown fa-2x mb-2"></i>
      <h4>No results found for "<?php echo htmlspecialchars($query); ?>"</h4>
      <p class="text-muted mb-0">Try searching for keywords like "Flood", "Education", "Clean", "Health", or organization names.</p>
    </div>
  <?php else: ?>

    <!-- 1. EVENTS SECTION -->
    <?php if (count($events) > 0): ?>
      <h3 class="mb-3 text-success"><i class="fa-solid fa-calendar-check me-2"></i> Volunteering Events (<?php echo count($events); ?>)</h3>
      <div class="row g-4 mb-5">
        <?php foreach ($events as $ev): ?>
          <div class="col-md-6">
            <div class="card result-card h-100 text-white p-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title text-success mb-0"><?php echo htmlspecialchars($ev['title']); ?></h5>
                <span class="badge-category"><?php echo htmlspecialchars($ev['type'] ?? 'General'); ?></span>
              </div>
              <p class="card-text text-light small mb-3"><?php echo htmlspecialchars(substr($ev['description'], 0, 140)) . '...'; ?></p>
              <div class="row g-2 small text-muted mb-3">
                <div class="col-6"><i class="fa-solid fa-location-dot text-success me-1"></i> <?php echo htmlspecialchars($ev['location']); ?></div>
                <div class="col-6"><i class="fa-regular fa-calendar text-success me-1"></i> <?php echo htmlspecialchars($ev['start_date']); ?></div>
                <div class="col-6"><i class="fa-solid fa-users text-success me-1"></i> <?php echo (int)$ev['volunteers']; ?> volunteers needed</div>
                <div class="col-6"><i class="fa-solid fa-building text-success me-1"></i> <?php echo htmlspecialchars($ev['org_name'] ?? 'Organization'); ?></div>
              </div>
              <div class="mt-auto pt-2 border-top border-secondary text-end">
                <a href="/pages/view_events.php?type=<?php echo urlencode($ev['type']); ?>" class="btn btn-sm btn-outline-success">View Event Category</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- 2. BLOGS SECTION -->
    <?php if (count($blogs) > 0): ?>
      <h3 class="mb-3 text-primary"><i class="fa-solid fa-newspaper me-2"></i> Community Blog Posts (<?php echo count($blogs); ?>)</h3>
      <div class="row g-4 mb-5">
        <?php foreach ($blogs as $b): ?>
          <div class="col-md-4">
            <div class="card result-card h-100 text-white overflow-hidden">
              <?php if (!empty($b['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($b['image_path']); ?>" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Blog Thumbnail">
              <?php endif; ?>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-light"><?php echo htmlspecialchars($b['title']); ?></h5>
                <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars(substr($b['content'], 0, 120)) . '...'; ?></p>
                <div class="pt-2 border-top border-secondary d-flex justify-content-between align-items-center">
                  <small class="text-muted"><i class="fa-regular fa-user me-1"></i> <?php echo htmlspecialchars($b['username'] ?? 'Volunteer'); ?></small>
                  <a href="/pages/blog_details.php?id=<?php echo $b['blog_id']; ?>" class="btn btn-sm btn-primary">Read Post</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- 3. ORGANIZATIONS SECTION -->
    <?php if (count($organizations) > 0): ?>
      <h3 class="mb-3 text-warning"><i class="fa-solid fa-building me-2"></i> Organizations (<?php echo count($organizations); ?>)</h3>
      <div class="row g-4 mb-5">
        <?php foreach ($organizations as $org): ?>
          <div class="col-md-4">
            <div class="card result-card h-100 text-white p-3">
              <h5 class="text-warning"><?php echo htmlspecialchars($org['org_name']); ?></h5>
              <p class="small text-light mb-2"><?php echo htmlspecialchars(substr($org['description'] ?? 'Active NGO Partner', 0, 120)) . '...'; ?></p>
              <div class="small text-muted mt-auto pt-2 border-top border-secondary">
                <div><i class="fa-solid fa-location-dot me-1 text-warning"></i> <?php echo htmlspecialchars($org['address']); ?></div>
                <div><i class="fa-solid fa-envelope me-1 text-warning"></i> <?php echo htmlspecialchars($org['contact_email']); ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<!-- Footer -->
<footer class="volunteer-footer">
  <div class="footer-content">
    <div class="footer-section about">
      <h3>About VolNet</h3>
      <p>Empowering communities by connecting volunteers and organizations worldwide.</p>
    </div>
    <div class="footer-section links">
      <h3>Quick Links</h3>
      <ul>
        <li><a href="<?php echo $home_url; ?>">Home</a></li>
        <li><a href="/pages/projects.php">Projects</a></li>
        <li><a href="/pages/blogs.php">Blog</a></li>
        <li><a href="/pages/contactus.php">Contact</a></li>
      </ul>
    </div>
    <div class="footer-section contact">
      <h3>Contact</h3>
      <p>Email: contact@volnet.org</p>
      <p>Phone: +880 1800 000 000</p>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; 2026 VolNet | Made with ❤️ for a better tomorrow
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
