<?php
/**
 * Add Product Action Handler
 * Handles uploading image and saving product data
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

// Validate required fields
$required_fields = ['product_cat', 'product_brand', 'product_title', 'product_price', 'product_desc', 'product_keywords'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $response['status'] = 'error';
        $response['message'] = "Missing field: $field";
        echo json_encode($response);
        exit();
    }
}

// Collect form data
$product_cat = (int)$_POST['product_cat'];
$product_brand = (int)$_POST['product_brand'];
$product_title = trim($_POST['product_title']);
$product_price = (float)$_POST['product_price'];
$product_desc = trim($_POST['product_desc']);
$product_keywords = trim($_POST['product_keywords']);
$image_path = null;

try {
    // Image upload section
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($file_ext, $allowed)) {
            throw new Exception('Invalid image format');
        }

        // 5MB limit
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large (max 5MB)');
        }

        // Absolute uploads path (shared server)
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $unique_name = 'img_' . time() . '_' . uniqid() . '.' . $file_ext;
        $destination = $upload_dir . $unique_name;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            chmod($destination, 0644);
            $image_path = 'uploads/' . $unique_name;
        } else {
            throw new Exception('Failed to move uploaded file');
        }
    }

    // Insert into DB
    $result = add_product_ctr(
        $product_cat,
        $product_brand,
        $product_title,
        $product_price,
        $product_desc,
        $image_path,
        $product_keywords
    );

    if ($result) {
        $response['status'] = 'success';
        $response['message'] = 'Product added successfully';
        $response['image_path'] = $image_path;
    } else {
        throw new Exception('Database insertion failed');
    }

} catch (Exception $e) {
    error_log("Add Product Error: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
