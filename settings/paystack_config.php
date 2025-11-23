<?php
/**
 * Paystack Configuration for GhanaTunes
 * Secure payment gateway settings
 */

// Paystack API Keys
define('PAYSTACK_SECRET_KEY', 'sk_test_de0f60ace6f98ed87085012ff3d3958e7c90267a');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_ee6698b8378ff4bc29632460092037afa0dc7f69');

// Paystack URLs
define('PAYSTACK_API_URL', 'https://api.paystack.co');
define('PAYSTACK_INIT_ENDPOINT', PAYSTACK_API_URL . '/transaction/initialize');
define('PAYSTACK_VERIFY_ENDPOINT', PAYSTACK_API_URL . '/transaction/verify/');

// Application Settings
define('APP_ENVIRONMENT', 'test'); // Change to 'live' in production
define('PAYSTACK_CALLBACK_URL', 'http://169.239.251.102:442/~shadrack.berdah/view/payment_callback.php');


/**
 * Initialize a Paystack transaction
 * 
 * @param float $amount Amount in GHS (will be converted to pesewas)
 * @param string $email Customer email
 * @param string $reference Optional reference
 * @return array Response with 'status' and 'data'
 */
function paystack_initialize_transaction($amount, $email, $reference = null) {
    // Generate unique reference if not provided
    if (!$reference) {
        $reference = 'GTUNES-' . time() . '-' . uniqid();
    }
    
    // Convert GHS to pesewas (1 GHS = 100 pesewas)
    $amount_in_pesewas = round($amount * 100);
    
    $data = [
        'amount' => $amount_in_pesewas,
        'email' => $email,
        'reference' => $reference,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'currency' => 'GHS',
        'metadata' => [
            'app' => 'GhanaTunes',
            'environment' => APP_ENVIRONMENT,
            'custom_fields' => [
                [
                    'display_name' => 'Platform',
                    'variable_name' => 'platform',
                    'value' => 'GhanaTunes E-Commerce'
                ]
            ]
        ]
    ];
    
    error_log("Paystack Init Request: " . json_encode($data));
    
    $response = paystack_api_request('POST', PAYSTACK_INIT_ENDPOINT, $data);
    
    return $response;
}

/**
 * Verify a Paystack transaction
 * 
 * @param string $reference Transaction reference
 * @return array Response with transaction details
 */
function paystack_verify_transaction($reference) {
    $response = paystack_api_request('GET', PAYSTACK_VERIFY_ENDPOINT . $reference);
    
    return $response;
}

/**
 * Make a request to Paystack API
 * 
 * @param string $method HTTP method (GET, POST)
 * @param string $url Full API endpoint URL
 * @param array $data Optional data to send
 * @return array API response decoded as array
 */
function paystack_api_request($method, $url, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    // Set headers
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Cache-Control: no-cache'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Send data for POST/PUT requests
    if ($method !== 'GET' && $data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    // Handle curl errors
    if ($curl_error) {
        error_log("Paystack API CURL Error: $curl_error");
        return [
            'status' => false,
            'message' => 'Connection error: ' . $curl_error
        ];
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    // Log for debugging
    error_log("Paystack API Response (HTTP $http_code): " . json_encode($result));
    
    return $result;
}

/**
 * Get currency symbol
 */
function get_currency_symbol($currency = 'GHS') {
    $symbols = [
        'GHS' => 'GHS',
        'USD' => '$',
        'EUR' => '€',
        'NGN' => '₦'
    ];
    
    return $symbols[$currency] ?? $currency;
}
?>  