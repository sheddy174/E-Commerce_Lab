<?php
// Include the order class
require_once(__DIR__ . '/../classes/order_class.php');

/**
 * Order Controller - Business logic layer
 * Coordinates between Views and Models for order management
 * Part of MVC architecture - WITH PAYSTACK SUPPORT
 * CORRECTED VERSION - Fixed update_order_status_ctr function
 */

/**
 * Create a new order - UPDATED
 * @param int $customer_id Customer ID
 * @param string $invoice_no Invoice number
 * @param string $order_date Order date
 * @param string $order_status Order status (backward compatible)
 * @param string $payment_status Payment status
 * @param string $delivery_status Delivery status
 * @return int|false Order ID on success, false on failure
 */
function create_order_ctr(
    $customer_id,
    $invoice_no,
    $order_date,
    $order_status = 'Pending',
    $payment_status = 'pending',
    $delivery_status = 'pending'
) {
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: " . $customer_id);
            return false;
        }

        if (empty($invoice_no)) {
            error_log("Invalid invoice number: " . $invoice_no);
            return false;
        }

        $order = new order_class();
        $result = $order->create_order(
            $customer_id,
            $invoice_no,
            $order_date,
            $order_status,
            $payment_status,
            $delivery_status
        );

        error_log("Create order attempt - Customer: " . $customer_id . ", Invoice: " . $invoice_no .
            ", Payment: " . $payment_status . ", Delivery: " . $delivery_status .
            " - Result: " . ($result ? 'Success (Order ID: ' . $result . ')' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Create order exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Add order details (products in order)
 * @param int $order_id Order ID
 * @param int $product_id Product ID
 * @param int $qty Quantity
 * @return bool Success status
 */
function add_order_details_ctr($order_id, $product_id, $qty)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return false;
        }

        if (!is_numeric($product_id) || $product_id <= 0) {
            error_log("Invalid product ID: " . $product_id);
            return false;
        }

        if (!is_numeric($qty) || $qty <= 0) {
            error_log("Invalid quantity: " . $qty);
            return false;
        }

        $order = new order_class();
        $result = $order->add_order_details($order_id, $product_id, $qty);

        error_log("Add order details - Order: " . $order_id . ", Product: " . $product_id . ", Qty: " . $qty . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Add order details exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Record payment for an order - NOW WITH PAYSTACK SUPPORT
 * @param float $amount Payment amount
 * @param int $customer_id Customer ID
 * @param int $order_id Order ID
 * @param string $currency Currency code
 * @param string $payment_date Payment date (YYYY-MM-DD)
 * @param string $payment_method Payment method (default: 'direct')
 * @param string $transaction_ref Transaction reference from Paystack
 * @param string $authorization_code Authorization code from Paystack
 * @param string $payment_channel Payment channel (card, mobile_money, etc.)
 * @return int|false Payment ID on success, false on failure
 */
function record_payment_ctr(
    $amount,
    $customer_id,
    $order_id,
    $currency = 'GHS',
    $payment_date,
    $payment_method = 'direct',
    $transaction_ref = null,
    $authorization_code = null,
    $payment_channel = null
) {
    try {
        if (!is_numeric($amount) || $amount <= 0) {
            error_log("Invalid payment amount: " . $amount);
            return false;
        }

        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: " . $customer_id);
            return false;
        }

        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return false;
        }

        $order = new order_class();
        $result = $order->record_payment(
            $amount,
            $customer_id,
            $order_id,
            $currency,
            $payment_date,
            $payment_method,
            $transaction_ref,
            $authorization_code,
            $payment_channel
        );

        $payment_info = $payment_method . ($transaction_ref ? " (Ref: $transaction_ref)" : "");
        error_log("Record payment - Order: " . $order_id . ", Amount: " . $amount . " " . $currency .
            ", Method: " . $payment_info . " - Result: " . ($result ? 'Success (Payment ID: ' . $result . ')' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Record payment exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all orders for a user
 * @param int $customer_id Customer ID
 * @return array|false Array of orders or false on failure
 */
function get_user_orders_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: " . $customer_id);
            return false;
        }

        $order = new order_class();
        return $order->get_user_orders($customer_id);
    } catch (Exception $e) {
        error_log("Get user orders exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get customer orders (alias for backward compatibility)
 * @param int $customer_id Customer ID
 * @return array|false Array of orders or false on failure
 */
function get_customer_orders_ctr($customer_id)
{
    return get_user_orders_ctr($customer_id);
}

/**
 * Get order by ID with full details
 * @param int $order_id Order ID
 * @return array|false Order data or false if not found
 */
function get_order_by_id_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return false;
        }

        $order = new order_class();
        return $order->get_order_by_id($order_id);
    } catch (Exception $e) {
        error_log("Get order by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order details with customer check
 * @param int $order_id Order ID
 * @param int $customer_id Customer ID (for security)
 * @return array|false Order data or false if not found
 */
function get_order_details_ctr($order_id, $customer_id = null)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return false;
        }

        $order = new order_class();

        // If customer_id provided, use secure method
        if ($customer_id !== null) {
            return $order->get_order_details($order_id, $customer_id);
        }

        // Otherwise use admin method
        return $order->get_order_by_id($order_id);
    } catch (Exception $e) {
        error_log("Get order details exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all products in a specific order
 * @param int $order_id Order ID
 * @return array|false Array of products or false on failure
 */
function get_order_products_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return false;
        }

        $order = new order_class();
        return $order->get_order_products($order_id);
    } catch (Exception $e) {
        error_log("Get order products exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update order status WITH tracking and notes support - CORRECTED
 * @param int $order_id Order ID
 * @param string $order_status New status
 * @param string|null $tracking_number Optional tracking number
 * @param string|null $notes Optional delivery notes
 * @return bool Success status
 */
function update_order_status_ctr($order_id, $order_status, $tracking_number = null, $notes = null)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return false;
        }

        if (empty(trim($order_status))) {
            error_log("Invalid order status: empty");
            return false;
        }

        $order = new order_class();
        $result = $order->update_order_status($order_id, $order_status, $tracking_number, $notes);

        $tracking_info = $tracking_number ? " with tracking: " . $tracking_number : "";
        $notes_info = $notes ? " with notes" : "";
        error_log("Update order status - Order: " . $order_id . ", Status: " . $order_status . $tracking_info . $notes_info . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Update order status exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique invoice number
 * @return string Unique invoice number
 */
function generate_invoice_number_ctr()
{
    try {
        $order = new order_class();
        $invoice = $order->generate_invoice_number();

        error_log("Generated invoice number: " . $invoice);

        return $invoice;
    } catch (Exception $e) {
        error_log("Generate invoice number exception: " . $e->getMessage());
        // Fallback: Generate timestamp-based invoice
        $fallback = 'GTUNES-' . date('Ymd') . '-' . time();
        error_log("Using fallback invoice: " . $fallback);
        return $fallback;
    }
}

/**
 * Calculate order total from order details
 * @param int $order_id Order ID
 * @return float Total amount
 */
function calculate_order_total_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            error_log("Invalid order ID: " . $order_id);
            return 0;
        }

        $order = new order_class();
        return $order->calculate_order_total($order_id);
    } catch (Exception $e) {
        error_log("Calculate order total exception: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get order statistics
 */
function get_order_stats_ctr()
{
    try {
        require_once(__DIR__ . '/../classes/order_class.php');
        $order = new order_class();
        return $order->get_order_stats();
    } catch (Exception $e) {
        error_log("Get order stats exception: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all orders (Admin function) - UPDATED with filters
 * @param string|null $status Filter by delivery status
 * @param string|null $search Search by customer name/email
 * @param int $limit Limit results
 * @param int $offset Offset for pagination
 * @return array|false Array of orders or false on failure
 */
function get_all_orders_ctr($status = null, $search = null, $limit = 50, $offset = 0)
{
    try {
        $order = new order_class();
        return $order->get_all_orders($limit, $offset, $status, $search);
    } catch (Exception $e) {
        error_log("Get all orders exception: " . $e->getMessage());
        return false;
    }
}
?>