<?php
require_once '../classes/order_class.php';

/**
 * Order Controller - Business logic layer
 * Coordinates between Views and Models for order management
 * Part of MVC architecture
 * Order Management
 */

/**
 * Create a new order
 * @param int $customer_id Customer ID
 * @param int $invoice_no Invoice number
 * @param string $order_date Order date
 * @param string $order_status Order status
 * @return int|false Order ID on success, false on failure
 */
function create_order_ctr($customer_id, $invoice_no, $order_date, $order_status = 'Pending')
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            error_log("Invalid customer ID: " . $customer_id);
            return false;
        }

        if (!is_numeric($invoice_no) || $invoice_no <= 0) {
            error_log("Invalid invoice number: " . $invoice_no);
            return false;
        }

        $order = new Order();
        $result = $order->createOrder($customer_id, $invoice_no, $order_date, $order_status);

        error_log("Create order attempt - Customer: " . $customer_id . ", Invoice: " . $invoice_no . " - Result: " . ($result ? 'Success (Order ID: ' . $result . ')' : 'Failed'));

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

        $order = new Order();
        $result = $order->addOrderDetails($order_id, $product_id, $qty);

        error_log("Add order details - Order: " . $order_id . ", Product: " . $product_id . ", Qty: " . $qty . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Add order details exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Record payment for an order
 * @param float $amount Payment amount
 * @param int $customer_id Customer ID
 * @param int $order_id Order ID
 * @param string $currency Currency code
 * @param string $payment_date Payment date
 * @return int|false Payment ID on success, false on failure
 */
function record_payment_ctr($amount, $customer_id, $order_id, $currency = 'GHS', $payment_date)
{
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

        $order = new Order();
        $result = $order->recordPayment($amount, $customer_id, $order_id, $currency, $payment_date);

        error_log("Record payment - Order: " . $order_id . ", Amount: " . $amount . " " . $currency . " - Result: " . ($result ? 'Success (Payment ID: ' . $result . ')' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Record payment exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get customer orders
 * @param int $customer_id Customer ID
 * @return array|false Array of orders or false on failure
 */
function get_customer_orders_ctr($customer_id)
{
    try {
        if (!is_numeric($customer_id) || $customer_id <= 0) {
            return false;
        }

        $order = new Order();
        return $order->getCustomerOrders($customer_id);
    } catch (Exception $e) {
        error_log("Get customer orders exception: " . $e->getMessage());
        return false;
    }
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
            return false;
        }

        $order = new Order();
        return $order->getOrderById($order_id);
    } catch (Exception $e) {
        error_log("Get order by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get order details (products in order)
 * @param int $order_id Order ID
 * @return array|false Array of order items or false on failure
 */
function get_order_details_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            return false;
        }

        $order = new Order();
        return $order->getOrderDetails($order_id);
    } catch (Exception $e) {
        error_log("Get order details exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Update order status
 * @param int $order_id Order ID
 * @param string $order_status New status
 * @return bool Success status
 */
function update_order_status_ctr($order_id, $order_status)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            return false;
        }

        if (empty(trim($order_status))) {
            return false;
        }

        $order = new Order();
        $result = $order->updateOrderStatus($order_id, $order_status);

        error_log("Update order status - Order: " . $order_id . ", Status: " . $order_status . " - Result: " . ($result ? 'Success' : 'Failed'));

        return $result;
    } catch (Exception $e) {
        error_log("Update order status exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique invoice number
 * @return int Unique invoice number
 */
function generate_invoice_number_ctr()
{
    try {
        $order = new Order();
        return $order->generateInvoiceNumber();
    } catch (Exception $e) {
        error_log("Generate invoice number exception: " . $e->getMessage());
        // Fallback: Generate a unique number based on timestamp
        return (int)(date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT));
    }
}

/**
 * Get all orders (Admin function)
 * @param int $limit Limit number of results
 * @param int $offset Offset for pagination
 * @return array|false Array of orders or false on failure
 */
function get_all_orders_ctr($limit = 50, $offset = 0)
{
    try {
        $order = new Order();
        return $order->getAllOrders($limit, $offset);
    } catch (Exception $e) {
        error_log("Get all orders exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Calculate order total
 * @param int $order_id Order ID
 * @return float Total amount
 */
function calculate_order_total_ctr($order_id)
{
    try {
        if (!is_numeric($order_id) || $order_id <= 0) {
            return 0;
        }

        $order = new Order();
        return $order->calculateOrderTotal($order_id);
    } catch (Exception $e) {
        error_log("Calculate order total exception: " . $e->getMessage());
        return 0;
    }
}
?>