<?php
/**
 * Get Order Details Action Handler
 * Retrieves full order information for display
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/order_controller.php';

$response = array();

// Check if user is logged in
if (!is_logged_in()) {
    $response['status'] = 'error';
    $response['message'] = 'Please login to continue';
    echo json_encode($response);
    exit();
}

// Get order ID from query parameter
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    $response['status'] = 'error';
    $response['message'] = 'Invalid order ID';
    echo json_encode($response);
    exit();
}

try {
    // Get order details
    $order = get_order_by_id_ctr($order_id);
    
    if ($order) {
        // If not admin, verify the order belongs to the logged-in customer
        if (!is_admin()) {
            $customer_id = get_user_id();
            if ($order['customer_id'] != $customer_id) {
                $response['status'] = 'error';
                $response['message'] = 'Unauthorized access to this order';
                echo json_encode($response);
                exit();
            }
        }
        
        $response['status'] = 'success';
        $response['data'] = $order;
        $response['message'] = 'Order details retrieved successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Order not found';
        error_log("Order #{$order_id} not found");
    }
    
} catch (Exception $e) {
    error_log("Get order details exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'System error occurred. Please try again.';
}

echo json_encode($response);
?>