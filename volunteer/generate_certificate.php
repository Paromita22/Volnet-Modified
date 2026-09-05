<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Security Check & Data Validation (This part is correct and remains the same)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer' || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id'])) {
    header("Location: /volunteer/profile.php?error=invalid_request");
    exit();
}

// 1. GATHER DATA (This part is correct and remains the same)
// ==================================================================
$user_id = $_SESSION['user_id'];
$event_id = (int)$_POST['event_id'];
$volunteer_id = null;
$volunteer_name = '';
$event_title = '';
$event_date = '';

$sql = "SELECT v.volunteer_id, v.name as volunteer_name, e.title as event_title, e.end_date
        FROM volunteer v
        JOIN volunteer_events ve ON v.volunteer_id = ve.volunteer_id
        JOIN eventt e ON ve.event_id = e.id
        WHERE v.user_id = ? AND e.id = ? AND ve.status = 'Attended'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $volunteer_id = $row['volunteer_id'];
    $volunteer_name = ucwords(strtolower($row['volunteer_name']));
    $event_title = $row['event_title'];
    $event_date = date("F j, Y", strtotime($row['end_date'])); // A cleaner date format
} else {
    header("Location: /volunteer/profile.php?error=data_not_found_or_not_attended");
    exit();
}
$stmt->close();

// Check if certificate already exists (This part is correct and remains the same)
$checkStmt = $conn->prepare("SELECT certificate_id FROM certificates WHERE volunteer_id = ? AND event_id = ?");
$checkStmt->bind_param("ii", $volunteer_id, $event_id);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    header("Location: /volunteer/profile.php?error=cert_exists");
    exit();
}
$checkStmt->close();

// 2. GENERATE THE CERTIFICATE IMAGE - SIMPLIFIED AND CORRECTED
// ==================================================================
// Paths to assets
$font_body_bold = __DIR__ . '/../assets/fonts/Montserrat-Bold.ttf'; // We'll use a bold font for the name
$font_body_regular = __DIR__ . '/../assets/fonts/Montserrat-Regular.ttf';
$template_image_path = __DIR__ . '/../assets/images/certificate_template.png'; // Use the ORIGINAL image

$font_script = __DIR__ . '/../assets/fonts/GreatVibes-Regular.ttf'; // ADD THIS LINE for the italic name


// Load the template image
$image = imagecreatefrompng($template_image_path);
if (!$image) {
    header("Location: /volunteer/profile.php?error=template_load_failed");
    exit();
}

// Get image dimensions for centering
$image_width = imagesx($image);

// Define colors
$color_black = imagecolorallocate($image, 50, 50, 50);
$color_white = imagecolorallocate($image, 255, 255, 255);

// --- A. Cover up ONLY the placeholder lines ---
// We use small, precise white boxes to hide the original lines.
// imagefilledrectangle(image, x1, y1, x2, y2, color)

//imagefilledrectangle($image, 250, 350, 600, 760, $color_white); // Covers the name line
//imagefilledrectangle($image, 180, 560, 340, 970, $color_white); // Covers the date line
//imagefilledrectangle($image, 500, 560, 680, 970, $color_white); // Covers the "Presented By" line


// --- B. Write the new, simplified text ---
// We only need to write three things now.





// 1. Volunteer's Name (Large and Italic)
$name_bbox = imagettfbbox(50, 0, $font_script, $volunteer_name);
$name_x = ($image_width - $name_bbox[2]) / 2; // Center it
imagettftext($image, 50, 0, $name_x,790, $color_black, $font_script, $volunteer_name);

// 2. Event Name (On the next line, smaller)
$event_text_line = "for contributing to the " . $event_title;
$event_bbox = imagettfbbox(30, 0, $font_body_bold, $event_text_line);
$event_x = ($image_width - $event_bbox[2]) / 2; // Center it
imagettftext($image, 30, 0, $event_x, 850, $color_black,  $font_body_bold, $event_text_line);








// 3. The Date (Bigger and Bolder)
imagettftext($image, 30, 0, 400, 1165, $color_black, $font_body_bold, $event_date);

// 4. Presented By "VolNet" (Bigger and Bolder)
imagettftext($image, 30, 0, 1375, 1165, $color_black, $font_body_bold, "VolNet");






// 3. SAVE THE FILE & UPDATE DATABASE
// ==================================================================
$output_folder = __DIR__ . '/../uploads/certificates/';
if (!is_dir($output_folder)) {
    mkdir($output_folder, 0775, true);
}
$file_name = 'cert_' . $volunteer_id . '_' . $event_id . '_' . time() . '.png';
$file_path = $output_folder . $file_name;
$web_db_path = '/uploads/certificates/' . $file_name;
imagepng($image, $file_path);
imagedestroy($image);

$insert_stmt = $conn->prepare("INSERT INTO certificates (volunteer_id, event_id, file_path) VALUES (?, ?, ?)");
$insert_stmt->bind_param("iis", $volunteer_id, $event_id, $web_db_path);
$insert_stmt->execute();
$insert_stmt->close();
$conn->close();

// 4. REDIRECT USER
// ==================================================================
header("Location: /volunteer/profile.php?cert_success=1");
exit();
?>