<?php
/**
 * Fetch Categories for Dropdown Action Handler
 * Retrieves all categories for populating dropdown menus
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/category_controller.php';

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
    // Get all categories (simple list, no counts needed for dropdown)
    $categories = get_all_categories_ctr();
    
    if ($categories !== false) {
        $response['status'] = 'success';
        $response['message'] = 'Categories retrieved successfully';
        $response['data'] = $categories;
        $response['count'] = count($categories);
        
        error_log("Categories for dropdown fetched successfully - Count: " . count($categories));
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to retrieve categories';
        $response['data'] = [];
        $response['count'] = 0;
        
        error_log("Failed to fetch categories for dropdown");
    }
    
} catch (Exception $e) {
    error_log("Fetch categories for dropdown exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while retrieving categories';
    $response['data'] = [];
    $response['count'] = 0;
}

echo json_encode($response);
?>