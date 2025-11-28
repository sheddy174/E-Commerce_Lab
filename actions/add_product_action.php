<?php
/**
 * Add Product Action Handler - CORRECTED VERSION
 * Works for both admin and artisan WITHOUT status column
 * FIXED: Removed extra artisan_id parameter from update_product_ctr call
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

$response = array();

// Check if user is logged in and is admin OR artisan
if (!is_logged_in()) {
    $response['status'] = 'error';
    $response['message'] = 'Please login to continue';
    echo json_encode($response);
    exit();
}

if (!is_admin() && !is_artisan()) {
    $response['status'] = 'error';
    $response['message'] = 'Admin or Artisan access required';
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

// Determine artisan_id if artisan (admin stays null)
$artisan_id = null;

if (is_artisan()) {
    require_once '../controllers/artisan_controller.php';
    
    // Get artisan profile
    $customer_id = get_user_id();
    $artisan_profile = get_artisan_profile_ctr($customer_id);
    
    if ($artisan_profile) {
        $artisan_id = $artisan_profile['artisan_id'];
        error_log("Artisan adding product - Artisan ID: {$artisan_id}");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Artisan profile not found. Please complete your profile first.';
        echo json_encode($response);
        exit();
    }
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

// Add product using standard function (works for both admin and artisan)
try {
    // Step 1: INSERT product with artisan_id
    $product_id = add_product_ctr(
        $product_data['product_cat'],
        $product_data['product_brand'],
        $product_data['product_title'],
        $product_data['product_price'],
        $product_data['product_desc'],
        null, // image_path (added later if image uploaded)
        $product_data['product_keywords'],
        $artisan_id  // NULL for admin, artisan_id for artisan
    );
    
    if ($product_id) {
        // Step 2: Handle image upload if provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $user_id = get_user_id();
            
            // uploads folder is at WEB ROOT, project is in subfolder
            $upload_base = dirname(dirname(__DIR__)) . '/uploads/';
            
            // Create user and product subfolders
            $user_folder = 'u' . $user_id . '/';
            $product_folder = 'p' . $product_id . '/';
            $full_path = $upload_base . $user_folder . $product_folder;
            
            // Check if uploads folder exists
            if (!is_dir($upload_base)) {
                error_log("ERROR: uploads folder not found at: " . $upload_base);
                $response['status'] = 'success';
                $response['message'] = is_artisan() 
                    ? 'Product added but image upload failed. Contact admin.'
                    : 'Product added but image upload failed: uploads folder not found';
                $response['product_id'] = $product_id;
                echo json_encode($response);
                exit();
            }
            
            // Check if writable
            if (!is_writable($upload_base)) {
                error_log("ERROR: uploads folder not writable: " . $upload_base);
                $response['status'] = 'success';
                $response['message'] = is_artisan()
                    ? 'Product added but image upload failed. Contact admin.'
                    : 'Product added but image upload failed: uploads folder not writable';
                $response['product_id'] = $product_id;
                echo json_encode($response);
                exit();
            }
            
            // Create subdirectories
            if (!is_dir($full_path)) {
                if (!mkdir($full_path, 0755, true)) {
                    error_log("Failed to create directory: " . $full_path);
                    $response['status'] = 'success';
                    $response['message'] = is_artisan()
                        ? 'Product added but image upload failed. Contact admin.'
                        : 'Product added but failed to create upload folders';
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
                
                // Store relative path for database
                $image_path = 'uploads/' . $user_folder . $product_folder . $unique_filename;
                
                // Step 3: UPDATE product with image path
                // FIXED: Removed artisan_id parameter - it's already set in database from INSERT
                update_product_ctr(
                    $product_id,
                    $product_data['product_cat'],
                    $product_data['product_brand'],
                    $product_data['product_title'],
                    $product_data['product_price'],
                    $product_data['product_desc'],
                    $image_path,
                    $product_data['product_keywords']
                    // artisan_id NOT needed here - already set during INSERT
                );
                
                error_log("Image uploaded successfully: " . $image_path);
                
                // Set appropriate success message
                if (is_artisan()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Product added successfully with image!';
                } else {
                    $response['status'] = 'success';
                    $response['message'] = 'Product "' . htmlspecialchars($product_data['product_title']) . '" added successfully with image';
                }
                $response['product_id'] = $product_id;
                $response['image_path'] = $image_path;
            } else {
                error_log("Failed to move uploaded file to: " . $destination);
                error_log("Temp file: " . $_FILES['product_image']['tmp_name']);
                
                $response['status'] = 'success';
                $response['message'] = is_artisan()
                    ? 'Product added but image upload failed. Contact admin.'
                    : 'Product added but image upload failed';
                $response['product_id'] = $product_id;
            }
        } else {
            // No image uploaded
            if (is_artisan()) {
                $response['status'] = 'success';
                $response['message'] = 'Product added successfully! You can add an image later.';
            } else {
                $response['status'] = 'success';
                $response['message'] = 'Product "' . htmlspecialchars($product_data['product_title']) . '" added successfully';
            }
            $response['product_id'] = $product_id;
        }
        
        error_log("Product added - ID: " . $product_id . ", Title: " . $product_data['product_title'] . (is_artisan() ? ", Artisan: {$artisan_id}" : ", Admin"));
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