<?php
/**
 * Fetch all brands with product counts
 * UPDATED: Now includes "added_today" count + proper logging
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/brand_controller.php';

$response = array();

try {
    // Check if user is admin
    if (!is_admin()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access';
        error_log("Unauthorized brand fetch attempt - User: " . (get_user_email() ?: 'Not logged in'));
        echo json_encode($response);
        exit();
    }

    // Fetch all brands with product counts
    $brands = get_brands_with_counts_ctr();

    if ($brands !== false) {
        // Get count of brands added today
        $added_today = get_brands_added_today_ctr();
        
        $response['status'] = 'success';
        $response['data'] = $brands;
        $response['added_today'] = $added_today;
        $response['message'] = 'Brands retrieved successfully';
        
        // Log successful fetch
        error_log("Brands fetched successfully - Count: " . count($brands) . ", Added Today: " . $added_today . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch brands';
        $response['data'] = [];
        $response['added_today'] = 0;
        
        error_log("Failed to fetch brands - User: " . get_user_email());
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    $response['added_today'] = 0;
    error_log("Brand fetch error: " . $e->getMessage() . " - User: " . get_user_email());
}

echo json_encode($response);
?>