<?php
// 1. START SESSION & INCLUDE DB
session_start();
include __DIR__ . '/../includes/db.php'; // Make sure this path is correct and provides a $conn object

// 2. AUTHORIZATION CHECK: Must be a logged-in organization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
    // Redirect to login page with an error if not authorized
    header("Location: /auth/login.html?error=unauthorized_access");
    exit();
}

// 3. FETCH THE ORGANIZATION'S org_id FROM THE DATABASE
$user_id = $_SESSION['user_id'];
$org_id = null;

$stmt_org = $conn->prepare("SELECT org_id FROM organization WHERE user_id = ?");
$stmt_org->bind_param("i", $user_id);
$stmt_org->execute();
$result_org = $stmt_org->get_result();

if ($result_org->num_rows > 0) {
    $org_data = $result_org->fetch_assoc();
    $org_id = $org_data['org_id'];
}
$stmt_org->close();

// If for some reason an 'organization' user has no org profile, block them.
if (is_null($org_id)) {
    // This indicates a data inconsistency.
    die("Error: Your organization profile could not be found. Please contact support.");
}


// 4. HANDLE FORM SUBMISSION
$error_message = ''; // To store and display any errors
$success_message = ''; // To store and display success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect and sanitize form data
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $start_date = $_POST['start-date'] ?? null;
    $start_time = $_POST['start-time'] ?? null;
    $end_date = $_POST['end-date'] ?? null;
    $end_time = $_POST['end-time'] ?? null;
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $volunteers = !empty($_POST['volunteers']) ? (int)$_POST['volunteers'] : 0;
    $age_restriction = $_POST['age-restriction'] ?? '';
    $gender = $_POST['gender'] ?? 'Both';
    $skills = $_POST['skills'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $deadline = $_POST['deadline'] ?? null;
    $is_urgent = isset($_POST['is_urgent']) ? (int)$_POST['is_urgent'] : 0;

    // Validation
    if (empty($title) || empty($type) || empty($start_date) || empty($location)) {
        $error_message = "Please fill in all required fields (Title, Type, Start Date, Location).";
    } elseif (!empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
        $error_message = "Event End Date cannot be before the Start Date.";
    } elseif (!empty($deadline) && strtotime($deadline) > strtotime($end_date ?: $start_date)) {
        $error_message = "Application deadline cannot be after the event has already concluded.";
    } elseif ($volunteers < 1) {
        $error_message = "Number of volunteers needed must be at least 1.";
    } else {
        // Prepare the SQL query with is_urgent and created_at
        $sql = "INSERT INTO eventt (
            org_id, title, type, start_date, start_time, end_date, end_time,
            location, description, volunteers, age_restriction,
            gender, skills, contact, deadline, is_urgent, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters: 1 int, 14 strings, 1 int
            $stmt->bind_param(
              "issssssssisssssi",
              $org_id,
              $title,
              $type,
              $start_date,
              $start_time,
              $end_date,
              $end_time,
              $location,
              $description,
              $volunteers,
              $age_restriction,
              $gender,
              $skills,
              $contact,
              $deadline,
              $is_urgent
          );
          

            // Execute the query
            if ($stmt->execute()) {
                header("Location: /organization/dashboard.php?status=event_created");
                exit();
            } else {
                $error_message = "Error creating event: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="shortcut icon" href="/assets/images/icon3.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  
  <title>Create Event - VolNet</title>
  <link rel="stylesheet" href="/assets/css/createevent.css"/>
</head>
<body>
  <header class="custom-header">
    <nav class="navbar navbar-expand-lg px-3">
      <a class="navbar-brand fw-bold text-white" href="/organization/dashboard.php">VolNet</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
        aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
      </button>
  
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
          <li class="nav-item">
            <a class="nav-link" href="/organization/dashboard.php">Home</a>
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
  
        <div class="d-flex align-items-center gap-2">
            <!-- This part now works correctly because session_start() is at the top -->
            <span class="text-white">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Organization'); ?>!</span>
            <a href="/auth/logout.php" class="btn btn-dark">Logout</a>
        </div>
      </div>
    </nav>
  </header>
  
  <section class="form">
    <div class="form-container">
        <h2>Create Event</h2>

        <!-- Display Error Message if it exists -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- The form now posts to itself. The action attribute is empty. -->
        <form action="" method="POST">

          <fieldset>
            <legend>Basic Details</legend>
            
            <label for="title">Event Title:</label>
            <input class="in" type="text" id="title" name="title" required>
    
            <label for="eventType">Type of Event:</label>
            <select name="type" id="eventType" required>
              <option value="" disabled selected>-- Select Type --</option>
              <option value="Animal Care">Animal Care</option>
              <option value="climate Action">Climate Action</option>
              <option value="Elderly Care">Elderly Care</option>
              <option value="Women Empowerment">Women Empowerment</option>
              <option value="Child Care">Child Care</option>
              <option value="Special Needs Care">Special Needs Care</option>
              <option value="Others">Others</option>
            </select>
    
            <label for="start-date">Start Date & Time:</label>
            <input class="in" type="date" id="start-date" name="start-date" required>
            <input class="in" type="time" name="start-time" required>
    
            <label for="end-date">End Date & Time:</label>
            <input class="in" type="date" id="end-date" name="end-date" required>
            <input class="in" type="time" name="end-time" required>
    
            <label for="location">Event Location:</label>
            <input class="in" type="text" id="location" name="location" required>
    
            <label for="description">Event Description:</label>
            <textarea id="description" name="description" rows="4"></textarea>
          </fieldset>
    
          <fieldset>
            <legend>Volunteer Requirements</legend>
    
            <label for="volunteers">No. of Volunteers Needed:</label>
            <input class="in" type="number" id="volunteers" name="volunteers">
    
            <label for="age-restriction">Age Restriction:</label>
            <input class="in" type="text" id="age-restriction" name="age-restriction" placeholder="e.g., 18+">
    
            <label for="gender">Gender Preference:</label>
            <select id="gender" name="gender">
              <option value="Both">Both</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
    
            <label for="skills">Skills Required:</label>
            <textarea id="skills" name="skills" rows="2" placeholder="e.g., First Aid, Public Speaking"></textarea>
    
            <label for="contact">Contact (Email or Phone):</label>
            <input class="in" type="text" id="contact" name="contact">
    
            <label for="deadline">Registration Deadline:</label>
            <input class="in" type="date" id="deadline" name="deadline">
          </fieldset>
          <!-- Add this hidden input inside the form -->
          <input type="hidden" id="is_urgent" name="is_urgent" value="0">

          <!-- Urgent Button -->
          <button class="submitbutton" type="submit" name="submit" onclick="document.getElementById('is_urgent').value='1'" style="background-color: red">Urgent</button><br><br>

          <!-- Create Button -->
          <button class="submitbutton" type="submit" name="submit" onclick="document.getElementById('is_urgent').value='0'">Create Event</button>
        </form>
      </div>
  </section>

  <footer class="volunteer-footer">
    <div class="footer-content">
      <div class="footer-section about"><h3>About Us</h3><p>We're a community of changemakers dedicated to making the world a better place through volunteering and compassion.</p></div>
      <div class="footer-section links"><h3>Quick Links</h3><ul><li><a href="#">Events</a></li><li><a href="#">Join Us</a></li><li><a href="#">Contact</a></li></ul></div>
      <div class="footer-section contact"><h3>Contact</h3><p>Email: volnet@gmail.com</p><p>Phone: +123 456 7890</p><p>Address: 123 Hope Street, Kindness City</p></div>
      <div class="footer-section social"><h3>Follow Us</h3><div class="social-icons"><a href="#"><i class="fa-brands fa-square-facebook"></i></a><a href="#"><i class="fa-brands fa-instagram"></i></a><a href="#"><i class="fa-brands fa-twitter"></i></a></div></div>
    </div>
    <div class="footer-bottom">© 2025 VolNet | Made with ❤️ for a better tomorrow</div>
  </footer>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>