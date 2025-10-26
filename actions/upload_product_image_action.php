<?php
/**
 * Upload Product Image Action Handler
 * Handles product image uploads with organized folder structure
 * Structure: uploads/u{user_id}/p{product_id}/image_name.ext
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';

$response = array();

// Check if user is logged in and is admin
if (!is_logged_in()) {
    $response['status'] = 'error';
    $response['message'] = 'Please login to continue';
    echo json_encode($response);
    exit();
}

if (!is_admin()) {
    $response['status'] = 'error';
    $response['message'] = 'Admin access required';
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
if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] === UPLOAD_ERR_NO_FILE) {
    $response['status'] = 'error';
    $response['message'] = 'No image file uploaded';
    echo json_encode($response);
    exit();
}

// Check for upload errors
if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    $response['status'] = 'error';
    $response['message'] = 'Error uploading file: ' . $_FILES['product_image']['error'];
    echo json_encode($response);
    exit();
}

// Get file information
$file = $_FILES['product_image'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_error = $file['error'];

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

// Get user ID and product ID
$user_id = get_user_id();
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// For new products (product_id = 0), use temporary ID or timestamp
if ($product_id === 0) {
    $product_id = 'temp_' . time();
}

// Create directory structure: uploads/u{user_id}/p{product_id}/
$upload_base = __DIR__ . '/../uploads/';
$user_folder = 'u' . $user_id . '/';
$product_folder = 'p' . $product_id . '/';
$full_path = $upload_base . $user_folder . $product_folder;

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
    if (!mkdir($full_path, 0777, true)) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to create upload directory';
        error_log("Failed to create directory: " . $full_path);
        error_log("Parent writable: " . (is_writable(dirname($full_path)) ? 'yes' : 'no'));
        echo json_encode($response);
        exit();
    }
    // Set directory permissions explicitly
    chmod($full_path, 0777);
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
$unique_filename = 'img_' . time() . '_' . uniqid() . '.' . $file_ext;
$destination = $full_path . $unique_filename;

// Move uploaded file
if (move_uploaded_file($file_tmp, $destination)) {
    // Store relative path (without leading ../)
    $relative_path = 'uploads/' . $user_folder . $product_folder . $unique_filename;
    
    $response['status'] = 'success';
    $response['message'] = 'Image uploaded successfully';
    $response['image_path'] = $relative_path;
    $response['file_name'] = $unique_filename;
    
    error_log("Image uploaded successfully - Path: " . $relative_path . ", User: " . get_user_email());
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to move uploaded file';
    error_log("Failed to move uploaded file from " . $file_tmp . " to " . $destination);
}

echo json_encode($response);
?>