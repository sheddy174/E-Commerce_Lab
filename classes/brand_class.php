<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../settings/db_class.php';

/**
 * Brand Model Class - Handles all brand-related database operations
 */
class Brand extends db_connection
{
    private $brand_id;
    private $brand_name;
    private $brand_cat;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct($brand_id = null)
    {
        parent::db_connect();
        if ($brand_id) {
            $this->brand_id = $brand_id;
            $this->loadBrand();
        }
    }

    /**
     * Load brand data from database
     */
    private function loadBrand($brand_id = null)
    {
        if ($brand_id) {
            $this->brand_id = $brand_id;
        }
        if (!$this->brand_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM brands WHERE brand_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->brand_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->brand_name = $result['brand_name'];
            $this->brand_cat = $result['brand_cat'];
            return true;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Create new brand
     * @param string $brand_name Brand name
     * @param int $brand_cat Category ID the brand belongs to
     * @return int|false Brand ID on success, false on failure
     */
    public function addBrand($brand_name, $brand_cat)
    {
        // Check if brand name + category combination already exists
        if ($this->brandCategoryExists($brand_name, $brand_cat)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO brands (brand_name, brand_cat) VALUES (?, ?)");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $brand_name, $brand_cat);
        
        if ($stmt->execute()) {
            $brand_id = $this->db->insert_id;
            $stmt->close();
            return $brand_id;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get brand by ID
     * @param int $brand_id Brand ID
     * @return array|false Brand data or false if not found
     */
    public function getBrandById($brand_id)
    {
        $stmt = $this->db->prepare("
            SELECT b.*, c.cat_name 
            FROM brands b 
            LEFT JOIN categories c ON b.brand_cat = c.cat_id 
            WHERE b.brand_id = ?
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $brand_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get brand by name and category
     * @param string $brand_name Brand name
     * @param int $brand_cat Category ID
     * @return array|false Brand data or false if not found
     */
    public function getBrandByNameAndCategory($brand_name, $brand_cat)
    {
        $stmt = $this->db->prepare("SELECT * FROM brands WHERE brand_name = ? AND brand_cat = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $brand_name, $brand_cat);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Check if brand name + category combination already exists
     * @param string $brand_name Brand name to check
     * @param int $brand_cat Category ID
     * @return bool True if exists, false otherwise
     */
    public function brandCategoryExists($brand_name, $brand_cat)
    {
        $stmt = $this->db->prepare("SELECT brand_id FROM brands WHERE brand_name = ? AND brand_cat = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $brand_name, $brand_cat);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    /**
     * Update brand
     * @param int $brand_id Brand ID
     * @param string $brand_name New brand name
     * @param int $brand_cat New category ID
     * @return bool Success status
     */
    public function updateBrand($brand_id, $brand_name, $brand_cat)
    {
        // Check if new name + category combination already exists (exclude current brand)
        $stmt = $this->db->prepare("SELECT brand_id FROM brands WHERE brand_name = ? AND brand_cat = ? AND brand_id != ?");
        if ($stmt) {
            $stmt->bind_param("sii", $brand_name, $brand_cat, $brand_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt->close();
                return false; // Combination already exists
            }
            $stmt->close();
        }

        $stmt = $this->db->prepare("UPDATE brands SET brand_name = ?, brand_cat = ? WHERE brand_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sii", $brand_name, $brand_cat, $brand_id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Delete brand
     * @param int $brand_id Brand ID to delete
     * @return bool Success status
     */
    public function deleteBrand($brand_id)
    {
        // Check if brand has associated products first
        $stmt = $this->db->prepare("SELECT COUNT(*) as product_count FROM products WHERE product_brand = ?");
        if ($stmt) {
            $stmt->bind_param("i", $brand_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['product_count'] > 0) {
                $stmt->close();
                return false; // Cannot delete brand with products
            }
            $stmt->close();
        }

        $stmt = $this->db->prepare("DELETE FROM brands WHERE brand_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $brand_id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Get all brands with category information
     * @return array|false Array of brands or false on failure
     */
    public function getAllBrands()
    {
        $sql = "SELECT b.brand_id, b.brand_name, b.brand_cat, c.cat_name 
                FROM brands b 
                LEFT JOIN categories c ON b.brand_cat = c.cat_id 
                ORDER BY c.cat_name ASC, b.brand_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get brands with product count
     * @return array|false Array of brands with product counts
     */
    public function getBrandsWithProductCount()
    {
        $sql = "SELECT b.brand_id, b.brand_name, b.brand_cat, c.cat_name, 
                       COUNT(p.product_id) as product_count 
                FROM brands b 
                LEFT JOIN categories c ON b.brand_cat = c.cat_id 
                LEFT JOIN products p ON b.brand_id = p.product_brand 
                GROUP BY b.brand_id, b.brand_name, b.brand_cat, c.cat_name 
                ORDER BY c.cat_name ASC, b.brand_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get brands by category
     * @param int $cat_id Category ID
     * @return array|false Array of brands in that category
     */
    public function getBrandsByCategory($cat_id)
    {
        $stmt = $this->db->prepare("
            SELECT b.brand_id, b.brand_name, b.brand_cat, c.cat_name 
            FROM brands b 
            LEFT JOIN categories c ON b.brand_cat = c.cat_id 
            WHERE b.brand_cat = ? 
            ORDER BY b.brand_name ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $brands = [];
        
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }
        
        $stmt->close();
        return $brands;
    }

    /**
     * Search brands by name
     * @param string $search_term Search term
     * @return array|false Array of matching brands
     */
    public function searchBrands($search_term)
    {
        $search_term = '%' . $search_term . '%';
        $stmt = $this->db->prepare("
            SELECT b.brand_id, b.brand_name, b.brand_cat, c.cat_name 
            FROM brands b 
            LEFT JOIN categories c ON b.brand_cat = c.cat_id 
            WHERE b.brand_name LIKE ? 
            ORDER BY c.cat_name ASC, b.brand_name ASC
        ");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $brands = [];
        
        while ($row = $result->fetch_assoc()) {
            $brands[] = $row;
        }
        
        $stmt->close();
        return $brands;
    }

    /**
     * Get count of brands added today
     * @return int Count of brands added today
     */
    public function getBrandsAddedTodayCount()
    {
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as today_count 
            FROM brands 
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
}
?>