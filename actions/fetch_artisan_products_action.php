<?php
/**
 * Fetch Artisan's Own Products
 * Returns only products belonging to the logged-in artisan
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

$response = array();

try {
    // Check if user is logged in and is artisan
    if (!is_logged_in()) {
        $response['status'] = 'error';
        $response['message'] = 'Please login to continue';
        echo json_encode($response);
        exit();
    }

    if (!is_artisan()) {
        $response['status'] = 'error';
        $response['message'] = 'Artisan access required';
        echo json_encode($response);
        exit();
    }

    // Get artisan profile
    $customer_id = get_user_id();
    $artisan_profile = get_artisan_profile_ctr($customer_id);

    if (!$artisan_profile) {
        $response['status'] = 'error';
        $response['message'] = 'Artisan profile not found';
        $response['data'] = [];
        echo json_encode($response);
        exit();
    }

    $artisan_id = $artisan_profile['artisan_id'];

    // Fetch artisan's products
    $products = get_artisan_products_ctr($artisan_id);

    if ($products !== false) {
        $response['status'] = 'success';
        $response['data'] = $products;
        $response['message'] = 'Products retrieved successfully';
        
        error_log("Artisan products fetched - Artisan ID: {$artisan_id}, Count: " . count($products));
    } else {
        $response['status'] = 'success'; // Still success, just no products
        $response['data'] = [];
        $response['message'] = 'No products found';
        
        error_log("No products found for artisan ID: {$artisan_id}");
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    error_log("Artisan product fetch error: " . $e->getMessage());
}
echo json_encode($response);
?>