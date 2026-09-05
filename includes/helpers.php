<?php
/**
 * VolNet General Utility Helpers
 */

/**
 * Ensure all upload directories exist and are writable
 */
function ensure_upload_directories() {
    $base_upload_dir = dirname(__DIR__) . '/uploads';
    $subdirs = ['badges', 'blogs', 'certificates', 'org_pics', 'past_events', 'profiles'];
    foreach ($subdirs as $dir) {
        $path = $base_upload_dir . '/' . $dir;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}

/**
 * Secure file upload handler
 * @param array $file $_FILES['input_name']
 * @param string $destination_subfolder e.g. 'profiles/' or 'org_pics/'
 * @param array $allowed_extensions e.g. ['jpg', 'jpeg', 'png', 'webp']
 * @param int $max_bytes
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function upload_file_safe($file, $destination_subfolder, $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'], $max_bytes = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'No file uploaded or upload error occurred.'];
    }

    if ($file['size'] > $max_bytes) {
        return ['success' => false, 'path' => null, 'error' => 'File size exceeds allowed limit.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions, true)) {
        return ['success' => false, 'path' => null, 'error' => 'Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions)];
    }

    $target_dir = dirname(__DIR__) . '/uploads/' . trim($destination_subfolder, '/') . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $unique_name = uniqid('upload_', true) . '.' . $ext;
    $target_path = $target_dir . $unique_name;
    $relative_path = 'uploads/' . trim($destination_subfolder, '/') . '/' . $unique_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'path' => $relative_path, 'error' => null];
    }

    return ['success' => false, 'path' => null, 'error' => 'Failed to move uploaded file.'];
}

// Automatically ensure upload dirs exist on include
ensure_upload_directories();
