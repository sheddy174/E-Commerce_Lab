<?php
require_once '../classes/cart_class.php';

/**
 * Cart Controller - Business logic layer
 * Coordinates between Views and Models for cart management
 */

/**
 * Add product to cart
 * @param int $product_id Product ID
 * @param int $customer_id Customer ID
 * @param string $ip_address IP address
 * @param int $qty Quantity
 * @return bool Success status
 */
function add_to_cart_ctr($product_id, $customer_id, $ip_address, $qty = 1)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: " . $product_id);
            return false;
        }

        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: " . $customer_id);
            return false;
        }

        if (!is_numeric($qty) || $qty <= 0) {
            error_log("Invalid quantity: " . $qty);
            return false;
        }

        $cart = new Cart();
        $result = $cart->addToCart($product_id, $customer_id, $ip_address, $qty);

        error_log("Add to cart attempt - Product: " . $product_id . ", Customer: " . $customer_id . ", Qty: " . $qty . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Add to cart exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update cart item quantity
 * @param int $product_id Product ID
 * @param int $customer_id Customer ID
 * @param int $qty New quantity
 * @return bool Success status
 */
function update_cart_item_ctr($product_id, $customer_id, $qty)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            return false;
        }

        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return false;
        }

        if (!is_numeric($qty) || $qty < 0) {
            return false;
        }

        $cart = new Cart();
        $result = $cart->updateCartQuantity($product_id, $customer_id, $qty);

        error_log("Update cart quantity - Product: " . $product_id . ", Customer: " . $customer_id . ", New Qty: " . $qty . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Update cart item exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove product from cart
 * @param int $product_id Product ID
 * @param int $customer_id Customer ID
 * @return bool Success status
 */
function remove_from_cart_ctr($product_id, $customer_id)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0) {
            return false;
        }

        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return false;
        }

        $cart = new Cart();
        $result = $cart->removeFromCart($product_id, $customer_id);

        error_log("Remove from cart - Product: " . $product_id . ", Customer: " . $customer_id . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Remove from cart exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's cart with all items and product details
 * @param int $customer_id Customer ID
 * @return array|false Array of cart items or false on failure
 */
function get_user_cart_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return false;
        }

        $cart = new Cart();
        return $cart->getUserCart($customer_id);
    } catch (Exception $e) {
        error_log("Get user cart exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Empty user's entire cart
 * @param int $customer_id Customer ID
 * @return bool Success status
 */
function empty_cart_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return false;
        }

        $cart = new Cart();
        $result = $cart->emptyCart($customer_id);

        error_log("Empty cart - Customer: " . $customer_id . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Empty cart exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cart item count for a user
 * @param int $customer_id Customer ID
 * @return int Number of items in cart
 */
function get_cart_item_count_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return 0;
        }

        $cart = new Cart();
        return $cart->getCartItemCount($customer_id);
    } catch (Exception $e) {
        error_log("Get cart item count exception: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get cart total amount for a user
 * @param int $customer_id Customer ID
 * @return float Total cart value
 */
function get_cart_total_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return 0;
        }

        $cart = new Cart();
        return $cart->getCartTotal($customer_id);
    } catch (Exception $e) {
        error_log("Get cart total exception: " . $e->getMessage());
        return 0;
    }
}

/**
 * Validate cart items
 * @param int $customer_id Customer ID
 * @return array Validation result
 */
function validate_cart_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return ['valid' => false, 'invalid_items' => []];
        }

        $cart = new Cart();
        return $cart->validateCart($customer_id);
    } catch (Exception $e) {
        error_log("Validate cart exception: " . $e->getMessage());
        return ['valid' => false, 'invalid_items' => []];
    }
}

/**
 * Check if product is in cart
 * @param int $product_id Product ID
 * @param int $customer_id Customer ID
 * @return array|false Cart item data or false
 */
function check_product_in_cart_ctr($product_id, $customer_id)
{
    try {
        if (!is_numeric($product_id) || $product_id <= 0 || !is_numeric($customer_id) || $customer_id <= 0) {
            return false;
        }

        $cart = new Cart();
        return $cart->checkProductInCart($product_id, $customer_id);
    } catch (Exception $e) {
        error_log("Check product in cart exception: " . $e->getMessage());
        return false;
    }
}
?>