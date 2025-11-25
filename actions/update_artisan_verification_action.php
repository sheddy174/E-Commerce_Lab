<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

$response = array();

// Check if user is admin
if (!is_admin()) {
    $response['status'] = 'error';
    $response['message'] = 'Admin access required';
    echo json_encode($response);
    exit();
}

// Validate input
if (!isset($_POST['artisan_id']) || !isset($_POST['status'])) {
    $response['status'] = 'error';
    $response['message'] = 'Missing required fields';
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
$result = update_verification_status_ctr($artisan_id, $status);

if ($result) {
    $response['status'] = 'success';
    $response['message'] = 'Artisan ' . ($status === 'verified' ? 'approved' : 'rejected') . ' successfully';
    log_activity("Artisan ID {$artisan_id} status changed to {$status}", 'info');
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to update verification status';
}

echo json_encode($response);
?>