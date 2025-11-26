<?php
/**
 * Update Artisan Verification Status
 * Allows admin to approve/reject artisan applications
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

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Validate inputs
if (!isset($_POST['artisan_id']) || empty($_POST['artisan_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'Artisan ID is required';
    echo json_encode($response);
    exit();
}

if (!isset($_POST['status']) || empty($_POST['status'])) {
    $response['status'] = 'error';
    $response['message'] = 'Status is required';
    echo json_encode($response);
    exit();
}

$artisan_id = (int)$_POST['artisan_id'];
$status = trim($_POST['status']);

// Validate status
if (!in_array($status, ['verified', 'rejected', 'pending'])) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid status';
    echo json_encode($response);
    exit();
}

// Update verification status
try {
    $result = update_artisan_verification_ctr($artisan_id, $status);
    
    if ($result) {
        $response['status'] = 'success';
        $response['message'] = 'Artisan ' . ($status === 'verified' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'updated')) . ' successfully';
        
        error_log("Artisan verification updated - ID: " . $artisan_id . ", Status: " . $status . ", Admin: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update verification status';
        
        error_log("Failed to update artisan verification - ID: " . $artisan_id);
    }
} catch (Exception $e) {
    error_log("Update artisan verification error: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred';
}

echo json_encode($response);
?>