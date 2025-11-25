<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../settings/db_class.php';

/**
 * Product Model Class - Handles all product-related database operations
 * Updated with Lab 7 Methods
 */
class Product extends db_connection
{
    private $product_id;
    private $product_cat;
    private $product_brand;
    private $product_title;
    private $product_price;
    private $product_desc;
    private $product_image;
    private $product_keywords;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct($product_id = null)
    {
        parent::db_connect();
        if ($product_id) {
            $this->product_id = $product_id;
            $this->loadProduct();
        }
    }

    /**
     * Load product data from database
     */
    private function loadProduct($product_id = null)
    {
        if ($product_id) {
            $this->product_id = $product_id;
        }
        if (!$this->product_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM products WHERE product_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->product_cat = $result['product_cat'];
            $this->product_brand = $result['product_brand'];
            $this->product_title = $result['product_title'];
            $this->product_price = $result['product_price'];
            $this->product_desc = $result['product_desc'];
            $this->product_image = $result['product_image'];
            $this->product_keywords = $result['product_keywords'];
            return true;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Create new product
     * @param int $product_cat Category ID
     * @param int $product_brand Brand ID
     * @param string $product_title Product title
     * @param float $product_price Product price
     * @param string $product_desc Product description
     * @param string $product_image Image path
     * @param string $product_keywords Keywords for search
     * @param int|null $artisan_id Artisan ID (optional, null for admin products)
     * @return int|false Product ID on success, false on failure
     */
    public function addProduct($product_cat, $product_brand, $product_title, $product_price, 
                               $product_desc, $product_image, $product_keywords, $artisan_id = null)
    {
        $stmt = $this->db->prepare("INSERT INTO products (product_cat, product_brand, product_title, 
                                     product_price, product_desc, product_image, product_keywords, artisan_id) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iisdsss i", $product_cat, $product_brand, $product_title, 
                         $product_price, $product_desc, $product_image, $product_keywords, $artisan_id);
        
        if ($stmt->execute()) {
            $product_id = $this->db->insert_id;
            $stmt->close();
            return $product_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    } 
    /**
     * Get product by ID with category and brand names
     * @param int $product_id Product ID
     * @return array|false Product data or false if not found
     */
    public function getProductById($product_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_id = ?
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Update product
     * @param int $product_id Product ID
     * @param int $product_cat Category ID
     * @param int $product_brand Brand ID
     * @param string $product_title Product title
     * @param float $product_price Product price
     * @param string $product_desc Product description
     * @param string $product_image Image path (optional, null to keep existing)
     * @param string $product_keywords Keywords
     * @return bool Success status
     */
    /**
     * Update product
     * @param int $product_id Product ID
     * @param int $product_cat Category ID
     * @param int $product_brand Brand ID
     * @param string $product_title Product title
     * @param float $product_price Product price
     * @param string $product_desc Product description
     * @param string $product_image Image path (optional, null to keep existing)
     * @param string $product_keywords Keywords
     * @param int|null $artisan_id Artisan ID (optional)
     * @return bool Success status
     */
    public function updateProduct($product_id, $product_cat, $product_brand, $product_title, 
                                  $product_price, $product_desc, $product_image, $product_keywords, $artisan_id = null)
    {
        // If image is null, don't update it (keep existing image)
        if ($product_image === null) {
            $stmt = $this->db->prepare("UPDATE products SET product_cat = ?, product_brand = ?, 
                                        product_title = ?, product_price = ?, product_desc = ?, 
                                        product_keywords = ?, artisan_id = ? WHERE product_id = ?");
            
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("iisdssii", $product_cat, $product_brand, $product_title, 
                             $product_price, $product_desc, $product_keywords, $artisan_id, $product_id);
        } else {
            $stmt = $this->db->prepare("UPDATE products SET product_cat = ?, product_brand = ?, 
                                        product_title = ?, product_price = ?, product_desc = ?, 
                                        product_image = ?, product_keywords = ?, artisan_id = ? WHERE product_id = ?");
            
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("iisdsssii", $product_cat, $product_brand, $product_title, 
                             $product_price, $product_desc, $product_image, $product_keywords, $artisan_id, $product_id);
        }
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Delete product
     * @param int $product_id Product ID to delete
     * @return bool Success status
     */
    public function deleteProduct($product_id)
    {
        // First, get the image path to delete the file
        $product = $this->getProductById($product_id);
        
        $stmt = $this->db->prepare("DELETE FROM products WHERE product_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $product_id);
        $success = $stmt->execute();
        $stmt->close();
        
        // Delete image file if product was deleted successfully
        if ($success && $product && !empty($product['product_image'])) {
            $image_path = '../' . $product['product_image'];
            if (file_exists($image_path) && is_file($image_path)) {
                unlink($image_path);
            }
        }
        
        return $success;
    }

    /**
     * Get all products with category and brand information
     * @return array|false Array of products or false on failure
     */
    public function getAllProducts()
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                ORDER BY p.product_id DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get products by category
     * @param int $cat_id Category ID
     * @return array|false Array of products or false on failure
     */
    public function getProductsByCategory($cat_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_cat = ? 
            ORDER BY p.product_title ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Get products by brand
     * @param int $brand_id Brand ID
     * @return array|false Array of products or false on failure
     */
    public function getProductsByBrand($brand_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_brand = ? 
            ORDER BY p.product_title ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Search products by keyword (Basic)
     * @param string $search_term Search term
     * @return array|false Array of matching products
     */
    public function searchProducts($search_term)
    {
        $search_term = '%' . $search_term . '%';
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_title LIKE ? 
               OR p.product_desc LIKE ? 
               OR p.product_keywords LIKE ?
            ORDER BY p.product_title ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Get product count
     * @return int|false Product count or false on failure
     */
    public function getProductCount()
    {
        $sql = "SELECT COUNT(*) as count FROM products";
        $result = $this->db_fetch_one($sql);
        return $result ? $result['count'] : false;
    }

    /* 
     * LAB 7 METHODS - Customer-Facing Product Display & Search
     * These methods are designed to provide product information to customers
     * through the front-end of the application.
     */

    /**
     * View all products (customer-facing)
     * @return array|false Array of all products with full details
     */
    public function viewAllProducts()
    {
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.product_cat = c.cat_id 
                LEFT JOIN brands b ON p.product_brand = b.brand_id 
                ORDER BY p.product_id DESC";
        return $this->db_fetch_all($sql);
    }

    /**
     * View single product with full details
     * @param int $product_id Product ID
     * @return array|false Product details or false if not found
     */
    public function viewSingleProduct($product_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_id = ?
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Search products by title, description, or keywords (Advanced)
     * Searches across multiple fields and ranks results
     * @param string $search_query Search term
     * @return array|false Array of matching products
     */
    public function searchProductsAdvanced($search_query)
    {
        $search_term = '%' . $search_query . '%';
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_title LIKE ? 
               OR p.product_desc LIKE ? 
               OR p.product_keywords LIKE ?
               OR c.cat_name LIKE ?
               OR b.brand_name LIKE ?
            ORDER BY 
                CASE 
                    WHEN p.product_title LIKE ? THEN 1
                    WHEN p.product_keywords LIKE ? THEN 2
                    ELSE 3
                END,
                p.product_title ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, 
                         $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Filter products by category
     * @param int $cat_id Category ID
     * @return array|false Array of products in category
     */
    public function filterProductsByCategory($cat_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_cat = ? 
            ORDER BY p.product_title ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Filter products by brand
     * @param int $brand_id Brand ID
     * @return array|false Array of products by brand
     */
    public function filterProductsByBrand($brand_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_brand = ? 
            ORDER BY p.product_title ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Filter products by price range
     * @param float $min_price Minimum price
     * @param float $max_price Maximum price
     * @return array|false Array of products in price range
     */
    public function filterProductsByPriceRange($min_price, $max_price)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.product_price BETWEEN ? AND ? 
            ORDER BY p.product_price ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("dd", $min_price, $max_price);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Get featured/latest products (limit)
     * @param int $limit Number of products to return
     * @return array|false Array of latest products
     */
    public function getFeaturedProducts($limit = 8)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            ORDER BY p.product_id DESC 
            LIMIT ?
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Get count of products added today
     * @return int Count of products added today
     */
    public function getProductsAddedTodayCount()
    {
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as today_count 
            FROM products 
            WHERE created_at BETWEEN ? AND ?
        ");
        
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("ss", $today_start, $today_end);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
    return $result ? (int)$result['today_count'] : 0;
}

/**
 * Get all products by artisan
 * @param int $artisan_id Artisan ID
 * @return array|false Array of products or false on failure
 */
public function getProductsByArtisan($artisan_id)
{
    $stmt = $this->db->prepare("
        SELECT p.*, c.cat_name, b.brand_name 
        FROM products p 
        LEFT JOIN categories c ON p.product_cat = c.cat_id 
        LEFT JOIN brands b ON p.product_brand = b.brand_id 
        WHERE p.artisan_id = ? 
        ORDER BY p.product_id DESC
    ");
    
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $artisan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    $stmt->close();
    return $products;
}

    /**
     * Count total products by artisan
     * @param int $artisan_id Artisan ID
     * @return int Product count
     */
    public function countArtisanProducts($artisan_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM products WHERE artisan_id = ?");
        
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $artisan_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get products by artisan with pagination
     * @param int $artisan_id Artisan ID
     * @param int $limit Products per page
     * @param int $offset Starting point
     * @return array|false Array of products or false on failure
     */
    public function getArtisanProductsPaginated($artisan_id, $limit = 10, $offset = 0)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cat_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.product_cat = c.cat_id 
            LEFT JOIN brands b ON p.product_brand = b.brand_id 
            WHERE p.artisan_id = ? 
            ORDER BY p.product_id DESC
            LIMIT ? OFFSET ?
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iii", $artisan_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }

    /**
     * Check if product belongs to artisan
     * @param int $product_id Product ID
     * @param int $artisan_id Artisan ID
     * @return bool True if product belongs to artisan
     */
    public function isArtisanProduct($product_id, $artisan_id)
    {
        $stmt = $this->db->prepare("SELECT artisan_id FROM products WHERE product_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result && $result['artisan_id'] == $artisan_id;
    }
}
?>