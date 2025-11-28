<?php
/**
 * Verify Paystack Payment and Create Order
 * UPDATED: Now sets proper payment_status and order_delivery_status
 */

session_start();
require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../controllers/cart_controller.php';
require_once '../controllers/order_controller.php';
// Set JSON header
header('Content-Type: application/json');
// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to proceed'
    ]);
    exit();
}

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    error_log("=== PAYSTACK VERIFY PAYMENT ===");
    error_log("Input data: " . json_encode($data));
    // Get reference from input
    $reference = isset($data['reference']) ? trim($data['reference']) : null;
    
    if (!$reference) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment reference is required',
            'verified' => false
        ]);
        exit();
    }
    
    $customer_id = get_user_id();
    $customer_name = get_user_name();
    $customer_email = get_user_email();
    
    error_log("Customer ID: $customer_id, Name: $customer_name, Email: $customer_email");
    
    // Verify payment with Paystack
    error_log("Calling Paystack verification API for reference: $reference");
    $verification = paystack_verify_transaction($reference);
    
    error_log("Paystack verification response: " . json_encode($verification));
    // Check if verification was successful
    if (!$verification || !isset($verification['status']) || $verification['status'] !== true) {
        $error_msg = isset($verification['message']) ? $verification['message'] : 'Payment verification failed';
        error_log("Verification failed: $error_msg");
        
        echo json_encode([
            'status' => 'error',
            'message' => $error_msg,
            'verified' => false
        ]);
        exit();
    }
    
    $payment_data = $verification['data'];
    // Verify payment status
    if ($payment_data['status'] !== 'success') {
        error_log("Payment status is not success: " . $payment_data['status']);
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment was not successful. Status: ' . ucfirst($payment_data['status']),
            'verified' => false
        ]);
        exit();
    }
    
    // Get cart items
    $cart_items = get_user_cart_ctr($customer_id);
    
    if (empty($cart_items)) {
        error_log("Cart is empty for customer: $customer_id");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Cart is empty. Please add items before checkout.',
            'verified' => false
        ]);
        exit();
    }
    
    error_log("Cart items count: " . count($cart_items));
    
    // Calculate total from cart
    $cart_total = 0;
    foreach ($cart_items as $item) {
        $cart_total += $item['product_price'] * $item['qty'];
    }
    
    // Verify amount matches
    $paid_amount = $payment_data['amount'] / 100;
    
    error_log("Cart total: GHS $cart_total, Paid amount: GHS $paid_amount");
    
    if (abs($paid_amount - $cart_total) > 0.01) {
        error_log("Amount mismatch! Paid: $paid_amount, Expected: $cart_total");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount does not match order total',
            'verified' => false,
            'expected' => number_format($cart_total, 2),
            'paid' => number_format($paid_amount, 2)
        ]);
        exit();
    }
    
    error_log("Amount verification passed");
    
    // Generate invoice number
    $invoice_no = generate_invoice_number_ctr();
    $order_date = date('Y-m-d');
    
    // UPDATED: Set proper statuses
    $order_status = 'Paid'; // Keep for backward compatibility
    $payment_status = 'completed'; // NEW: Payment is completed
    $delivery_status = 'pending'; // NEW: Delivery starts as pending
    
    error_log("Generated invoice: $invoice_no, Order date: $order_date");
    error_log("Statuses - Order: $order_status, Payment: $payment_status, Delivery: $delivery_status");
    
    // Create order
    error_log("Creating order...");
    $order_id = create_order_ctr($customer_id, $invoice_no, $order_date, $order_status, $payment_status, $delivery_status);
    
    if (!$order_id) {
        error_log("CRITICAL: Failed to create order for customer: $customer_id");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create order. Please contact support.',
            'verified' => true
        ]);
        exit();
    }
    
    error_log("Order created successfully - Order ID: $order_id");
    
    // Add order details (products)
    error_log("Adding order details...");
    $all_details_added = true;
    $details_count = 0;
    
    foreach ($cart_items as $item) {
        $result = add_order_details_ctr($order_id, $item['p_id'], $item['qty']);
        
        if (!$result) {
            error_log("CRITICAL: Failed to add order detail - Product: " . $item['p_id'] . ", Qty: " . $item['qty']);
            $all_details_added = false;
        } else {
            $details_count++;
            error_log("Order detail added - Product: " . $item['p_id'] . ", Qty: " . $item['qty']);
        }
    }
    
    error_log("Order details added: $details_count of " . count($cart_items));
    
    if (!$all_details_added) {
        error_log("WARNING: Some order details failed to add");
    }
    
    // Record payment with Paystack details
    error_log("Recording payment...");
    $payment_method = 'paystack';
    $transaction_ref = $payment_data['reference'];
    $authorization_code = isset($payment_data['authorization']['authorization_code']) ? 
                         $payment_data['authorization']['authorization_code'] : null;
    $payment_channel = isset($payment_data['channel']) ? $payment_data['channel'] : 'card';
    
    error_log("Payment details - Method: $payment_method, Ref: $transaction_ref, Channel: $payment_channel");
    
    $payment_id = record_payment_ctr(
        $paid_amount,
        $customer_id,
        $order_id,
        'GHS',
        $order_date,
        $payment_method,
        $transaction_ref,
        $authorization_code,
        $payment_channel
    );
    
    if (!$payment_id) {
        error_log("CRITICAL: Failed to record payment for order: $order_id");
    } else {
        error_log("Payment recorded successfully - Payment ID: $payment_id");
    }
    
    // Empty the cart
    error_log("Emptying cart...");
    $cart_emptied = empty_cart_ctr($customer_id);
    
    if (!$cart_emptied) {
        error_log("WARNING: Failed to empty cart for customer: $customer_id");
    } else {
        error_log("Cart emptied successfully");
    }
    
    // Clear session payment data
    unset($_SESSION['payment_reference']);
    unset($_SESSION['payment_amount']);
    
    error_log("=== ORDER COMPLETED SUCCESSFULLY ===");
    error_log("Order ID: $order_id, Invoice: $invoice_no, Amount: GHS $paid_amount");
    
    // Success response
    echo json_encode([
        'status' => 'success',
        'verified' => true,
        'message' => 'Payment verified and order created successfully',
        'order_id' => $order_id,
        'invoice_no' => $invoice_no,
        'order_date' => date('F j, Y'),
        'total_amount' => number_format($paid_amount, 2),
        'currency' => 'GHS',
        'items_count' => count($cart_items),
        'payment_reference' => $transaction_ref,
        'payment_method' => ucfirst($payment_channel),
        'customer_name' => $customer_name,
        'customer_email' => $customer_email
    ]);
    
} catch (Exception $e) {
    error_log("=== EXCEPTION IN PAYSTACK VERIFICATION ===");
    error_log("Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred during verification. Please contact support.',
        'verified' => false,
        'error_details' => $e->getMessage()
    ]);
}
?>