<?php
/**
 * Remove from Cart Action Handler
 * Processes requests to remove products from cart
 * Cart Management
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

    // Get and validate product ID
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        $response['status'] = 'error';
        $response['message'] = 'Product ID is required';
        echo json_encode($response);
        exit();
    }

    $product_id = (int)$_POST['product_id'];

    // Validate product ID
    if ($product_id <= 0) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid product ID';
        echo json_encode($response);
        exit();
    }

    // Remove from cart using controller
    $result = remove_from_cart_ctr($product_id, $customer_id);

    if ($result) {
        // Success - get updated cart count and total
        $cart_count = get_cart_item_count_ctr($customer_id);
        $cart_total = get_cart_total_ctr($customer_id);
        
        $response['status'] = 'success';
        $response['message'] = 'Product removed from cart successfully';
        $response['cart_count'] = $cart_count;
        $response['cart_total'] = number_format($cart_total, 2);
        
        error_log("Remove from cart success - Product: $product_id, Customer: $customer_id");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to remove product from cart. Please try again.';
        
        error_log("Remove from cart failed - Product: $product_id, Customer: $customer_id");
    }

} catch (Exception $e) {
    // Handle any unexpected errors
    error_log("Remove from cart exception: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['message'] = 'An error occurred while removing product from cart';
}

// Return JSON response
echo json_encode($response);
exit();
?>