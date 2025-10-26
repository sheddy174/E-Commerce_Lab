<?php
/**
 * Add Brand Action Handler
 * Processes brand creation requests
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
if (!isset($_POST['brand_name']) || empty(trim($_POST['brand_name']))) {
    $response['status'] = 'error';
    $response['message'] = 'Brand name is required';
    echo json_encode($response);
    exit();
}

if (!isset($_POST['brand_cat']) || empty($_POST['brand_cat']) || !is_numeric($_POST['brand_cat'])) {
    $response['status'] = 'error';
    $response['message'] = 'Category is required';
    echo json_encode($response);
    exit();
}

$brand_name = trim($_POST['brand_name']);
$brand_cat = (int) trim($_POST['brand_cat']);

// Validate brand name and category through controller
$validation = validate_brand_ctr($brand_name, $brand_cat);
if (!$validation['valid']) {
    $response['status'] = 'error';
    $response['message'] = $validation['message'];
    echo json_encode($response);
    exit();
}

// Attempt to add brand through controller
try {
    $brand_id = add_brand_ctr($brand_name, $brand_cat);
    
    if ($brand_id) {
        $response['status'] = 'success';
        $response['message'] = 'Brand "' . htmlspecialchars($brand_name) . '" added successfully';
        $response['brand_id'] = $brand_id;
        $response['brand_name'] = htmlspecialchars($brand_name);
        $response['brand_cat'] = $brand_cat;
        
        error_log("Brand added successfully - ID: " . $brand_id . ", Name: " . $brand_name . ", Category: " . $brand_cat . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add brand. This brand may already exist in the selected category.';
        
        error_log("Failed to add brand: " . $brand_name . " (Cat: " . $brand_cat . "), User: " . get_user_email());
    }
    
} catch (Exception $e) {
    error_log("Add brand exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred while adding brand';
}

echo json_encode($response);
?>