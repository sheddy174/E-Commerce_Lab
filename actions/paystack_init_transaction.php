<?php
/**
 * Initialize Paystack Payment Transaction
 * This endpoint receives payment initialization requests from frontend
 */

session_start();
require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../controllers/cart_controller.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to proceed with payment'
    ]);
    exit();
}

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    error_log("=== PAYSTACK INIT TRANSACTION ===");
    error_log("Input data: " . json_encode($data));
    
    // Validate input
    if (!isset($data['email']) || empty($data['email'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email is required'
        ]);
        exit();
    }
    
    $customer_id = get_user_id();
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email address'
        ]);
        exit();
    }
    
    // Get cart items to calculate total
    $cart_items = get_user_cart_ctr($customer_id);
    
    if (empty($cart_items)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Your cart is empty'
        ]);
        exit();
    }
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['product_price'] * $item['qty'];
    }
    
    error_log("Total amount calculated: GHS " . $total_amount);
    
    // Generate unique reference
    $reference = 'GTUNES-' . $customer_id . '-' . time();
    
    // Initialize Paystack transaction
    $paystack_response = paystack_initialize_transaction($total_amount, $email, $reference);
    
    error_log("Paystack response: " . json_encode($paystack_response));
    
    // Check if initialization was successful
    if ($paystack_response && isset($paystack_response['status']) && $paystack_response['status'] === true) {
        // Store reference in session for verification
        $_SESSION['payment_reference'] = $reference;
        $_SESSION['payment_amount'] = $total_amount;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment initialized successfully',
            'authorization_url' => $paystack_response['data']['authorization_url'],
            'access_code' => $paystack_response['data']['access_code'],
            'reference' => $reference
        ]);
    } else {
        $error_message = isset($paystack_response['message']) ? $paystack_response['message'] : 'Failed to initialize payment';
        
        error_log("Paystack initialization failed: " . $error_message);
        
        echo json_encode([
            'status' => 'error',
            'message' => $error_message
        ]);
    }
    
} catch (Exception $e) {
    error_log("Exception in paystack_init_transaction: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>