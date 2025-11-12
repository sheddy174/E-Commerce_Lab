<?php
/**
 * Process Checkout Action Handler
 * Handles the backend processing of checkout after simulated payment
 * Moves items from cart to orders, orderdetails, and payment tables
 */

// Set JSON response header
header('Content-Type: application/json');

// Start session and include necessary files
session_start();
require_once '../settings/core.php';
require_once '../controllers/cart_controller.php';
require_once '../controllers/order_controller.php';
require_once '../settings/db_class.php';

// Initialize response array
$response = array();

try {
    // Check if user is logged in
    if (!is_logged_in()) {
        $response['status'] = 'error';
        $response['message'] = 'Please login to complete checkout';
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

    // Get cart items
    $cart_items = get_user_cart_ctr($customer_id);

    // Check if cart is empty
    if (empty($cart_items)) {
        $response['status'] = 'error';
        $response['message'] = 'Your cart is empty. Please add items before checkout.';
        echo json_encode($response);
        exit();
    }

    // Validate cart items (ensure all products are still available)
    $validation = validate_cart_ctr($customer_id);
    if (!$validation['valid']) {
        $response['status'] = 'error';
        $response['message'] = 'Some items in your cart are no longer available. Please review your cart.';
        echo json_encode($response);
        exit();
    }

    // Calculate total amount
    $total_amount = get_cart_total_ctr($customer_id);

    if ($total_amount <= 0) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid cart total. Please try again.';
        echo json_encode($response);
        exit();
    }

    // Start database transaction for data integrity
    $db = new db_connection();
    $db->db_connect();
    $db->begin_transaction();

    try {
        // Step 1: Generate unique invoice number
        $invoice_no = generate_invoice_number_ctr();
        
        if (!$invoice_no) {
            throw new Exception('Failed to generate invoice number');
        }

        // Step 2: Create order
        $order_date = date('Y-m-d');
        $order_status = 'Pending';
        
        $order_id = create_order_ctr($customer_id, $invoice_no, $order_date, $order_status);
        
        if (!$order_id) {
            throw new Exception('Failed to create order');
        }

        error_log("Order created - Order ID: $order_id, Invoice: $invoice_no");

        // Step 3: Add order details (all cart items)
        foreach ($cart_items as $item) {
            $added = add_order_details_ctr($order_id, $item['p_id'], $item['qty']);
            
            if (!$added) {
                throw new Exception('Failed to add order details for product ID: ' . $item['p_id']);
            }
        }

        error_log("Order details added - Order ID: $order_id, Items: " . count($cart_items));

        // Step 4: Record payment
        $payment_date = date('Y-m-d');
        $currency = 'GHS';
        
        $payment_id = record_payment_ctr($total_amount, $customer_id, $order_id, $currency, $payment_date);
        
        if (!$payment_id) {
            throw new Exception('Failed to record payment');
        }

        error_log("Payment recorded - Payment ID: $payment_id, Amount: $total_amount");

        // Step 5: Empty the cart
        $cart_emptied = empty_cart_ctr($customer_id);
        
        if (!$cart_emptied) {
            // Log warning but don't fail the transaction
            error_log("Warning: Failed to empty cart after successful checkout - Customer: $customer_id");
        }

        // Commit transaction
        $db->commit_transaction();

        // Success response
        $response['status'] = 'success';
        $response['message'] = 'Order placed successfully!';
        $response['order_id'] = $order_id;
        $response['invoice_no'] = $invoice_no;
        $response['total_amount'] = number_format($total_amount, 2);
        $response['currency'] = $currency;
        $response['order_date'] = date('F j, Y', strtotime($order_date));
        $response['items_count'] = count($cart_items);
        
        error_log("Checkout completed successfully - Order ID: $order_id, Customer: $customer_id, Amount: $total_amount");

    } catch (Exception $e) {
        // Rollback transaction on any error
        $db->rollback_transaction();
        
        error_log("Checkout transaction failed: " . $e->getMessage());
        
        throw $e; // Re-throw to outer catch block
    }

} catch (Exception $e) {
    // Handle any unexpected errors
    error_log("Checkout exception: " . $e->getMessage());
    
    $response['status'] = 'error';
    $response['message'] = 'Checkout failed: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);
exit();
?>