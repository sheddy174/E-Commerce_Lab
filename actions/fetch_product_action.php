<?php
/**
 * Fetch all products with category and brand information
 * UPDATED: Now includes "added_today" count + proper logging
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/product_controller.php';

$response = array();

try {
    // Check if user is admin
    if (!is_admin()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access';
        error_log("Unauthorized product fetch attempt - User: " . (get_user_email() ?: 'Not logged in'));
        echo json_encode($response);
        exit();
    }

    // Fetch all products
    $products = get_all_products_ctr();

    if ($products !== false) {
        // Get count of products added today
        $added_today = get_products_added_today_ctr();
        
        $response['status'] = 'success';
        $response['data'] = $products;
        $response['added_today'] = $added_today;
        $response['message'] = 'Products retrieved successfully';
        
        // Log successful fetch
        error_log("Products fetched successfully - Count: " . count($products) . ", Added Today: " . $added_today . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch products';
        $response['data'] = [];
        $response['added_today'] = 0;
        
        error_log("Failed to fetch products - User: " . get_user_email());
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    $response['added_today'] = 0;
    error_log("Product fetch error: " . $e->getMessage() . " - User: " . get_user_email());
}

echo json_encode($response);
?>