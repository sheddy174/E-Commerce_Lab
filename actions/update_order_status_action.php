<?php
/**
 * Update Order Status Action Handler
 * Handles order status updates and tracking information
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/order_controller.php';

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

// Get and validate input
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : null;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

// Validate order ID
if ($order_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid order ID';
    echo json_encode($response);
    exit();
}

// Validate status
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid order status';
    echo json_encode($response);
    exit();
}

// Clean empty strings to null
if (empty($tracking_number)) {
    $tracking_number = null;
}
if (empty($notes)) {
    $notes = null;
}

try {
    // Update order status
    $result = update_order_status_ctr($order_id, $status, $tracking_number, $notes);
    
    if ($result) {
        $response['status'] = 'success';
        $response['message'] = 'Order status updated successfully to ' . ucfirst($status);
        
        error_log("Order #{$order_id} status updated to {$status} by admin " . get_user_email());
        
        // TODO: Send email notification to customer about status change
        // send_order_status_email($order_id, $status);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update order status. Please try again.';
        error_log("Failed to update order #{$order_id} status");
    }
    
} catch (Exception $e) {
    error_log("Update order status exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred. Please try again.';
}

echo json_encode($response);
?>