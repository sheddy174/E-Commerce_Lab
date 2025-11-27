<?php
/**
 * Fetch Artisan's Products Action
 * Returns only products belonging to the logged-in artisan
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/product_controller.php';
require_once '../controllers/artisan_controller.php';

$response = array();

try {
    // Check if user is artisan
    if (!is_artisan()) {
        $response['status'] = 'error';
        $response['message'] = 'Artisan access required';
        echo json_encode($response);
        exit();
    }

    // Get artisan info
    $customer_id = get_user_id();
    $artisan = get_artisan_by_customer_id_ctr($customer_id);

    if (!$artisan) {
        $response['status'] = 'error';
        $response['message'] = 'Artisan profile not found';
        echo json_encode($response);
        exit();
    }

    $artisan_id = $artisan['artisan_id'];

    // Fetch artisan's products
    require_once '../classes/product_class.php';
    $product = new Product();
    $products = $product->getProductsByArtisan($artisan_id);

    if ($products !== false) {
        $response['status'] = 'success';
        $response['data'] = $products;
        $response['message'] = 'Products retrieved successfully';
        
        error_log("Artisan products fetched - Artisan ID: {$artisan_id}, Count: " . count($products) . ", User: " . get_user_email());
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch products';
        $response['data'] = [];
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    error_log("Artisan products fetch error: " . $e->getMessage());
}

echo json_encode($response);
?>