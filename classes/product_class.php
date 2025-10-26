<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../settings/db_class.php';

/**
 * Product Model Class - Handles all product-related database operations
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
     * @return int|false Product ID on success, false on failure
     */
    public function addProduct($product_cat, $product_brand, $product_title, $product_price, 
                               $product_desc, $product_image, $product_keywords)
    {
        $stmt = $this->db->prepare("INSERT INTO products (product_cat, product_brand, product_title, 
                                     product_price, product_desc, product_image, product_keywords) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iisdsss", $product_cat, $product_brand, $product_title, 
                         $product_price, $product_desc, $product_image, $product_keywords);
        
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
    public function updateProduct($product_id, $product_cat, $product_brand, $product_title, 
                                  $product_price, $product_desc, $product_image, $product_keywords)
    {
        // If image is null, don't update it (keep existing image)
        if ($product_image === null) {
            $stmt = $this->db->prepare("UPDATE products SET product_cat = ?, product_brand = ?, 
                                        product_title = ?, product_price = ?, product_desc = ?, 
                                        product_keywords = ? WHERE product_id = ?");
            
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("iisdssi", $product_cat, $product_brand, $product_title, 
                             $product_price, $product_desc, $product_keywords, $product_id);
        } else {
            $stmt = $this->db->prepare("UPDATE products SET product_cat = ?, product_brand = ?, 
                                        product_title = ?, product_price = ?, product_desc = ?, 
                                        product_image = ?, product_keywords = ? WHERE product_id = ?");
            
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("iisdsssi", $product_cat, $product_brand, $product_title, 
                             $product_price, $product_desc, $product_image, $product_keywords, $product_id);
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
            if (file_exists($image_path)) {
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
     * Search products by keyword
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
}
?>