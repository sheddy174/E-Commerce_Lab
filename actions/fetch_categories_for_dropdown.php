<?php
/**
 * Fetch categories for dropdown (accessible by admin AND artisans)
 * Used for product creation forms
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/category_controller.php';

$response = array();

try {
    // Check if user is logged in (admin OR artisan)
    if (!is_logged_in()) {
        $response['status'] = 'error';
        $response['message'] = 'Please login to continue';
        echo json_encode($response);
        exit();
    }

    // Artisans and admins can both READ categories
    if (!is_admin() && !is_artisan()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access';
        error_log("Unauthorized category fetch - User: " . get_user_email());
        echo json_encode($response);
        exit();
    }

    // Fetch all categories (simple list for dropdown)
    require_once '../classes/category_class.php';
    $category = new Category();
    $categories = $category->getAllCategories();

    if ($categories !== false) {
        $response['status'] = 'success';
        $response['data'] = $categories;
        $response['message'] = 'Categories retrieved successfully';
        
        error_log("Categories fetched for dropdown - Count: " . count($categories) . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch categories';
        $response['data'] = [];
        
        error_log("Failed to fetch categories - User: " . get_user_email());
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    error_log("Category fetch error: " . $e->getMessage());
}

echo json_encode($response);
?>