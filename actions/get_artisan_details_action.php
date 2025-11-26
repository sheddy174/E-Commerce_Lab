<?php
/**
 * Get Artisan Details
 * Returns detailed information about a specific artisan
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

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

// Validate input
if (!isset($_GET['artisan_id']) || empty($_GET['artisan_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'Artisan ID is required';
    echo json_encode($response);
    exit();
}

$artisan_id = (int)$_GET['artisan_id'];

// Get artisan details
try {
    $artisan = get_artisan_by_id_ctr($artisan_id);
    
    if ($artisan) {
        $response['status'] = 'success';
        $response['data'] = $artisan;
        $response['message'] = 'Artisan details retrieved successfully';
        
        error_log("Artisan details retrieved - ID: " . $artisan_id . ", Admin: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Artisan not found';
        
        error_log("Artisan not found - ID: " . $artisan_id);
    }
} catch (Exception $e) {
    error_log("Get artisan details error: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred';
}

echo json_encode($response);
?>