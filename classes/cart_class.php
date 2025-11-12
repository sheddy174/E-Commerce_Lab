<?php
require_once '../settings/db_class.php';

/**
 * Cart Model Class
 * Handles all cart-related database operations
 * Part of Model layer in MVC architecture
 * Cart Management
 */
class Cart extends db_connection
{
    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        parent::db_connect();
    }

    /**
     * Check if product already exists in user's cart
     * @param int $product_id Product ID
     * @param int $customer_id Customer ID
     * @return array|false Cart item data or false if not found
     */
    public function checkProductInCart($product_id, $customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM cart 
            WHERE p_id = ? AND c_id = ?
        ");
        
        if (!$stmt) {
            error_log("Check product in cart prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Add product to cart
     * If product exists, increment quantity
     * @param int $product_id Product ID
     * @param int $customer_id Customer ID
     * @param string $ip_address IP address (for logged-in users, still store for reference)
     * @param int $qty Quantity to add
     * @return bool Success status
     */
    public function addToCart($product_id, $customer_id, $ip_address, $qty = 1)
    {
        // Check if product already exists in cart
        $existing = $this->checkProductInCart($product_id, $customer_id);
        
        if ($existing) {
            // Product exists, update quantity
            $new_qty = $existing['qty'] + $qty;
            return $this->updateCartQuantity($product_id, $customer_id, $new_qty);
        } else {
            // Product doesn't exist, insert new
            $stmt = $this->db->prepare("
                INSERT INTO cart (p_id, c_id, ip_add, qty) 
                VALUES (?, ?, ?, ?)
            ");
            
            if (!$stmt) {
                error_log("Add to cart prepare failed: " . $this->db->error);
                return false;
            }

            $stmt->bind_param("iisi", $product_id, $customer_id, $ip_address, $qty);
            $success = $stmt->execute();
            
            if (!$success) {
                error_log("Add to cart execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            return $success;
        }
    }

    /**
     * Update quantity of a product in cart
     * @param int $product_id Product ID
     * @param int $customer_id Customer ID
     * @param int $qty New quantity
     * @return bool Success status
     */
    public function updateCartQuantity($product_id, $customer_id, $qty)
    {
        // If quantity is 0 or negative, remove from cart
        if ($qty <= 0) {
            return $this->removeFromCart($product_id, $customer_id);
        }

        $stmt = $this->db->prepare("
            UPDATE cart 
            SET qty = ? 
            WHERE p_id = ? AND c_id = ?
        ");
        
        if (!$stmt) {
            error_log("Update cart quantity prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iii", $qty, $product_id, $customer_id);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Update cart quantity execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $success;
    }

    /**
     * Remove product from cart
     * @param int $product_id Product ID
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function removeFromCart($product_id, $customer_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM cart 
            WHERE p_id = ? AND c_id = ?
        ");
        
        if (!$stmt) {
            error_log("Remove from cart prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ii", $product_id, $customer_id);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Remove from cart execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $success;
    }

    /**
     * Get all cart items for a user with product details
     * @param int $customer_id Customer ID
     * @return array|false Array of cart items or false on failure
     */
    public function getUserCart($customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                p.product_id,
                p.product_title,
                p.product_price,
                p.product_image,
                p.product_desc,
                cat.cat_name,
                b.brand_name
            FROM cart c
            INNER JOIN products p ON c.p_id = p.product_id
            LEFT JOIN categories cat ON p.product_cat = cat.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE c.c_id = ?
            ORDER BY c.p_id DESC
        ");
        
        if (!$stmt) {
            error_log("Get user cart prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_items = [];
        
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
        }
        
        $stmt->close();
        return $cart_items;
    }

    /**
     * Empty entire cart for a user
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function emptyCart($customer_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM cart 
            WHERE c_id = ?
        ");
        
        if (!$stmt) {
            error_log("Empty cart prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $customer_id);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Empty cart execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $success;
    }

    /**
     * Get cart item count for a user
     * @param int $customer_id Customer ID
     * @return int Number of items in cart
     */
    public function getCartItemCount($customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(qty) as total_items 
            FROM cart 
            WHERE c_id = ?
        ");
        
        if (!$stmt) {
            error_log("Get cart count prepare failed: " . $this->db->error);
            return 0;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result ? (int)$result['total_items'] : 0;
    }

    /**
     * Get cart total amount for a user
     * @param int $customer_id Customer ID
     * @return float Total cart value
     */
    public function getCartTotal($customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(c.qty * p.product_price) as total 
            FROM cart c
            INNER JOIN products p ON c.p_id = p.product_id
            WHERE c.c_id = ?
        ");
        
        if (!$stmt) {
            error_log("Get cart total prepare failed: " . $this->db->error);
            return 0;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result ? (float)$result['total'] : 0;
    }

    /**
     * Validate cart items (check if all products still exist and have valid prices)
     * @param int $customer_id Customer ID
     * @return array Validation result with status and invalid items
     */
    public function validateCart($customer_id)
    {
        $cart_items = $this->getUserCart($customer_id);
        $invalid_items = [];
        
        foreach ($cart_items as $item) {
            // Check if product still exists and is valid
            if (empty($item['product_id']) || $item['product_price'] <= 0) {
                $invalid_items[] = $item;
            }
        }
        
        return [
            'valid' => empty($invalid_items),
            'invalid_items' => $invalid_items
        ];
    }
}
?>