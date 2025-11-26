<?php
/**
 * Fetch all categories with product counts
 * UPDATED: Now includes "added_today" count + proper logging
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/category_controller.php';

$response = array();

try {
    // Check if user is admin
    if (!is_admin()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access';
        error_log("Unauthorized category fetch attempt - User: " . (get_user_email() ?: 'Not logged in'));
        echo json_encode($response);
        exit();
    }

    // Fetch all categories with product counts
    $categories = get_categories_with_counts_ctr();

    if ($categories !== false) {
        // Get count of categories added today
        $added_today = get_categories_added_today_ctr();
        
        $response['status'] = 'success';
        $response['data'] = $categories;
        $response['added_today'] = $added_today;
        $response['message'] = 'Categories retrieved successfully';
        
        // Log successful fetch
        error_log("Categories fetched successfully - Count: " . count($categories) . ", Added Today: " . $added_today . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch categories';
        $response['data'] = [];
        $response['added_today'] = 0;
        
        error_log("Failed to fetch categories - User: " . get_user_email());
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    $response['added_today'] = 0;
    error_log("Category fetch error: " . $e->getMessage() . " - User: " . get_user_email());
}

echo json_encode($response);
?>