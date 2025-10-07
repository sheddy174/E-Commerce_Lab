<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
require_once '../settings/db_class.php';

/**
 * Category Model Class - Handles all category-related database operations
 * Part of Model layer in MVC architecture
 */
class Category extends db_connection
{
    private $cat_id;
    private $cat_name;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct($cat_id = null)
    {
        parent::db_connect();
        if ($cat_id) {
            $this->cat_id = $cat_id;
            $this->loadCategory();
        }
    }

    /**
     * Load category data from database
     */
    private function loadCategory($cat_id = null)
    {
        if ($cat_id) {
            $this->cat_id = $cat_id;
        }
        if (!$this->cat_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM categories WHERE cat_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->cat_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->cat_name = $result['cat_name'];
            return true;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Create new category
     * @param string $cat_name Category name
     * @return int|false Category ID on success, false on failure
     */
    public function addCategory($cat_name)
    {
        // Check if category name already exists
        if ($this->categoryExists($cat_name)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO categories (cat_name) VALUES (?)");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $cat_name);
        
        if ($stmt->execute()) {
            $cat_id = $this->db->insert_id;
            $stmt->close();
            return $cat_id;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get category by ID
     * @param int $cat_id Category ID
     * @return array|false Category data or false if not found
     */
    public function getCategoryById($cat_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE cat_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get category by name
     * @param string $cat_name Category name
     * @return array|false Category data or false if not found
     */
    public function getCategoryByName($cat_name)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE cat_name = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $cat_name);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Check if category name already exists
     * @param string $cat_name Category name to check
     * @return bool True if exists, false otherwise
     */
    public function categoryExists($cat_name)
    {
        $stmt = $this->db->prepare("SELECT cat_id FROM categories WHERE cat_name = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $cat_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    /**
     * Update category
     * @param int $cat_id Category ID
     * @param string $cat_name New category name
     * @return bool Success status
     */
    public function updateCategory($cat_id, $cat_name)
    {
        // Check if new name already exists (exclude current category)
        $stmt = $this->db->prepare("SELECT cat_id FROM categories WHERE cat_name = ? AND cat_id != ?");
        if ($stmt) {
            $stmt->bind_param("si", $cat_name, $cat_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt->close();
                return false; // Name already exists
            }
            $stmt->close();
        }

        $stmt = $this->db->prepare("UPDATE categories SET cat_name = ? WHERE cat_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $cat_name, $cat_id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Delete category
     * @param int $cat_id Category ID to delete
     * @return bool Success status
     */
    public function deleteCategory($cat_id)
    {
        // Check if category has associated products first
        $stmt = $this->db->prepare("SELECT COUNT(*) as product_count FROM products WHERE product_cat = ?");
        if ($stmt) {
            $stmt->bind_param("i", $cat_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['product_count'] > 0) {
                $stmt->close();
                return false; // Cannot delete category with products
            }
            $stmt->close();
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE cat_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $cat_id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Get all categories
     * @return array|false Array of categories or false on failure
     */
    public function getAllCategories()
    {
        $sql = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Get categories with product count
     * @return array|false Array of categories with product counts
     */
    public function getCategoriesWithProductCount()
    {
        $sql = "SELECT c.cat_id, c.cat_name, COUNT(p.product_id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.cat_id = p.product_cat 
                GROUP BY c.cat_id, c.cat_name 
                ORDER BY c.cat_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Search categories by name
     * @param string $search_term Search term
     * @return array|false Array of matching categories
     */
    public function searchCategories($search_term)
    {
        $search_term = '%' . $search_term . '%';
        $stmt = $this->db->prepare("SELECT cat_id, cat_name FROM categories WHERE cat_name LIKE ? ORDER BY cat_name ASC");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        $stmt->close();
        return $categories;
    }
}
?>