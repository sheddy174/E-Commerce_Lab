<?php
/**
 * Delete Product Action Handler
 * Processes product deletion requests
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

// Validate input
if (!isset($_POST['product_id']) || empty(trim($_POST['product_id']))) {
    $response['status'] = 'error';
    $response['message'] = 'Product ID is required';
    echo json_encode($response);
    exit();
}

$product_id = (int) trim($_POST['product_id']);

// Validate product ID
if ($product_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid product ID';
    echo json_encode($response);
    exit();
}

// Check if product exists
$existing_product = get_product_by_id_ctr($product_id);
if (!$existing_product) {
    $response['status'] = 'error';
    $response['message'] = 'Product not found';
    echo json_encode($response);
    exit();
}

// Attempt to delete product through controller
try {
    $success = delete_product_ctr($product_id);
    
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = 'Product "' . htmlspecialchars($existing_product['product_title']) . '" deleted successfully';
        $response['product_id'] = $product_id;
        
        error_log("Product deleted successfully - ID: " . $product_id . ", Title: " . $existing_product['product_title'] . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete product. Please try again.';
        
        error_log("Failed to delete product - ID: " . $product_id . ", User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Delete product exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while deleting product';
}

echo json_encode($response);
?>