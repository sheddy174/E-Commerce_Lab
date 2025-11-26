<?php
/**
 * Get Dashboard Statistics
 * Returns counts for categories, brands, products, and artisans
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../classes/category_class.php';
require_once '../classes/brand_class.php';
require_once '../classes/product_class.php';
require_once '../controllers/artisan_controller.php';

$response = array();

try {
    // Check if user is admin
    if (!is_admin()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access';
        error_log("Unauthorized dashboard stats access attempt by: " . (get_user_email() ?: 'guest'));
        echo json_encode($response);
        exit();
    }
    
    // Get category count
    $category_class = new Category();
    $categories = $category_class->getAllCategories();
    $total_categories = is_array($categories) ? count($categories) : 0;
    
    // Get brand count
    $brand_class = new Brand();
    $brands = $brand_class->getAllBrands();
    $total_brands = is_array($brands) ? count($brands) : 0;
    
    // Get product count
    $product_class = new Product();
    $products = $product_class->getAllProducts();
    $total_products = is_array($products) ? count($products) : 0;
    
    // Get artisan count
    $all_artisans = get_all_artisans_ctr();
    $total_artisans = is_array($all_artisans) ? count($all_artisans) : 0;
    
    $response['status'] = 'success';
    $response['data'] = array(
        'total_categories' => $total_categories,
        'total_brands' => $total_brands,
        'total_products' => $total_products,
        'total_artisans' => $total_artisans
    );
    
    // LOGGING: Track dashboard stats access
    error_log("Dashboard stats fetched - Categories: $total_categories, Brands: $total_brands, " .
              "Products: $total_products, Artisans: $total_artisans - User: " . get_user_email());
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error fetching statistics: ' . $e->getMessage();
    error_log("Dashboard stats error: " . $e->getMessage() . " - User: " . (get_user_email() ?: 'unknown'));
}

echo json_encode($response);
?>