<?php
/**
 * Add Product Action Handler
 * Processes product creation requests with image upload
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

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

// Collect product data
$product_data = array(
    'product_title' => isset($_POST['product_title']) ? trim($_POST['product_title']) : '',
    'product_cat' => isset($_POST['product_cat']) ? (int)$_POST['product_cat'] : 0,
    'product_brand' => isset($_POST['product_brand']) ? (int)$_POST['product_brand'] : 0,
    'product_price' => isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0,
    'product_desc' => isset($_POST['product_desc']) ? trim($_POST['product_desc']) : '',
    'product_keywords' => isset($_POST['product_keywords']) ? trim($_POST['product_keywords']) : ''
);

// Validate product data
$validation = validate_product_ctr($product_data);
if (!$validation['valid']) {
    $response['status'] = 'error';
    $response['message'] = $validation['message'];
    echo json_encode($response);
    exit();
}

// Handle image upload
$image_path = null;

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    // Get file information
    $file = $_FILES['product_image'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Allowed file extensions
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    // Validate file extension
    if (!in_array($file_ext, $allowed_extensions)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions);
        echo json_encode($response);
        exit();
    }
    
    // Validate file size (5MB max)
    $max_file_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_file_size) {
        $response['status'] = 'error';
        $response['message'] = 'File too large. Maximum size is 5MB';
        echo json_encode($response);
        exit();
    }
    
    // First, add product to get product_id, then upload image
    // We'll do a two-step process: add product without image, then update with image
    
} else {
    // No image uploaded, use default or null
    $image_path = null; // or set a default image path
}

// Attempt to add product through controller
try {
    // Add product first (with or without image)
    $product_id = add_product_ctr(
        $product_data['product_cat'],
        $product_data['product_brand'],
        $product_data['product_title'],
        $product_data['product_price'],
        $product_data['product_desc'],
        $image_path,
        $product_data['product_keywords']
    );
    
    if ($product_id) {
        // If image was uploaded, now handle the upload with the product_id
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $user_id = get_user_id();
            $upload_base = '../uploads/';
            $user_folder = 'u' . $user_id . '/';
            $product_folder = 'p' . $product_id . '/';
            $full_path = $upload_base . $user_folder . $product_folder;
            
            // Create directories
            if (!file_exists($full_path)) {
                mkdir($full_path, 0755, true);
            }
            
            // Generate unique filename
            $file_ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid('img_', true) . '.' . $file_ext;
            $destination = $full_path . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                // Update product with image path
                $image_path = 'uploads/' . $user_folder . $product_folder . $unique_filename;
                
                // Update product with image
                update_product_ctr(
                    $product_id,
                    $product_data['product_cat'],
                    $product_data['product_brand'],
                    $product_data['product_title'],
                    $product_data['product_price'],
                    $product_data['product_desc'],
                    $image_path,
                    $product_data['product_keywords']
                );
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Product "' . htmlspecialchars($product_data['product_title']) . '" added successfully';
        $response['product_id'] = $product_id;
        
        error_log("Product added successfully - ID: " . $product_id . ", Title: " . $product_data['product_title'] . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add product. Please try again.';
        
        error_log("Failed to add product: " . $product_data['product_title'] . ", User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Add product exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while adding product';
}

echo json_encode($response);
?>