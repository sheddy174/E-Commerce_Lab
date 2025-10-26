<?php
/**
 * Update Brand Action Handler
 * Processes brand update requests
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

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Validate input
if (!isset($_POST['brand_id']) || !isset($_POST['brand_name']) || !isset($_POST['brand_cat']) ||
    empty(trim($_POST['brand_id'])) || empty(trim($_POST['brand_name'])) || empty($_POST['brand_cat'])) {
    $response['status'] = 'error';
    $response['message'] = 'Brand ID, name, and category are required';
    echo json_encode($response);
    exit();
}

$brand_id = (int) trim($_POST['brand_id']);
$brand_name = trim($_POST['brand_name']);
$brand_cat = (int) trim($_POST['brand_cat']);

// Validate brand ID
if ($brand_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid brand ID';
    echo json_encode($response);
    exit();
}

// Check if brand exists
$existing_brand = get_brand_by_id_ctr($brand_id);
if (!$existing_brand) {
    $response['status'] = 'error';
    $response['message'] = 'Brand not found';
    echo json_encode($response);
    exit();
}

// Validate new brand name length
if (strlen($brand_name) < 2) {
    $response['status'] = 'error';
    $response['message'] = 'Brand name must be at least 2 characters long';
    echo json_encode($response);
    exit();
}

if (strlen($brand_name) > 100) {
    $response['status'] = 'error';
    $response['message'] = 'Brand name must be less than 100 characters';
    echo json_encode($response);
    exit();
}

// Check for valid characters
if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $brand_name)) {
    $response['status'] = 'error';
    $response['message'] = 'Brand name can only contain letters, numbers, spaces, hyphens, and underscores';
    echo json_encode($response);
    exit();
}

// Validate category ID
if ($brand_cat <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid category ID';
    echo json_encode($response);
    exit();
}

// Attempt to update brand through controller
try {
    $success = update_brand_ctr($brand_id, $brand_name, $brand_cat);
    
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = 'Brand "' . htmlspecialchars($brand_name) . '" updated successfully';
        $response['brand_id'] = $brand_id;
        $response['brand_name'] = htmlspecialchars($brand_name);
        $response['brand_cat'] = $brand_cat;
        
        error_log("Brand updated successfully - ID: " . $brand_id . ", New Name: " . $brand_name . ", Category: " . $brand_cat . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update brand. This brand name may already exist in the selected category.';
        
        error_log("Failed to update brand - ID: " . $brand_id . ", Name: " . $brand_name . ", Category: " . $brand_cat . ", User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Update brand exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while updating brand';
}

echo json_encode($response);
?>