<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/artisan_class.php';
require_once '../classes/product_class.php';

/**
 * Artisan Controller - Manages artisan-related operations
 * Complete CRUD functionality for artisan profiles and related data
 */

// ==========================================
// ARTISAN PROFILE CRUD OPERATIONS
// ==========================================

/**
 * Create artisan profile
 * @param array $artisan_data Profile data
 * @return int|false Artisan ID on success, false on failure
 */
function create_artisan_profile_ctr($artisan_data)
{
    try {
        $artisan = new Artisan();
        return $artisan->createArtisanProfile($artisan_data);
    } catch (Exception $e) {
        error_log("Create artisan profile exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get artisan profile by customer ID
 * @param int $customer_id Customer ID
 * @return array|false Profile data on success, false on failure
 */
function get_artisan_profile_ctr($customer_id)
{
    try {
        $artisan = new Artisan();
        return $artisan->getArtisanProfile($customer_id);
    } catch (Exception $e) {
        error_log("Get artisan profile exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get artisan profile by artisan ID
 * @param int $artisan_id Artisan ID
 * @return array|false Profile data on success, false on failure
 */
function get_artisan_by_id_ctr($artisan_id)
{
    try {
        $artisan = new Artisan();
        return $artisan->getArtisanById($artisan_id);
    } catch (Exception $e) {
        error_log("Get artisan by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update artisan profile
 * @param int $artisan_id Artisan ID
 * @param array $data Updated profile data
 * @return bool Success status
 */
function update_artisan_profile_ctr($artisan_id, $data)
{
    try {
        $artisan = new Artisan();
        return $artisan->updateArtisanProfile($artisan_id, $data);
    } catch (Exception $e) {
        error_log("Update artisan profile exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete artisan profile
 * @param int $artisan_id Artisan ID
 * @return bool Success status
 */
function delete_artisan_profile_ctr($artisan_id)
{
    try {
        $artisan = new Artisan();
        return $artisan->deleteArtisanProfile($artisan_id);
    } catch (Exception $e) {
        error_log("Delete artisan profile exception: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// ARTISAN LISTING & FILTERING
// ==========================================

/**
 * Get all artisans (all statuses)
 * @return array|false Array of artisans on success, false on failure
 */
function get_all_artisans_ctr()
{
    try {
        $artisan = new Artisan();
        return $artisan->getAllArtisans();
    } catch (Exception $e) {
        error_log("Get all artisans exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all verified artisans
 * @return array|false Array of verified artisans on success, false on failure
 */
function get_verified_artisans_ctr()
{
    try {
        $artisan = new Artisan();
        return $artisan->getVerifiedArtisans();
    } catch (Exception $e) {
        error_log("Get verified artisans exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get pending artisans (awaiting verification)
 * @return array|false Array of pending artisans on success, false on failure
 */
function get_pending_artisans_ctr()
{
    try {
        $artisan = new Artisan();
        return $artisan->getPendingArtisans();
    } catch (Exception $e) {
        error_log("Get pending artisans exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get artisans by craft specialty
 * @param string $specialty Craft specialty
 * @return array|false Array of artisans on success, false on failure
 */
function get_artisans_by_specialty_ctr($specialty)
{
    try {
        $artisan = new Artisan();
        return $artisan->getArtisansBySpecialty($specialty);
    } catch (Exception $e) {
        error_log("Get artisans by specialty exception: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// VERIFICATION MANAGEMENT
// ==========================================

/**
 * Update verification status
 * @param int $artisan_id Artisan ID
 * @param string $status Verification status (pending, verified, rejected)
 * @return bool Success status
 */
function update_verification_status_ctr($artisan_id, $status)
{
    try {
        $artisan = new Artisan();
        return $artisan->updateVerificationStatus($artisan_id, $status);
    } catch (Exception $e) {
        error_log("Update verification status exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if artisan is verified
 * @param int $customer_id Customer ID
 * @return bool True if verified, false otherwise
 */
function is_artisan_verified_ctr($customer_id)
{
    try {
        $artisan = new Artisan();
        $profile = $artisan->getArtisanProfile($customer_id);
        return $profile && $profile['verification_status'] === 'verified';
    } catch (Exception $e) {
        error_log("Check artisan verification exception: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// STATISTICS & ANALYTICS
// ==========================================

/**
 * Get artisan statistics (products, sales, revenue)
 * @param int $artisan_id Artisan ID
 * @return array|false Statistics array on success, false on failure
 */
function get_artisan_stats_ctr($artisan_id)
{
    try {
        $artisan = new Artisan();
        return $artisan->getArtisanStats($artisan_id);
    } catch (Exception $e) {
        error_log("Get artisan stats exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update artisan rating
 * @param int $artisan_id Artisan ID
 * @param float $rating New rating (0.00 - 5.00)
 * @return bool Success status
 */
function update_artisan_rating_ctr($artisan_id, $rating)
{
    try {
        $artisan = new Artisan();
        return $artisan->updateArtisanRating($artisan_id, $rating);
    } catch (Exception $e) {
        error_log("Update artisan rating exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update artisan commission rate
 * @param int $artisan_id Artisan ID
 * @param float $rate Commission rate (0.00 - 100.00)
 * @return bool Success status
 */
function update_commission_rate_ctr($artisan_id, $rate)
{
    try {
        $artisan = new Artisan();
        return $artisan->updateCommissionRate($artisan_id, $rate);
    } catch (Exception $e) {
        error_log("Update commission rate exception: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// SEARCH & FILTERING
// ==========================================

/**
 * Search artisans by name or shop name
 * @param string $search_term Search term
 * @return array|false Array of matching artisans on success, false on failure
 */
function search_artisans_ctr($search_term)
{
    try {
        $artisan = new Artisan();
        return $artisan->searchArtisans($search_term);
    } catch (Exception $e) {
        error_log("Search artisans exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get top rated artisans
 * @param int $limit Number of artisans to return
 * @return array|false Array of top artisans on success, false on failure
 */
function get_top_artisans_ctr($limit = 10)
{
    try {
        $artisan = new Artisan();
        return $artisan->getTopRatedArtisans($limit);
    } catch (Exception $e) {
        error_log("Get top artisans exception: " . $e->getMessage());
        return false;
    }
}

// ==========================================
// PROFILE IMAGE MANAGEMENT
// ==========================================

/**
 * Update artisan profile image
 * @param int $artisan_id Artisan ID
 * @param string $image_path Path to profile image
 * @return bool Success status
 */
function update_artisan_image_ctr($artisan_id, $image_path)
{
    try {
        $artisan = new Artisan();
        return $artisan->updateProfileImage($artisan_id, $image_path);
    } catch (Exception $e) {
        error_log("Update artisan image exception: " . $e->getMessage());
        return false;
    }
}


 //Add to artisan_controller.php

/**
 * Get all products by artisan
 * @param int $artisan_id Artisan ID
 * @return array|false Array of products on success, false on failure
 */
function get_artisan_products_ctr($artisan_id)
{
    try {
        $product = new Product();
        return $product->getProductsByArtisan($artisan_id);
    } catch (Exception $e) {
        error_log("Get artisan products exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Count total products by artisan
 * @param int $artisan_id Artisan ID
 * @return int Product count
 */
function count_artisan_products_ctr($artisan_id)
{
    try {
        $product = new Product();
        return $product->countArtisanProducts($artisan_id);
    } catch (Exception $e) {
        error_log("Count artisan products exception: " . $e->getMessage());
        return 0;
    }
}


/**
 * Upload verification document
 * @param int $artisan_id Artisan ID
 * @param string $doc_type Document type (id_card, business_permit, etc.)
 * @param string $doc_path Document path
 * @return bool Success status
 */
function add_verification_document_ctr($artisan_id, $doc_type, $doc_path)
{
    try {
        $artisan = new Artisan();
        return $artisan->addVerificationDocument($artisan_id, $doc_type, $doc_path);
    } catch (Exception $e) {
        error_log("Add verification document exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get artisan verification documents
 * @param int $artisan_id Artisan ID
 * @return array|false Array of documents on success, false on failure
 */
function get_verification_documents_ctr($artisan_id)
{
    try {
        $artisan = new Artisan();
        return $artisan->getVerificationDocuments($artisan_id);
    } catch (Exception $e) {
        error_log("Get verification documents exception: " . $e->getMessage());
        return false;
    }
}
?>