<?php
/**
 * COMPREHENSIVE DIAGNOSTIC SCRIPT
 * This will tell us exactly what's wrong
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>GhanaTunes Product Data Diagnostic</h1>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: white; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
    h2 { background: #2E86AB; color: white; padding: 10px; }
    h3 { background: #ddd; padding: 5px; }
</style>";

// Step 1: Load required files
echo "<h2>Step 1: Loading Required Files</h2>";

try {
    require_once '../controllers/product_controller.php';
    echo "<p class='success'>✅ product_controller.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Failed to load product_controller.php: " . $e->getMessage() . "</p>";
    exit;
}

// Step 2: Check if function exists
echo "<h2>Step 2: Checking Controller Function</h2>";

if (function_exists('get_all_products_with_artisan_ctr')) {
    echo "<p class='success'>✅ get_all_products_with_artisan_ctr() function EXISTS</p>";
} else {
    echo "<p class='error'>❌ get_all_products_with_artisan_ctr() function NOT FOUND!</p>";
    echo "<p>Available functions:</p>";
    echo "<pre>";
    $functions = get_defined_functions();
    $user_functions = array_filter($functions['user'], function($func) {
        return strpos($func, 'product') !== false;
    });
    print_r($user_functions);
    echo "</pre>";
    exit;
}

// Step 3: Try to fetch products
echo "<h2>Step 3: Fetching Products</h2>";

try {
    $products = get_all_products_with_artisan_ctr();
    
    if ($products === false) {
        echo "<p class='error'>❌ Function returned FALSE (database error or no results)</p>";
    } elseif (empty($products)) {
        echo "<p class='warning'>⚠️ Function returned empty array (no products in database)</p>";
    } else {
        echo "<p class='success'>✅ Successfully fetched " . count($products) . " products</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception thrown: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Step 4: Analyze first product structure
if (!empty($products)) {
    echo "<h2>Step 4: Analyzing Product Data Structure</h2>";
    
    $first = $products[0];
    
    echo "<h3>Required Fields Check:</h3>";
    
    $required_fields = [
        'product_id' => 'Product ID',
        'product_title' => 'Product Title',
        'product_price' => 'Product Price',
        'product_image' => 'Product Image',
        'cat_name' => 'Category Name',
        'brand_name' => 'Brand Name',
        'artisan_id' => 'Artisan ID (can be NULL)',
        'shop_name' => 'Shop Name (can be NULL)',
        'artisan_name' => 'Artisan Name (can be NULL)'
    ];
    
    $missing_fields = [];
    
    foreach ($required_fields as $field => $label) {
        if (array_key_exists($field, $first)) {
            $value = $first[$field] ?? 'NULL';
            echo "<p class='success'>✅ {$label}: {$value}</p>";
        } else {
            echo "<p class='error'>❌ {$label}: MISSING!</p>";
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo "<h3 class='error'>⚠️ MISSING FIELDS DETECTED!</h3>";
        echo "<p>These fields are required but missing:</p>";
        echo "<pre>" . implode(", ", $missing_fields) . "</pre>";
        echo "<p>This is causing the DataTables error!</p>";
    }
    
    echo "<h3>Complete First Product Data:</h3>";
    echo "<pre>";
    print_r($first);
    echo "</pre>";
    
    // Step 5: Test all products
    echo "<h2>Step 5: Testing All Products</h2>";
    
    $admin_count = 0;
    $artisan_count = 0;
    
    foreach ($products as $product) {
        if ($product['artisan_id']) {
            $artisan_count++;
        } else {
            $admin_count++;
        }
    }
    
    echo "<p>Admin Products (artisan_id = NULL): <strong>{$admin_count}</strong></p>";
    echo "<p>Artisan Products (artisan_id set): <strong>{$artisan_count}</strong></p>";
    
    echo "<h3>All Products Summary:</h3>";
    echo "<table border='1' cellpadding='5' style='background: white; border-collapse: collapse;'>";
    echo "<tr style='background: #2E86AB; color: white;'>";
    echo "<th>ID</th><th>Title</th><th>Artisan ID</th><th>Shop Name</th><th>Source</th>";
    echo "</tr>";
    
    foreach ($products as $product) {
        $source = $product['artisan_id'] ? "🔨 Artisan" : "🛡️ Admin";
        $shop = $product['shop_name'] ?? 'N/A';
        echo "<tr>";
        echo "<td>{$product['product_id']}</td>";
        echo "<td>{$product['product_title']}</td>";
        echo "<td>" . ($product['artisan_id'] ?? 'NULL') . "</td>";
        echo "<td>{$shop}</td>";
        echo "<td>{$source}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Step 6: Test JSON encoding
    echo "<h2>Step 6: Testing JSON Response (What AJAX Receives)</h2>";
    
    $json_response = [
        'status' => 'success',
        'data' => $products,
        'added_today' => 0,
        'message' => 'Products retrieved successfully'
    ];
    
    $json = json_encode($json_response);
    
    if ($json === false) {
        echo "<p class='error'>❌ JSON encoding failed: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p class='success'>✅ JSON encoding successful</p>";
        echo "<h3>Sample JSON (first product only):</h3>";
        echo "<pre>";
        echo json_encode([
            'status' => 'success',
            'data' => [$first],
            'message' => 'Sample response'
        ], JSON_PRETTY_PRINT);
        echo "</pre>";
    }
}

// Step 7: Final diagnosis
echo "<h2>Step 7: Final Diagnosis</h2>";

if (empty($products)) {
    echo "<div style='background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 10px 0;'>";
    echo "<h3>⚠️ NO PRODUCTS FOUND</h3>";
    echo "<p>Your database has no products yet. This is why admin/product.php shows nothing.</p>";
    echo "<p><strong>Action Required:</strong> Add products from the admin panel first.</p>";
    echo "</div>";
} elseif (!empty($missing_fields)) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 15px; margin: 10px 0;'>";
    echo "<h3>❌ DATA STRUCTURE PROBLEM</h3>";
    echo "<p>Products exist but missing fields: <strong>" . implode(", ", $missing_fields) . "</strong></p>";
    echo "<p><strong>Problem:</strong> The getAllProductsWithArtisan() method is not returning artisan data.</p>";
    echo "<p><strong>Action Required:</strong> Check Product class method in classes/product_class.php</p>";
    echo "</div>";
} else {
    echo "<div style='background: #d1e7dd; border: 2px solid #198754; padding: 15px; margin: 10px 0;'>";
    echo "<h3>✅ ALL CHECKS PASSED!</h3>";
    echo "<p>Your data structure is correct. You have:</p>";
    echo "<ul>";
    echo "<li>{$admin_count} Admin products</li>";
    echo "<li>{$artisan_count} Artisan products</li>";
    echo "<li>All required fields present</li>";
    echo "</ul>";
    echo "<p><strong>Next Step:</strong> Clear browser cache (Ctrl+Shift+R) and test admin/product.php</p>";
    echo "<p>If DataTables error persists, the problem is in product.js (JavaScript file)</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Diagnostic Complete!</strong> Time: " . date('Y-m-d H:i:s') . "</p>";
?>