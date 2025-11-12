<?php
require_once '../classes/brand_class.php';

/**
 * Brand Controller - Business logic layer
 * Coordinates between Views and Models for brand management
 */

/**
 * Add a new brand
 * @param string $brand_name Brand name
 * @param int $brand_cat Category ID
 * @return int|false Brand ID on success, false on failure
 */
function add_brand_ctr($brand_name, $brand_cat)
{
    try {
        // Validate inputs
        if (empty(trim($brand_name)) || empty($brand_cat) || !is_numeric($brand_cat)) {
            return false;
        }

        // Sanitize input
        $brand_name = trim($brand_name);
        $brand_cat = (int) $brand_cat;

        $brand = new Brand();
        $result = $brand->addBrand($brand_name, $brand_cat);

        error_log("Add brand attempt: " . $brand_name . " (Category: " . $brand_cat . ") - Result: " . ($result ? 'Success (ID: ' . $result . ')' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Add brand exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all brands with category information
 * @return array|false Array of brands or false on failure
 */
function get_all_brands_ctr()
{
    try {
        $brand = new Brand();
        return $brand->getAllBrands();
    } catch (Exception $e) {
        error_log("Get all brands exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get brands with product counts
 * @return array|false Array of brands with product counts
 */
function get_brands_with_counts_ctr()
{
    try {
        $brand = new Brand();
        return $brand->getBrandsWithProductCount();
    } catch (Exception $e) {
        error_log("Get brands with counts exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get brand by ID
 * @param int $brand_id Brand ID
 * @return array|false Brand data or false if not found
 */
function get_brand_by_id_ctr($brand_id)
{
    try {
        if (!is_numeric($brand_id) || $brand_id <= 0) {
            return false;
        }

        $brand = new Brand();
        return $brand->getBrandById($brand_id);
    } catch (Exception $e) {
        error_log("Get brand by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get brands by category
 * @param int $cat_id Category ID
 * @return array|false Array of brands or false on failure
 */
function get_brands_by_category_ctr($cat_id)
{
    try {
        if (!is_numeric($cat_id) || $cat_id <= 0) {
            return false;
        }

        $brand = new Brand();
        return $brand->getBrandsByCategory($cat_id);
    } catch (Exception $e) {
        error_log("Get brands by category exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update brand
 * @param int $brand_id Brand ID
 * @param string $brand_name New brand name
 * @param int $brand_cat New category ID
 * @return bool Success status
 */
function update_brand_ctr($brand_id, $brand_name, $brand_cat)
{
    try {
        // Validate inputs
        if (
            !is_numeric($brand_id) || $brand_id <= 0 ||
            empty(trim($brand_name)) ||
            !is_numeric($brand_cat) || $brand_cat <= 0
        ) {
            return false;
        }

        // Sanitize inputs
        $brand_name = trim($brand_name);
        $brand_cat = (int) $brand_cat;

        $brand = new Brand();
        $result = $brand->updateBrand($brand_id, $brand_name, $brand_cat);

        error_log("Update brand attempt - ID: " . $brand_id . ", Name: " . $brand_name . ", Category: " . $brand_cat . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Update brand exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete brand
 * @param int $brand_id Brand ID to delete
 * @return bool Success status
 */
function delete_brand_ctr($brand_id)
{
    try {
        if (!is_numeric($brand_id) || $brand_id <= 0) {
            return false;
        }

        $brand = new Brand();
        $result = $brand->deleteBrand($brand_id);

        error_log("Delete brand attempt - ID: " . $brand_id . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Delete brand exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if brand name + category combination exists
 * @param string $brand_name Brand name to check
 * @param int $brand_cat Category ID
 * @return bool True if exists, false otherwise
 */
function brand_category_exists_ctr($brand_name, $brand_cat)
{
    try {
        if (empty(trim($brand_name)) || !is_numeric($brand_cat)) {
            return false;
        }

        $brand = new Brand();
        $exists = $brand->brandCategoryExists(trim($brand_name), $brand_cat);

        error_log("Brand+Category exists check for: " . $brand_name . " (Cat: " . $brand_cat . ") - Result: " . ($exists ? 'EXISTS' : 'AVAILABLE'));

        return $exists;
    } catch (Exception $e) {
        error_log("Brand+Category exists check exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Search brands by name
 * @param string $search_term Search term
 * @return array|false Array of matching brands
 */
function search_brands_ctr($search_term)
{
    try {
        if (empty(trim($search_term))) {
            return get_all_brands_ctr();
        }

        $brand = new Brand();
        return $brand->searchBrands(trim($search_term));
    } catch (Exception $e) {
        error_log("Search brands exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate brand name and category
 * @param string $brand_name Brand name to validate
 * @param int $brand_cat Category ID
 * @return array Validation result with status and message
 */
function validate_brand_ctr($brand_name, $brand_cat)
{
    $result = ['valid' => false, 'message' => ''];

    // Check if empty
    if (empty(trim($brand_name))) {
        $result['message'] = 'Brand name is required';
        return $result;
    }

    if (empty($brand_cat) || !is_numeric($brand_cat) || $brand_cat <= 0) {
        $result['message'] = 'Valid category is required';
        return $result;
    }

    // Check length
    $brand_name = trim($brand_name);
    if (strlen($brand_name) < 2) {
        $result['message'] = 'Brand name must be at least 2 characters long';
        return $result;
    }

    if (strlen($brand_name) > 100) {
        $result['message'] = 'Brand name must be less than 100 characters';
        return $result;
    }

    // Check for valid characters (letters, numbers, spaces, hyphens, underscores)
    if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $brand_name)) {
        $result['message'] = 'Brand name can only contain letters, numbers, spaces, hyphens, and underscores';
        return $result;
    }

    // Check if brand name + category combination already exists
    if (brand_category_exists_ctr($brand_name, $brand_cat)) {
        $result['message'] = 'This brand already exists in the selected category';
        return $result;
    }

    $result['valid'] = true;
    $result['message'] = 'Brand is valid';
    return $result;
}

/**
 * Get count of brands added today
 * @return int Count of brands added today
 */
function get_brands_added_today_ctr()
{
    try {
        $brand = new Brand();
        return $brand->getBrandsAddedTodayCount();
    } catch (Exception $e) {
        error_log("Get brands added today exception: " . $e->getMessage());
        return 0;
    }
}
?>