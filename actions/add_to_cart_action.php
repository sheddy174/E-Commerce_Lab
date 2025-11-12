<?php
/**
 * Add to Cart Action Handler
 * Processes requests to add products to cart
 * Cart Management
 * Part of MVC architecture
 * Handles input validation, session checks, and invokes controller functions
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
        $response['message'] = 'Please login to add items to cart';
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

    // Get quantity (default to 1 if not provided)
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    // Validate quantity
    if ($qty <= 0) {
        $response['status'] = 'error';
        $response['message'] = 'Quantity must be greater than 0';
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

    // Get user's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Add to cart using controller
    $result = add_to_cart_ctr($product_id, $customer_id, $ip_address, $qty);

    if ($result) {
        // Success - get updated cart count
        $cart_count = get_cart_item_count_ctr($customer_id);
        
        $response['status'] = 'success';
        $response['message'] = 'Product added to cart successfully';
        $response['cart_count'] = $cart_count;
        
        error_log("Add to cart success - Product: $product_id, Customer: $customer_id, Qty: $qty");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add product to cart. Please try again.';
        
        error_log("Add to cart failed - Product: $product_id, Customer: $customer_id");
    }

} catch (Exception $e) {
    // Handle any unexpected errors
    error_log("Add to cart exception: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['message'] = 'An error occurred while adding product to cart';
}

// Return JSON response
echo json_encode($response);
exit();
?>