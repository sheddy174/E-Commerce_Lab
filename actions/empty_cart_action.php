<?php
/**
 * Empty Cart Action Handler
 * Processes requests to clear all items from cart
 */

// Set JSON response header
header('Content-Type: application/json');

// Start session and include necessary files
session_start();
require_once '../settings/core.php';
require_once '../controllers/cart_controller.php';

// Initialize response array
$response = array();

try {
    // Check if user is logged in
    if (!is_logged_in()) {
        $response['status'] = 'error';
        $response['message'] = 'Please login to manage your cart';
        echo json_encode($response);
        exit();
    }

    // Get customer ID from session
    $customer_id = get_user_id();

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['status'] = 'error';
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit();
    }

    // Get cart items count before emptying
    $items_count = get_cart_item_count_ctr($customer_id);

    // Check if cart is already empty
    if ($items_count == 0) {
        $response['status'] = 'info';
        $response['message'] = 'Your cart is already empty';
        echo json_encode($response);
        exit();
    }

    // Empty cart using controller
    $result = empty_cart_ctr($customer_id);

    if ($result) {
        // Success
        $response['status'] = 'success';
        $response['message'] = 'Cart cleared successfully';
        $response['cart_count'] = 0;
        $response['cart_total'] = '0.00';
        
        error_log("Empty cart success - Customer: $customer_id, Items removed: $items_count");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to clear cart. Please try again.';
        
        error_log("Empty cart failed - Customer: $customer_id");
    }

} catch (Exception $e) {
    // Handle any unexpected errors
    error_log("Empty cart exception: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['message'] = 'An error occurred while clearing cart';
}

// Return JSON response
echo json_encode($response);
exit();
?>