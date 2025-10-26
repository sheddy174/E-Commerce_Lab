<?php
/**
 * Add Product Action Handler
 * Processes product creation requests with image upload
 * CORRECTED for shared server with uploads/ at web root
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

// Handle image upload validation
$image_path = null;

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['product_image'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    if (!in_array($file_ext, $allowed_extensions)) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions);
        echo json_encode($response);
        exit();
    }
    
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
        null,
        $product_data['product_keywords']
    );
    
    if ($product_id) {
        // Now handle image upload with the product_id
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $user_id = get_user_id();
            
            // CRITICAL FIX: uploads folder is at WEB ROOT, project is in subfolder
            // Use __DIR__ to get current directory, then go UP twice to reach web root
            $upload_base = dirname(dirname(__DIR__)) . '/uploads/';
            
            // Create user and product subfolders
            $user_folder = 'u' . $user_id . '/';
            $product_folder = 'p' . $product_id . '/';
            $full_path = $upload_base . $user_folder . $product_folder;
            
            // Check if uploads folder exists
            if (!is_dir($upload_base)) {
                error_log("ERROR: uploads folder not found at: " . $upload_base);
                $response['status'] = 'success';
                $response['message'] = 'Product added but image upload failed: uploads folder not found';
                $response['product_id'] = $product_id;
                $response['debug'] = array(
                    'upload_base' => $upload_base,
                    'current_dir' => __DIR__,
                    'exists' => 'no'
                );
                echo json_encode($response);
                exit();
            }
            
            // Check if writable
            if (!is_writable($upload_base)) {
                error_log("ERROR: uploads folder not writable: " . $upload_base);
                $response['status'] = 'success';
                $response['message'] = 'Product added but image upload failed: uploads folder not writable';
                $response['product_id'] = $product_id;
                echo json_encode($response);
                exit();
            }
            
            // Create subdirectories
            if (!is_dir($full_path)) {
                if (!mkdir($full_path, 0755, true)) {
                    error_log("Failed to create directory: " . $full_path);
                    $response['status'] = 'success';
                    $response['message'] = 'Product added but failed to create upload folders';
                    $response['product_id'] = $product_id;
                    echo json_encode($response);
                    exit();
                }
                chmod($full_path, 0755);
            }
            
            // Generate unique filename
            $file_ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $unique_filename = 'img_' . time() . '_' . uniqid() . '.' . $file_ext;
            $destination = $full_path . $unique_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                chmod($destination, 0644);
                
                // Store relative path for database (web-accessible path)
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
                
                $response['status'] = 'success';
                $response['message'] = 'Product "' . htmlspecialchars($product_data['product_title']) . '" added successfully with image';
                $response['product_id'] = $product_id;
                $response['image_path'] = $image_path;
            } else {
                error_log("Failed to move uploaded file to: " . $destination);
                error_log("Temp file: " . $_FILES['product_image']['tmp_name']);
                error_log("Temp exists: " . (file_exists($_FILES['product_image']['tmp_name']) ? 'yes' : 'no'));
                
                $response['status'] = 'success';
                $response['message'] = 'Product added but image upload failed';
                $response['product_id'] = $product_id;
                $response['debug'] = array(
                    'destination' => $destination,
                    'temp_file' => $_FILES['product_image']['tmp_name'],
                    'temp_exists' => file_exists($_FILES['product_image']['tmp_name']) ? 'yes' : 'no',
                    'dest_writable' => is_writable(dirname($destination)) ? 'yes' : 'no'
                );
            }
        } else {
            // No image uploaded
            $response['status'] = 'success';
            $response['message'] = 'Product "' . htmlspecialchars($product_data['product_title']) . '" added successfully';
            $response['product_id'] = $product_id;
        }
        
        error_log("Product added - ID: " . $product_id . ", Title: " . $product_data['product_title']);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add product. Please try again.';
        error_log("Failed to add product: " . $product_data['product_title']);
    }
    
} catch (Exception $e) {
    error_log("Add product exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while adding product';
}

echo json_encode($response);
?>