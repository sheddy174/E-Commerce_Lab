<?php
require_once '../classes/product_class.php';

/**
 * Product Controller - Business logic layer
 * Coordinates between Views and Models for product management
 */

/**
 * Add a new product
 * @param int $product_cat Category ID
 * @param int $product_brand Brand ID
 * @param string $product_title Product title
 * @param float $product_price Product price
 * @param string $product_desc Product description
 * @param string $product_image Image path
 * @param string $product_keywords Keywords
 * @return int|false Product ID on success, false on failure
 */
function add_product_ctr($product_cat, $product_brand, $product_title, $product_price, 
                        $product_desc, $product_image, $product_keywords)
{
    try {
        // Validate inputs
        if (empty($product_cat) || !is_numeric($product_cat) ||
            empty($product_brand) || !is_numeric($product_brand) ||
            empty(trim($product_title)) ||
            empty($product_price) || !is_numeric($product_price)) {
            return false;
        }
        
        // Sanitize inputs
        $product_title = trim($product_title);
        $product_desc = trim($product_desc);
        $product_keywords = trim($product_keywords);
        $product_cat = (int) $product_cat;
        $product_brand = (int) $product_brand;
        $product_price = (float) $product_price;
        
        $product = new Product();
        $result = $product->addProduct($product_cat, $product_brand, $product_title, 
                                      $product_price, $product_desc, $product_image, $product_keywords);
        
        error_log("Add product attempt: " . $product_title . " - Result: " . ($result ? 'Success (ID: ' . $result . ')' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Add product exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all products
 * @return array|false Array of products or false on failure
 */
function get_all_products_ctr()
{
    try {
        $product = new Product();
        return $product->getAllProducts();
    } catch (Exception $e) {
        error_log("Get all products exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get product by ID
 * @param int $product_id Product ID
 * @return array|false Product data or false if not found
 */
function get_product_by_id_ctr($product_id)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            return false;
        }
        
        $product = new Product();
        return $product->getProductById($product_id);
    } catch (Exception $e) {
        error_log("Get product by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get products by category
 * @param int $cat_id Category ID
 * @return array|false Array of products or false on failure
 */
function get_products_by_category_ctr($cat_id)
{
    try {
        if (!is_numeric($cat_id) || $cat_id <= 0) {
            return false;
        }
        
        $product = new Product();
        return $product->getProductsByCategory($cat_id);
    } catch (Exception $e) {
        error_log("Get products by category exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get products by brand
 * @param int $brand_id Brand ID
 * @return array|false Array of products or false on failure
 */
function get_products_by_brand_ctr($brand_id)
{
    try {
        if (!is_numeric($brand_id) || $brand_id <= 0) {
            return false;
        }
        
        $product = new Product();
        return $product->getProductsByBrand($brand_id);
    } catch (Exception $e) {
        error_log("Get products by brand exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update product
 * @param int $product_id Product ID
 * @param int $product_cat Category ID
 * @param int $product_brand Brand ID
 * @param string $product_title Product title
 * @param float $product_price Product price
 * @param string $product_desc Product description
 * @param string $product_image Image path (null to keep existing)
 * @param string $product_keywords Keywords
 * @return bool Success status
 */
function update_product_ctr($product_id, $product_cat, $product_brand, $product_title, 
                           $product_price, $product_desc, $product_image, $product_keywords)
{
    try {
        // Validate inputs
        if (!is_numeric($product_id) || $product_id <= 0 ||
            !is_numeric($product_cat) || $product_cat <= 0 ||
            !is_numeric($product_brand) || $product_brand <= 0 ||
            empty(trim($product_title)) ||
            !is_numeric($product_price) || $product_price < 0) {
            return false;
        }
        
        // Sanitize inputs
        $product_title = trim($product_title);
        $product_desc = trim($product_desc);
        $product_keywords = trim($product_keywords);
        
        $product = new Product();
        $result = $product->updateProduct($product_id, $product_cat, $product_brand, $product_title, 
                                         $product_price, $product_desc, $product_image, $product_keywords);
        
        error_log("Update product attempt - ID: " . $product_id . ", Title: " . $product_title . " - Result: " . ($result ? 'Success' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Update product exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete product
 * @param int $product_id Product ID to delete
 * @return bool Success status
 */
function delete_product_ctr($product_id)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            return false;
        }
        
        $product = new Product();
        $result = $product->deleteProduct($product_id);
        
        error_log("Delete product attempt - ID: " . $product_id . " - Result: " . ($result ? 'Success' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Delete product exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Search products
 * @param string $search_term Search term
 * @return array|false Array of matching products
 */
function search_products_ctr($search_term)
{
    try {
        if (empty(trim($search_term))) {
            return get_all_products_ctr();
        }
        
        $product = new Product();
        return $product->searchProducts(trim($search_term));
    } catch (Exception $e) {
        error_log("Search products exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate product data
 * @param array $data Product data to validate
 * @return array Validation result with status and message
 */
function validate_product_ctr($data)
{
    $result = ['valid' => false, 'message' => ''];
    
    // Check required fields
    if (empty($data['product_title']) || empty(trim($data['product_title']))) {
        $result['message'] = 'Product title is required';
        return $result;
    }
    
    if (empty($data['product_cat']) || !is_numeric($data['product_cat']) || $data['product_cat'] <= 0) {
        $result['message'] = 'Valid category is required';
        return $result;
    }
    
    if (empty($data['product_brand']) || !is_numeric($data['product_brand']) || $data['product_brand'] <= 0) {
        $result['message'] = 'Valid brand is required';
        return $result;
    }
    
    if (empty($data['product_price']) || !is_numeric($data['product_price']) || $data['product_price'] < 0) {
        $result['message'] = 'Valid price is required (must be 0 or greater)';
        return $result;
    }
    
    // Check title length
    $title = trim($data['product_title']);
    if (strlen($title) < 3) {
        $result['message'] = 'Product title must be at least 3 characters long';
        return $result;
    }
    
    if (strlen($title) > 200) {
        $result['message'] = 'Product title must be less than 200 characters';
        return $result;
    }
    
    // Check description length
    if (!empty($data['product_desc'])) {
        if (strlen($data['product_desc']) > 500) {
            $result['message'] = 'Product description must be less than 500 characters';
            return $result;
        }
    }
    
    $result['valid'] = true;
    $result['message'] = 'Product data is valid';
    return $result;
}

/**
 * Get product count
 * @return int|false Product count or false on failure
 */
function get_product_count_ctr()
{
    try {
        $product = new Product();
        return $product->getProductCount();
    } catch (Exception $e) {
        error_log("Get product count exception: " . $e->getMessage());
        return false;
    }
}
?>