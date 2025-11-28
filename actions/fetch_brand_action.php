<?php
/**
 * Fetch all brands (accessible by admin AND artisans)
 * Admins can fetch for management, artisans can fetch for product creation
 * FIXED: Now uses get_brands_with_counts_ctr() to include product_count
 */

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/brand_controller.php';

$response = array();

try {
    // Check if user is logged in (admin OR artisan)
    if (!is_logged_in()) {
        $response['status'] = 'error';
        $response['message'] = 'Please login to continue';
        echo json_encode($response);
        exit();
    }

    // Both admins and artisans can READ brands
    if (!is_admin() && !is_artisan()) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access';
        error_log("Unauthorized brand fetch - User: " . get_user_email());
        echo json_encode($response);
        exit();
    }

    // FIXED: Fetch brands with category info AND product counts
    $brands = get_brands_with_counts_ctr();

    if ($brands !== false) {
        // If artisan, return basic data (no "added_today" stats)
        if (is_artisan()) {
            $response['status'] = 'success';
            $response['data'] = $brands;
            $response['message'] = 'Brands retrieved successfully';
        } else {
            // Admin gets additional stats
            $added_today = get_brands_added_today_ctr();
            
            $response['status'] = 'success';
            $response['data'] = $brands;
            $response['added_today'] = $added_today;
            $response['message'] = 'Brands retrieved successfully';
        }
        
        error_log("Brands fetched - Count: " . count($brands) . ", User: " . get_user_email() . " (Role: " . (is_admin() ? 'Admin' : 'Artisan') . ")");
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to fetch brands';
        $response['data'] = [];
        
        error_log("Failed to fetch brands - User: " . get_user_email());
    }

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    $response['data'] = [];
    error_log("Brand fetch error: " . $e->getMessage());
}

echo json_encode($response);
?>