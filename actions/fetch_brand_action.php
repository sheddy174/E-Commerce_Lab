<?php
/**
 * Fetch Brands Action Handler
 * Retrieves all brands for display
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/brand_controller.php';

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
    // Get brands with product counts
    $brands = get_brands_with_counts_ctr();
    
    if ($brands !== false) {
        $response['status'] = 'success';
        $response['message'] = 'Brands retrieved successfully';
        $response['data'] = $brands;
        $response['count'] = count($brands);
        $response['added_today'] = get_brands_added_today_ctr(); // ADD THIS LINE
        
        error_log("Brands fetched successfully - Count: " . count($brands) . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve brands';
        $response['data'] = [];
        $response['count'] = 0;
        
        error_log("Failed to fetch brands, User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Fetch brands exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while retrieving brands';
    $response['data'] = [];
    $response['count'] = 0;
}

echo json_encode($response);
?>