<?php
/**
 * Fetch Products Action Handler
 * Retrieves all products for display
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

try {
    // Get all products with category and brand information
    $products = get_all_products_ctr();
    
    if ($products !== false) {
        $response['status'] = 'success';
        $response['message'] = 'Products retrieved successfully';
        $response['data'] = $products;
        $response['count'] = count($products);
        $response['added_today'] = get_products_added_today_ctr(); // ADD THIS LINE
        error_log("Products fetched successfully - Count: " . count($products) . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve products';
        $response['data'] = [];
        $response['count'] = 0;
        
        error_log("Failed to fetch products, User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Fetch products exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while retrieving products';
    $response['data'] = [];
    $response['count'] = 0;
}

echo json_encode($response);
?>