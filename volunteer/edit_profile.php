<?php
// 1. START SESSION & INCLUDE DB
session_start();
include __DIR__ . '/../includes/db.php';

// 2. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer') {
    header("Location: /auth/login.html?error=unauthorized");
    exit();
}

// 3. FETCH CURRENT DATA TO PRE-FILL THE FORM
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare(
    // MODIFIED: Added v.profile_pic_path to the query
    "SELECT u.email, v.name, v.age, v.mobile, v.address, v.skills, v.bio, v.profile_pic_path 
     FROM users u 
     LEFT JOIN volunteer v ON u.user_id = v.user_id 
     WHERE u.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// Assign to variables for easy use in the form
$name = htmlspecialchars($result['name'] ?? '');
$email = htmlspecialchars($result['email'] ?? '');
$age = htmlspecialchars($result['age'] ?? '');
$mobile = htmlspecialchars($result['mobile'] ?? '');
$address = htmlspecialchars($result['address'] ?? '');
$skills = htmlspecialchars($result['skills'] ?? '');
$bio = htmlspecialchars($result['bio'] ?? '');
// NEW: Get the profile picture path
$profile_pic = htmlspecialchars($result['profile_pic_path'] ?? '');

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Setup Account</title>
  <link rel="stylesheet" href="/assets/css/orgh.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #000; color: #fff; }
    .account-container { padding: 30px 20px; }
    .form-card { background-color: #1a1a1a; border: 1px solid #4b4847; border-radius: 8px; padding: 25px; }
    .form-card h3 { color: #9bd258; margin-bottom: 20px; font-weight: bold; }
    .form-label { color: #ddd; }
    .form-control, .form-control:focus { background-color: #333; color: #fff; border-color: #4b4847; }
    .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(155, 210, 88, 0.25); }
    .btn-save { background-color: #9bd258; color: #000; font-weight: bold; border: none; }
    .btn-save:hover { background-color: #b0f068; }
    /* NEW STYLE for profile picture */
    #profile-img-preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 3px solid #4b4847;
    }
  </style>
</head>
<body>

<!-- Your sidebar and header HTML remains the same... -->
<span class="menu-icon" onclick="toggleSidebar()">☰</span>
<aside class="sidebar" id="sidebar">
    <a href="#">Get certifications</a>
    <a href="#">History</a>
    <a href="#">Application Tracking</a>
    <a href="#">Saved for later</a>
    <a href="#">Setup account</a>
</aside>
<header class="custom-header"><!-- ...header content... --></header>


<div class="container account-container">
    <h1 class="text-center mb-5" style="color: #9bd258; font-weight: bold;">Setup Your Account</h1>
    
    <!-- MODIFIED: Added enctype for file uploads -->
    <form action="/volunteer/update_profile.php" method="POST" enctype="multipart/form-data" class="form-card">
        
        <!-- ============================================= -->
        <!-- == NEW: PROFILE PICTURE UPLOAD SECTION      == -->
        <!-- ============================================= -->
        <h3>Profile Picture</h3>
        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-center">
                <!-- Display current profile picture or a default one -->
                <img src="<?php echo !empty($profile_pic) ? $profile_pic : '/assets/images/logo_1.png'; ?>" 
                     alt="Profile Picture" 
                     id="profile-img-preview"
                     class="img-fluid rounded-circle">
            </div>
            <div class="col-md-9">
                <label for="profile_pic" class="form-label">Upload a new picture</label>
                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/png, image/jpeg, image/gif">
                <small class="form-text text-muted">A square image is recommended. Max size: 5MB.</small>
            </div>
        </div>
        <hr style="border-color: #4b4847;">
        <!-- ============================================= -->
        
        <h3>Personal Information</h3>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="age" class="form-label">Age</label>
                <input type="number" class="form-control" id="age" name="age" value="<?php echo $age; ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="mobile" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" id="mobile" name="mobile" value="<?php echo $mobile; ?>">
            </div>
             <div class="col-12 mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"><?php echo $address; ?></textarea>
            </div>
        </div>

        <h3 class="mt-4">Profile Details</h3>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="bio" class="form-label">Bio / About Me</label>
                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo $bio; ?></textarea>
            </div>
            <div class="col-12 mb-3">
                <label for="skills" class="form-label">Skills (comma-separated)</label>
                <textarea class="form-control" id="skills" name="skills" rows="3"><?php echo $skills; ?></textarea>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-save">Save Changes</button>
        </div>
    </form>
</div>

<!-- Your footer HTML remains the same... -->
<footer class="volunteer-footer"><!-- ...footer content... --></footer>
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        // ... your existing sidebar toggle function
    }

    // NEW: JavaScript for live image preview
    const profilePicInput = document.getElementById('profile_pic');
    const profilePicPreview = document.getElementById('profile-img-preview');

    if (profilePicInput && profilePicPreview) {
        profilePicInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    /*
      IMPORTANT: Your `update_profile.php` file must be updated to handle the file upload.
      Here is the logic you need to add to it:

      $user_id = $_SESSION['user_id'];
      $pic_path_to_db = null;

      // Check if a new picture was uploaded
      if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
          $upload_dir = '/uploads/profiles/';
          if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
          
          $file_info = pathinfo($_FILES['profile_pic']['name']);
          $extension = $file_info['extension'];
          $new_filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
          $destination = $upload_dir . $new_filename;
          
          if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
              $pic_path_to_db = $destination;
          }
      }

      // When building your SQL UPDATE query:
      if ($pic_path_to_db) {
          // If a new pic was uploaded, include it in the update
          $sql = "UPDATE volunteer SET name=?, ..., profile_pic_path=? WHERE user_id=?";
          $stmt->bind_param("...si", $name, ..., $pic_path_to_db, $user_id);
      } else {
          // Otherwise, update everything except the picture
          $sql = "UPDATE volunteer SET name=?, ... WHERE user_id=?";
          $stmt->bind_param("...i", $name, ..., $user_id);
      }
      $stmt->execute();
    */
</script>

</body>
</html>