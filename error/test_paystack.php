<?php
/**
 * PAYSTACK VERIFICATION DIAGNOSTIC
 * This will test your Paystack API connection and verification
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Paystack Verification Diagnostic</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: white; padding: 10px; border: 1px solid #ddd; }
    h2 { background: #2E86AB; color: white; padding: 10px; }
</style>";

// Step 1: Check if paystack_config.php exists
echo "<h2>Step 1: Checking Configuration Files</h2>";

if (file_exists('../settings/paystack_config.php')) {
    echo "<p class='success'>✅ paystack_config.php found</p>";
    require_once '../settings/paystack_config.php';
} else {
    echo "<p class='error'>❌ paystack_config.php NOT FOUND!</p>";
    echo "<p>Expected location: E-Commerce_Lab/settings/paystack_config.php</p>";
    exit;
}

// Step 2: Check if constants are defined
echo "<h2>Step 2: Checking Paystack Constants</h2>";

if (defined('PAYSTACK_SECRET_KEY')) {
    $key_preview = substr(PAYSTACK_SECRET_KEY, 0, 7) . '...' . substr(PAYSTACK_SECRET_KEY, -4);
    echo "<p class='success'>✅ PAYSTACK_SECRET_KEY defined: {$key_preview}</p>";
    
    if (strpos(PAYSTACK_SECRET_KEY, 'sk_test_') === 0) {
        echo "<p class='warning'>⚠️ Using TEST key (sk_test_...)</p>";
    } elseif (strpos(PAYSTACK_SECRET_KEY, 'sk_live_') === 0) {
        echo "<p class='success'>✅ Using LIVE key (sk_live_...)</p>";
    } else {
        echo "<p class='error'>❌ Invalid key format! Should start with sk_test_ or sk_live_</p>";
    }
} else {
    echo "<p class='error'>❌ PAYSTACK_SECRET_KEY not defined!</p>";
}

if (defined('PAYSTACK_PUBLIC_KEY')) {
    $pub_key_preview = substr(PAYSTACK_PUBLIC_KEY, 0, 7) . '...' . substr(PAYSTACK_PUBLIC_KEY, -4);
    echo "<p class='success'>✅ PAYSTACK_PUBLIC_KEY defined: {$pub_key_preview}</p>";
} else {
    echo "<p class='error'>❌ PAYSTACK_PUBLIC_KEY not defined!</p>";
}

// Step 3: Check if cURL is available
echo "<h2>Step 3: Checking cURL Availability</h2>";

if (function_exists('curl_init')) {
    echo "<p class='success'>✅ cURL is installed</p>";
    
    $curl_version = curl_version();
    echo "<p>cURL version: {$curl_version['version']}</p>";
    echo "<p>SSL version: {$curl_version['ssl_version']}</p>";
} else {
    echo "<p class='error'>❌ cURL is NOT installed! Cannot make API calls to Paystack.</p>";
    exit;
}

// Step 4: Check if verification function exists
echo "<h2>Step 4: Checking Verification Functions</h2>";

if (function_exists('paystack_verify_transaction')) {
    echo "<p class='success'>✅ paystack_verify_transaction() function exists</p>";
} else {
    echo "<p class='error'>❌ paystack_verify_transaction() function NOT FOUND!</p>";
}

if (function_exists('paystack_initialize_transaction')) {
    echo "<p class='success'>✅ paystack_initialize_transaction() function exists</p>";
} else {
    echo "<p class='warning'>⚠️ paystack_initialize_transaction() function NOT FOUND</p>";
}

// Step 5: Test API Connection (using a test reference)
echo "<h2>Step 5: Testing Paystack API Connection</h2>";

if (!defined('PAYSTACK_SECRET_KEY')) {
    echo "<p class='error'>Cannot test - Secret key not defined</p>";
} else {
    echo "<p>Testing connection to Paystack API...</p>";
    
    $test_url = "https://api.paystack.co/transaction";
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "<p>HTTP Response Code: <strong>{$http_code}</strong></p>";
    
    if ($curl_error) {
        echo "<p class='error'>❌ cURL Error: {$curl_error}</p>";
    } elseif ($http_code == 200) {
        echo "<p class='success'>✅ Successfully connected to Paystack API!</p>";
        echo "<p>API is responding correctly.</p>";
    } elseif ($http_code == 401) {
        echo "<p class='error'>❌ Authentication failed! Your secret key is invalid.</p>";
    } else {
        echo "<p class='error'>❌ Unexpected response code: {$http_code}</p>";
    }
    
    if ($response) {
        echo "<h3>API Response Sample:</h3>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
    }
}

// Step 6: Test verification with the actual failed reference
echo "<h2>Step 6: Test Verification with Failed Reference</h2>";

echo "<p>Let's try to verify the reference from your screenshot: <strong>GTUNES-2-1764462104</strong></p>";

if (function_exists('paystack_verify_transaction') && defined('PAYSTACK_SECRET_KEY')) {
    $test_reference = 'GTUNES-2-1764462104';
    
    echo "<p>Attempting to verify reference: {$test_reference}</p>";
    
    $verify_result = paystack_verify_transaction($test_reference);
    
    echo "<h3>Verification Result:</h3>";
    echo "<pre>";
    print_r($verify_result);
    echo "</pre>";
    
    if ($verify_result && isset($verify_result['status'])) {
        if ($verify_result['status'] === true) {
            echo "<p class='success'>✅ Verification function returned SUCCESS!</p>";
            
            if (isset($verify_result['data']['status'])) {
                $status = $verify_result['data']['status'];
                echo "<p>Payment Status: <strong>{$status}</strong></p>";
                
                if ($status === 'success') {
                    echo "<p class='success'>✅ Payment was actually SUCCESSFUL on Paystack!</p>";
                    echo "<p class='warning'>⚠️ This means the problem is in your callback handling, not Paystack!</p>";
                }
            }
        } else {
            echo "<p class='error'>❌ Verification returned FALSE</p>";
            echo "<p>Message: " . ($verify_result['message'] ?? 'No error message') . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Verification function returned invalid response</p>";
    }
} else {
    echo "<p class='error'>Cannot test - verification function or secret key missing</p>";
}

// Step 7: Summary
echo "<h2>Step 7: Summary & Recommendations</h2>";

echo "<div style='background: #fff; padding: 15px; border: 2px solid #2E86AB;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Share your <code>view/payment_callback.php</code> file</li>";
echo "<li>Share your <code>settings/paystack_config.php</code> file (hide actual keys)</li>";
echo "<li>Check your PHP error logs for detailed error messages</li>";
echo "<li>If verification test above succeeded, problem is in callback handling</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Diagnostic Complete!</strong> Time: " . date('Y-m-d H:i:s') . "</p>";
?>