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
}

// Attempt to add product through controller (without image first)
try {
    $product_id = add_product_ctr(
        $product_data['product_cat'],
        $product_data['product_brand'],
        $product_data['product_title'],
        $product_data['product_price'],
        $product_data['product_desc'],
        null, // Image will be added after
        $product_data['product_keywords']
    );
    
    if ($product_id) {
        // Now handle image upload with the product_id
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $user_id = get_user_id();
            
            // Use absolute path from document root
            $upload_base = __DIR__ . '/../uploads/';
            $user_folder = 'u' . $user_id . '/';
            $product_folder = 'p' . $product_id . '/';
            $full_path = $upload_base . $user_folder . $product_folder;
            
            // Create directories if they don't exist
            if (!file_exists($full_path)) {
                if (!mkdir($full_path, 0777, true)) {
                    error_log("Failed to create directory: " . $full_path);
                    // Product added but image failed - still return success
                    $response['status'] = 'success';
                    $response['message'] = 'Product added but image upload failed';
                    $response['product_id'] = $product_id;
                    echo json_encode($response);
                    exit();
                }
                // Set directory permissions
                chmod($full_path, 0777);
            }
            
            // Generate unique filename
            $file_ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $unique_filename = 'img_' . time() . '_' . uniqid() . '.' . $file_ext;
            $destination = $full_path . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                // Set file permissions
                chmod($destination, 0644);
                
                // Store relative path (for database and display)
                $image_path = 'uploads/' . $user_folder . $product_folder . $unique_filename;
                
                // Update product with image path
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
                
                error_log("Image uploaded successfully: " . $image_path);
            } else {
                error_log("Failed to move uploaded file to: " . $destination);
                error_log("Upload error code: " . $_FILES['product_image']['error']);
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Product "' . htmlspecialchars($product_data['product_title']) . '" added successfully';
        $response['product_id'] = $product_id;
        if (isset($image_path)) {
            $response['image_path'] = $image_path;
        }
        
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