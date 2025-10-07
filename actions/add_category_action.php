<?php
/**
 * Add Category Action Handler
 * Processes category creation requests
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

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Validate input
if (!isset($_POST['cat_name']) || empty(trim($_POST['cat_name']))) {
    $response['status'] = 'error';
    $response['message'] = 'Category name is required';
    echo json_encode($response);
    exit();
}

$cat_name = trim($_POST['cat_name']);

// Validate category name through controller
$validation = validate_category_name_ctr($cat_name);
if (!$validation['valid']) {
    $response['status'] = 'error';
    $response['message'] = $validation['message'];
    echo json_encode($response);
    exit();
}

// Attempt to add category through controller
try {
    $cat_id = add_category_ctr($cat_name);
    
    if ($cat_id) {
        $response['status'] = 'success';
        $response['message'] = 'Category "' . htmlspecialchars($cat_name) . '" added successfully';
        $response['cat_id'] = $cat_id;
        $response['cat_name'] = htmlspecialchars($cat_name);
        
        error_log("Category added successfully - ID: " . $cat_id . ", Name: " . $cat_name . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add category. Category name may already exist.';
        
        error_log("Failed to add category: " . $cat_name . ", User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Add category exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while adding category';
}

echo json_encode($response);
?>