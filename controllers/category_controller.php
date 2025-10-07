<?php
require_once '../classes/category_class.php';

/**
 * Category Controller - Business logic layer
 * Coordinates between Views and Models for category management
 */

/**
 * Add a new category
 * @param string $cat_name Category name
 * @return int|false Category ID on success, false on failure
 */
function add_category_ctr($cat_name)
{
    try {
        // Validate input
        if (empty(trim($cat_name))) {
            return false;
        }
        
        // Sanitize input
        $cat_name = trim($cat_name);
        
        $category = new Category();
        $result = $category->addCategory($cat_name);
        
        error_log("Add category attempt: " . $cat_name . " - Result: " . ($result ? 'Success (ID: ' . $result . ')' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Add category exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all categories
 * @return array|false Array of categories or false on failure
 */
function get_all_categories_ctr()
{
    try {
        $category = new Category();
        return $category->getAllCategories();
    } catch (Exception $e) {
        error_log("Get all categories exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get categories with product counts
 * @return array|false Array of categories with product counts
 */
function get_categories_with_counts_ctr()
{
    try {
        $category = new Category();
        return $category->getCategoriesWithProductCount();
    } catch (Exception $e) {
        error_log("Get categories with counts exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get category by ID
 * @param int $cat_id Category ID
 * @return array|false Category data or false if not found
 */
function get_category_by_id_ctr($cat_id)
{
    try {
        if (!is_numeric($cat_id) || $cat_id <= 0) {
            return false;
        }
        
        $category = new Category();
        return $category->getCategoryById($cat_id);
    } catch (Exception $e) {
        error_log("Get category by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get category by name
 * @param string $cat_name Category name
 * @return array|false Category data or false if not found
 */
function get_category_by_name_ctr($cat_name)
{
    try {
        if (empty(trim($cat_name))) {
            return false;
        }
        
        $category = new Category();
        return $category->getCategoryByName(trim($cat_name));
    } catch (Exception $e) {
        error_log("Get category by name exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update category
 * @param int $cat_id Category ID
 * @param string $cat_name New category name
 * @return bool Success status
 */
function update_category_ctr($cat_id, $cat_name)
{
    try {
        // Validate inputs
        if (!is_numeric($cat_id) || $cat_id <= 0 || empty(trim($cat_name))) {
            return false;
        }
        
        // Sanitize input
        $cat_name = trim($cat_name);
        
        $category = new Category();
        $result = $category->updateCategory($cat_id, $cat_name);
        
        error_log("Update category attempt - ID: " . $cat_id . ", Name: " . $cat_name . " - Result: " . ($result ? 'Success' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Update category exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete category
 * @param int $cat_id Category ID to delete
 * @return bool Success status
 */
function delete_category_ctr($cat_id)
{
    try {
        if (!is_numeric($cat_id) || $cat_id <= 0) {
            return false;
        }
        
        $category = new Category();
        $result = $category->deleteCategory($cat_id);
        
        error_log("Delete category attempt - ID: " . $cat_id . " - Result: " . ($result ? 'Success' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Delete category exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if category name exists
 * @param string $cat_name Category name to check
 * @return bool True if exists, false otherwise
 */
function category_exists_ctr($cat_name)
{
    try {
        if (empty(trim($cat_name))) {
            return false;
        }
        
        $category = new Category();
        $exists = $category->categoryExists(trim($cat_name));
        
        error_log("Category exists check for: " . $cat_name . " - Result: " . ($exists ? 'EXISTS' : 'AVAILABLE'));
        
        return $exists;
    } catch (Exception $e) {
        error_log("Category exists check exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Search categories by name
 * @param string $search_term Search term
 * @return array|false Array of matching categories
 */
function search_categories_ctr($search_term)
{
    try {
        if (empty(trim($search_term))) {
            return get_all_categories_ctr();
        }
        
        $category = new Category();
        return $category->searchCategories(trim($search_term));
    } catch (Exception $e) {
        error_log("Search categories exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate category name
 * @param string $cat_name Category name to validate
 * @return array Validation result with status and message
 */
function validate_category_name_ctr($cat_name)
{
    $result = ['valid' => false, 'message' => ''];
    
    // Check if empty
    if (empty(trim($cat_name))) {
        $result['message'] = 'Category name is required';
        return $result;
    }
    
    // Check length
    $cat_name = trim($cat_name);
    if (strlen($cat_name) < 2) {
        $result['message'] = 'Category name must be at least 2 characters long';
        return $result;
    }
    
    if (strlen($cat_name) > 100) {
        $result['message'] = 'Category name must be less than 100 characters';
        return $result;
    }
    
    // Check for valid characters (letters, numbers, spaces, hyphens, underscores)
    if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $cat_name)) {
        $result['message'] = 'Category name can only contain letters, numbers, spaces, hyphens, and underscores';
        return $result;
    }
    
    // Check if category name already exists
    if (category_exists_ctr($cat_name)) {
        $result['message'] = 'Category name already exists';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Category name is valid';
    return $result;
}
?>