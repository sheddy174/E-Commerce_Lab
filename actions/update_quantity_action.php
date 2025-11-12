<?php
/**
 * Update Quantity Action Handler
 * Processes requests to update product quantity in cart
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

    // Get and validate quantity
    if (!isset($_POST['qty'])) {
        $response['status'] = 'error';
        $response['message'] = 'Quantity is required';
        echo json_encode($response);
        exit();
    }

    $qty = (int)$_POST['qty'];

    // Validate quantity (can be 0 to remove item)
    if ($qty < 0) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid quantity';
        echo json_encode($response);
        exit();
    }

    // Maximum quantity per product
    if ($qty > 99) {
        $response['status'] = 'error';
        $response['message'] = 'Maximum quantity per product is 99';
        echo json_encode($response);
        exit();
    }

    // Update cart quantity using controller
    $result = update_cart_item_ctr($product_id, $customer_id, $qty);

    if ($result) {
        // Success - get updated cart count and total
        $cart_count = get_cart_item_count_ctr($customer_id);
        $cart_total = get_cart_total_ctr($customer_id);
        
        // Calculate item subtotal (get product price)
        $cart_items = get_user_cart_ctr($customer_id);
        $item_subtotal = 0;
        
        foreach ($cart_items as $item) {
            if ($item['p_id'] == $product_id) {
                $item_subtotal = $item['qty'] * $item['product_price'];
                break;
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = $qty == 0 ? 'Product removed from cart' : 'Cart updated successfully';
        $response['cart_count'] = $cart_count;
        $response['cart_total'] = number_format($cart_total, 2);
        $response['item_subtotal'] = number_format($item_subtotal, 2);
        $response['new_qty'] = $qty;
        
        error_log("Update cart quantity success - Product: $product_id, Customer: $customer_id, New Qty: $qty");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update cart. Please try again.';
        
        error_log("Update cart quantity failed - Product: $product_id, Customer: $customer_id");
    }

} catch (Exception $e) {
    // Handle any unexpected errors
    error_log("Update quantity exception: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['message'] = 'An error occurred while updating cart';
}

// Return JSON response
echo json_encode($response);
exit();
?>