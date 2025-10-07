<?php
/**
 * Delete Category Action Handler
 * Processes category deletion requests
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
if (!isset($_POST['cat_id']) || empty(trim($_POST['cat_id']))) {
    $response['status'] = 'error';
    $response['message'] = 'Category ID is required';
    echo json_encode($response);
    exit();
}

$cat_id = (int) trim($_POST['cat_id']);

// Validate category ID
if ($cat_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid category ID';
    echo json_encode($response);
    exit();
}

// Check if category exists
$existing_category = get_category_by_id_ctr($cat_id);
if (!$existing_category) {
    $response['status'] = 'error';
    $response['message'] = 'Category not found';
    echo json_encode($response);
    exit();
}

// Attempt to delete category through controller
try {
    $success = delete_category_ctr($cat_id);
    
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = 'Category "' . htmlspecialchars($existing_category['cat_name']) . '" deleted successfully';
        $response['cat_id'] = $cat_id;
        
        error_log("Category deleted successfully - ID: " . $cat_id . ", Name: " . $existing_category['cat_name'] . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete category. It may have associated products.';
        
        error_log("Failed to delete category - ID: " . $cat_id . ", User: " . get_user_email() . " (possibly has products)");
    }
    
} catch (Exception $e) {
    error_log("Delete category exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while deleting category';
}

echo json_encode($response);
?>