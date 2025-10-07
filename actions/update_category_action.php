<?php
/**
 * Update Category Action Handler
 * Processes category update requests
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
if (!isset($_POST['cat_id']) || !isset($_POST['cat_name']) || 
    empty(trim($_POST['cat_id'])) || empty(trim($_POST['cat_name']))) {
    $response['status'] = 'error';
    $response['message'] = 'Category ID and name are required';
    echo json_encode($response);
    exit();
}

$cat_id = (int) trim($_POST['cat_id']);
$cat_name = trim($_POST['cat_name']);

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

// Validate new category name (modified validation for updates)
$result = ['valid' => false, 'message' => ''];

// Check if empty
if (empty($cat_name)) {
    $response['status'] = 'error';
    $response['message'] = 'Category name is required';
    echo json_encode($response);
    exit();
}

// Check length
if (strlen($cat_name) < 2) {
    $response['status'] = 'error';
    $response['message'] = 'Category name must be at least 2 characters long';
    echo json_encode($response);
    exit();
}

if (strlen($cat_name) > 100) {
    $response['status'] = 'error';
    $response['message'] = 'Category name must be less than 100 characters';
    echo json_encode($response);
    exit();
}

// Check for valid characters
if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $cat_name)) {
    $response['status'] = 'error';
    $response['message'] = 'Category name can only contain letters, numbers, spaces, hyphens, and underscores';
    echo json_encode($response);
    exit();
}

// Check if new name already exists (excluding current category)
$existing_name = get_category_by_name_ctr($cat_name);
if ($existing_name && $existing_name['cat_id'] != $cat_id) {
    $response['status'] = 'error';
    $response['message'] = 'Category name already exists';
    echo json_encode($response);
    exit();
}

// Attempt to update category through controller
try {
    $success = update_category_ctr($cat_id, $cat_name);
    
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = 'Category "' . htmlspecialchars($cat_name) . '" updated successfully';
        $response['cat_id'] = $cat_id;
        $response['cat_name'] = htmlspecialchars($cat_name);
        
        error_log("Category updated successfully - ID: " . $cat_id . ", New Name: " . $cat_name . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update category. Name may already exist.';
        
        error_log("Failed to update category - ID: " . $cat_id . ", Name: " . $cat_name . ", User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Update category exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while updating category';
}

echo json_encode($response);
?>