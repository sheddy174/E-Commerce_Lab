<?php
/**
 * ORDER CONTROLLER FUNCTIONS DIAGNOSTIC
 * Tests which functions exist and their parameters
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Order Controller Functions Diagnostic</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: white; padding: 10px; border: 1px solid #ddd; }
    h2 { background: #2E86AB; color: white; padding: 10px; margin-top: 20px; }
    .code { background: #f0f0f0; padding: 5px; font-family: 'Courier New'; }
</style>";

// Load controllers
echo "<h2>Step 1: Loading Controller Files</h2>";

$controller_file = '../controllers/order_controller.php';

if (file_exists($controller_file)) {
    echo "<p class='success'>✅ order_controller.php found</p>";
    require_once $controller_file;
} else {
    echo "<p class='error'>❌ order_controller.php NOT FOUND at: {$controller_file}</p>";
    exit;
}

// Test cart controller for field names
if (file_exists('../controllers/cart_controller.php')) {
    echo "<p class='success'>✅ cart_controller.php found</p>";
    require_once '../controllers/cart_controller.php';
}

// Check if core functions exist
echo "<h2>Step 2: Checking Required Functions</h2>";

$required_functions = [
    'generate_invoice_number_ctr',
    'create_order_ctr',
    'add_order_ctr',
    'add_order_details_ctr',
    'add_order_detail_ctr',
    'record_payment_ctr',
    'empty_cart_ctr',
    'clear_cart_ctr',
    'get_user_cart_ctr'
];

$found_functions = [];

foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "<p class='success'>✅ {$func}() exists</p>";
        $found_functions[] = $func;
        
        // Get function parameters
        $reflection = new ReflectionFunction($func);
        $params = $reflection->getParameters();
        
        echo "<div class='code' style='margin-left: 30px; margin-bottom: 10px;'>";
        echo "Parameters: ";
        
        if (empty($params)) {
            echo "None";
        } else {
            $param_names = array_map(function($p) {
                return '$' . $p->getName();
            }, $params);
            echo implode(', ', $param_names);
            echo " <span style='color: #666;'>(" . count($params) . " params)</span>";
        }
        
        echo "</div>";
    } else {
        echo "<p class='error'>❌ {$func}() NOT FOUND</p>";
    }
}

// Test cart structure
echo "<h2>Step 3: Testing Cart Data Structure</h2>";

if (function_exists('get_user_cart_ctr')) {
    echo "<p>Checking what fields get_user_cart_ctr() returns...</p>";
    
    // We can't actually call it without a customer ID, but we can check the class
    if (file_exists('../classes/cart_class.php')) {
        require_once '../classes/cart_class.php';
        
        echo "<p class='success'>✅ cart_class.php loaded</p>";
        echo "<p class='warning'>⚠️ Need actual cart data to see field names</p>";
        echo "<p>Common field names to check in your code:</p>";
        echo "<ul>";
        echo "<li><strong>p_id</strong> or <strong>product_id</strong>?</li>";
        echo "<li><strong>qty</strong> or <strong>quantity</strong>?</li>";
        echo "<li><strong>product_price</strong> or <strong>price</strong>?</li>";
        echo "</ul>";
    }
}

// Summary
echo "<h2>Step 4: Summary & Recommendations</h2>";

echo "<div style='background: white; padding: 20px; border: 2px solid #2E86AB;'>";

echo "<h3>Functions Found:</h3>";
echo "<ul>";
foreach ($found_functions as $func) {
    echo "<li class='success'>{$func}()</li>";
}
echo "</ul>";

echo "<h3>⚠️ CRITICAL: Check Your paystack_verify_payment.php</h3>";

// Check for create_order function
if (in_array('create_order_ctr', $found_functions)) {
    echo "<p class='success'>✅ Use: create_order_ctr()</p>";
} elseif (in_array('add_order_ctr', $found_functions)) {
    echo "<p class='warning'>⚠️ Use: add_order_ctr() instead of create_order_ctr()</p>";
} else {
    echo "<p class='error'>❌ No order creation function found!</p>";
}

// Check for order details function
if (in_array('add_order_details_ctr', $found_functions)) {
    echo "<p class='success'>✅ Use: add_order_details_ctr() (plural)</p>";
} elseif (in_array('add_order_detail_ctr', $found_functions)) {
    echo "<p class='warning'>⚠️ Use: add_order_detail_ctr() (singular) instead</p>";
} else {
    echo "<p class='error'>❌ No order details function found!</p>";
}

// Check for cart clearing function
if (in_array('empty_cart_ctr', $found_functions)) {
    echo "<p class='success'>✅ Use: empty_cart_ctr()</p>";
} elseif (in_array('clear_cart_ctr', $found_functions)) {
    echo "<p class='warning'>⚠️ Use: clear_cart_ctr() instead of empty_cart_ctr()</p>";
} else {
    echo "<p class='error'>❌ No cart clearing function found!</p>";
}

// Check for payment recording
if (in_array('record_payment_ctr', $found_functions)) {
    echo "<p class='success'>✅ Use: record_payment_ctr()</p>";
} else {
    echo "<p class='warning'>⚠️ record_payment_ctr() not found - payment won't be recorded</p>";
}

// Check for invoice generation
if (in_array('generate_invoice_number_ctr', $found_functions)) {
    echo "<p class='success'>✅ Use: generate_invoice_number_ctr()</p>";
} else {
    echo "<p class='warning'>⚠️ generate_invoice_number_ctr() not found - need to generate invoice manually</p>";
}

echo "<hr>";
echo "<h3>📋 Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Share this diagnostic result</strong> (screenshot)</li>";
echo "<li><strong>Share your controllers/order_controller.php</strong> file</li>";
echo "<li><strong>Share your controllers/cart_controller.php</strong> file</li>";
echo "<li>I'll create a CORRECTED paystack_verify_payment.php that uses the RIGHT function names</li>";
echo "</ol>";

echo "</div>";

echo "<hr>";
echo "<p><strong>Diagnostic Complete!</strong> Time: " . date('Y-m-d H:i:s') . "</p>";
?>