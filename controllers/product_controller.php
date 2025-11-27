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
 * @param int|null $artisan_id Artisan ID (optional, defaults to null for admin)
 * @return int|false Product ID on success, false on failure
 */
function add_product_ctr(
    $product_cat,
    $product_brand,
    $product_title,
    $product_price,
    $product_desc,
    $product_image,
    $product_keywords,
    $artisan_id = null  // ADDED: Default to null for admin products
) {
    try {
        // Validate inputs
        if (
            empty($product_cat) || !is_numeric($product_cat) ||
            empty($product_brand) || !is_numeric($product_brand) ||
            empty(trim($product_title)) ||
            empty($product_price) || !is_numeric($product_price)
        ) {
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
        $result = $product->addProduct(
            $product_cat,
            $product_brand,
            $product_title,
            $product_price,
            $product_desc,
            $product_image,
            $product_keywords,
            $artisan_id  // ADDED: Pass artisan_id (will be null for admin)
        );

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
 * @param int|null $artisan_id Artisan ID (optional, defaults to null)
 * @return bool Success status
 */
function update_product_ctr(
    $product_id,
    $product_cat,
    $product_brand,
    $product_title,
    $product_price,
    $product_desc,
    $product_image,
    $product_keywords,
    $artisan_id = null  // ADDED: Default to null
) {
    try {
        // Validate inputs
        if (
            !is_numeric($product_id) || $product_id <= 0 ||
            !is_numeric($product_cat) || $product_cat <= 0 ||
            !is_numeric($product_brand) || $product_brand <= 0 ||
            empty(trim($product_title)) ||
            !is_numeric($product_price) || $product_price < 0
        ) {
            return false;
        }

        // Sanitize inputs
        $product_title = trim($product_title);
        $product_desc = trim($product_desc);
        $product_keywords = trim($product_keywords);

        $product = new Product();
        $result = $product->updateProduct(
            $product_id,
            $product_cat,
            $product_brand,
            $product_title,
            $product_price,
            $product_desc,
            $product_image,
            $product_keywords,
            $artisan_id  // ADDED: Pass artisan_id
        );

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

/*
 * Customer-Facing Product Display & Search
 * LAB 7 Additions
 */

/**
 * View all products (customer-facing)
 * Enhanced version for customer product display
 * @return array|false Array of all products
 */
function view_all_products_ctr()
{
    try {
        $product = new Product();
        return $product->viewAllProducts();
    } catch (Exception $e) {
        error_log("View all products exception: " . $e->getMessage());
        return false;
    }
}

/**
 * View single product with full details
 * For detailed product view page
 * @param int $product_id Product ID
 * @return array|false Product details or false if not found
 */
function view_single_product_ctr($product_id)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            return false;
        }

        $product = new Product();
        return $product->viewSingleProduct($product_id);
    } catch (Exception $e) {
        error_log("View single product exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Search products by query (Advanced)
 * Searches across title, description, keywords, category, and brand
 * @param string $search_query Search term
 * @return array|false Array of matching products
 */
function search_products_advanced_ctr($search_query)
{
    try {
        if (empty(trim($search_query))) {
            return view_all_products_ctr();
        }

        $product = new Product();
        return $product->searchProductsAdvanced(trim($search_query));
    } catch (Exception $e) {
        error_log("Search products advanced exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Filter products by category
 * For category-based filtering on customer pages
 * @param int $cat_id Category ID
 * @return array|false Array of products in category
 */
function filter_products_by_category_ctr($cat_id)
{
    try {
        if (!is_numeric($cat_id) || $cat_id <= 0) {
            return false;
        }

        $product = new Product();
        return $product->filterProductsByCategory($cat_id);
    } catch (Exception $e) {
        error_log("Filter by category exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Filter products by brand
 * For brand-based filtering on customer pages
 * @param int $brand_id Brand ID
 * @return array|false Array of products by brand
 */
function filter_products_by_brand_ctr($brand_id)
{
    try {
        if (!is_numeric($brand_id) || $brand_id <= 0) {
            return false;
        }

        $product = new Product();
        return $product->filterProductsByBrand($brand_id);
    } catch (Exception $e) {
        error_log("Filter by brand exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Filter products by price range
 * For price-based filtering
 * @param float $min_price Minimum price
 * @param float $max_price Maximum price
 * @return array|false Array of products in range
 */
function filter_products_by_price_ctr($min_price, $max_price)
{
    try {
        if (!is_numeric($min_price) || !is_numeric($max_price) || $min_price < 0 || $max_price < $min_price) {
            return false;
        }

        $product = new Product();
        return $product->filterProductsByPriceRange($min_price, $max_price);
    } catch (Exception $e) {
        error_log("Filter by price exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get featured/latest products
 * For homepage or featured sections
 * @param int $limit Number of products to return
 * @return array|false Array of latest products
 */
function get_featured_products_ctr($limit = 8)
{
    try {
        $product = new Product();
        return $product->getFeaturedProducts($limit);
    } catch (Exception $e) {
        error_log("Get featured products exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get count of products added today
 * @return int Count of products added today
 */
function get_products_added_today_ctr()
{
    try {
        $product = new Product();
        return $product->getProductsAddedTodayCount();
    } catch (Exception $e) {
        error_log("Get products added today exception: " . $e->getMessage());
        return 0;
    }
}

/**
 * Add product with artisan and status
 * @param int $cat_id Category ID
 * @param int $brand_id Brand ID
 * @param string $title Product title
 * @param float $price Product price
 * @param string $desc Product description
 * @param string|null $image Product image path
 * @param string $keywords Product keywords
 * @param int|null $artisan_id Artisan ID (NULL for admin products)
 * @param string $status Product status (active/pending)
 * @return int|false Product ID if successful, false otherwise
 */
function add_product_with_artisan_ctr($cat_id, $brand_id, $title, $price, $desc, $image, $keywords, $artisan_id = null, $status = 'active')
{
    try {
        require_once '../classes/product_class.php';
        $product = new Product();
        return $product->addProductWithArtisan($cat_id, $brand_id, $title, $price, $desc, $image, $keywords, $artisan_id, $status);
    } catch (Exception $e) {
        error_log("Add product with artisan exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update product with status
 * @param int $product_id Product ID
 * @param int $cat_id Category ID
 * @param int $brand_id Brand ID
 * @param string $title Product title
 * @param float $price Product price
 * @param string $desc Product description
 * @param string $image Product image path
 * @param string $keywords Product keywords
 * @param string $status Product status
 * @return bool Success status
 */
function update_product_with_status_ctr($product_id, $cat_id, $brand_id, $title, $price, $desc, $image, $keywords, $status)
{
    try {
        require_once '../classes/product_class.php';
        $product = new Product();
        return $product->updateProductWithStatus($product_id, $cat_id, $brand_id, $title, $price, $desc, $image, $keywords, $status);
    } catch (Exception $e) {
        error_log("Update product with status exception: " . $e->getMessage());
        return false;
    }
}
?>