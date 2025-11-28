<?php
/**
 * Upload Artisan Profile Image Action Handler
 * CORRECTED VERSION - Includes database update after upload
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';

$response = array();

// Check if user is logged in
if (!is_logged_in()) {
    $response['status'] = 'error';
    $response['message'] = 'Please login to continue';
    echo json_encode($response);
    exit();
}

// Check if user is artisan
if (!is_artisan()) {
    $response['status'] = 'error';
    $response['message'] = 'Artisan access required';
    echo json_encode($response);
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
    $response['status'] = 'error';
    $response['message'] = 'No image file uploaded';
    echo json_encode($response);
    exit();
}

// Check for upload errors
if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    $response['status'] = 'error';
    $response['message'] = 'Error uploading file: ' . $_FILES['profile_image']['error'];
    echo json_encode($response);
    exit();
}

// Get file information
$file = $_FILES['profile_image'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];

// Get file extension
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Allowed file extensions
$allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');

// Validate file extension
if (!in_array($file_ext, $allowed_extensions)) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowed_extensions);
    echo json_encode($response);
    exit();
}

// Validate file size (5MB max)
$max_file_size = 5 * 1024 * 1024; // 5MB in bytes
if ($file_size > $max_file_size) {
    $response['status'] = 'error';
    $response['message'] = 'File too large. Maximum size is 5MB';
    echo json_encode($response);
    exit();
}

// Get customer ID
$customer_id = get_user_id();

// CRITICAL: uploads/ is at web root, project is in E-Commerce_Lab/
// From actions/ folder, go UP to E-Commerce_Lab/, then UP to web root, then into uploads/
$upload_base = dirname(dirname(__DIR__)) . '/uploads/';
$user_folder = 'u' . $customer_id . '/';
$profile_folder = 'artisan/profile/';
$full_path = $upload_base . $user_folder . $profile_folder;

// Verify upload base exists
if (!file_exists($upload_base)) {
    $response['status'] = 'error';
    $response['message'] = 'Upload directory does not exist. Please contact administrator.';
    error_log("Upload base directory missing: " . $upload_base);
    echo json_encode($response);
    exit();
}

// Create directories if they don't exist
if (!file_exists($full_path)) {
    if (!mkdir($full_path, 0755, true)) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to create upload directory';
        error_log("Failed to create directory: " . $full_path);
        echo json_encode($response);
        exit();
    }
    chmod($full_path, 0755);
}

// Verify directory is writable
if (!is_writable($full_path)) {
    $response['status'] = 'error';
    $response['message'] = 'Upload directory is not writable';
    error_log("Directory not writable: " . $full_path);
    echo json_encode($response);
    exit();
}

// Generate unique filename to prevent overwrites
$unique_filename = 'profile_' . time() . '_' . uniqid() . '.' . $file_ext;
$destination = $full_path . $unique_filename;

// Move uploaded file
if (move_uploaded_file($file_tmp, $destination)) {
    // Set file permissions
    chmod($destination, 0644);
    
    // Store relative path (for database and web access)
    $relative_path = 'uploads/' . $user_folder . $profile_folder . $unique_filename;
    
    // CRITICAL FIX: Update database with image path
    require_once '../controllers/customer_controller.php';
    
    $update_result = update_customer_image_ctr($customer_id, $relative_path);
    
    if ($update_result) {
        $response['status'] = 'success';
        $response['message'] = 'Profile image uploaded successfully';
        $response['image_path'] = $relative_path;
        
        error_log("Profile image uploaded and saved - Customer: {$customer_id}, Path: {$relative_path}");
    } else {
        // File uploaded but database update failed
        $response['status'] = 'warning';
        $response['message'] = 'Image uploaded to server but failed to save to database. Please contact administrator.';
        $response['image_path'] = $relative_path;
        
        error_log("CRITICAL: Image uploaded but DB update failed - Customer: {$customer_id}, Path: {$relative_path}");
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to move uploaded file';
    error_log("Failed to move uploaded file from " . $file_tmp . " to " . $destination);
}

echo json_encode($response);
?>