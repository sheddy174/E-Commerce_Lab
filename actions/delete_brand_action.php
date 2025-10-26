<?php
/**
 * Delete Brand Action Handler
 * Processes brand deletion requests
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
if (!isset($_POST['brand_id']) || empty(trim($_POST['brand_id']))) {
    $response['status'] = 'error';
    $response['message'] = 'Brand ID is required';
    echo json_encode($response);
    exit();
}

$brand_id = (int) trim($_POST['brand_id']);

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

// Attempt to delete brand through controller
try {
    $success = delete_brand_ctr($brand_id);
    
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = 'Brand "' . htmlspecialchars($existing_brand['brand_name']) . '" deleted successfully';
        $response['brand_id'] = $brand_id;
        
        error_log("Brand deleted successfully - ID: " . $brand_id . ", Name: " . $existing_brand['brand_name'] . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete brand. It may have associated products.';
        
        error_log("Failed to delete brand - ID: " . $brand_id . ", User: " . get_user_email() . " (possibly has products)");
    }
    
} catch (Exception $e) {
    error_log("Delete brand exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while deleting brand';
}

echo json_encode($response);
?>