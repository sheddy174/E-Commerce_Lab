<?php
require_once '../settings/db_class.php';

/**
 * Order Model Class
 * Handles all order, order details, and payment operations
 */
class Order extends db_connection
{
    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        parent::db_connect();
    }

    /**
     * Create a new order
     * @param int $customer_id Customer ID
     * @param int $invoice_no Unique invoice number
     * @param string $order_date Order date (Y-m-d format)
     * @param string $order_status Order status (default: 'Pending')
     * @return int|false Order ID on success, false on failure
     */
    public function createOrder($customer_id, $invoice_no, $order_date, $order_status = 'Pending')
    {
        $stmt = $this->db->prepare("
            INSERT INTO orders (customer_id, invoice_no, order_date, order_status) 
            VALUES (?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Create order prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iiss", $customer_id, $invoice_no, $order_date, $order_status);
        
        if ($stmt->execute()) {
            $order_id = $this->db->insert_id;
            $stmt->close();
            error_log("Order created successfully - Order ID: " . $order_id . ", Customer: " . $customer_id);
            return $order_id;
        }
        
        error_log("Create order execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Add order details (products in the order)
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @param int $qty Quantity ordered
     * @return bool Success status
     */
    public function addOrderDetails($order_id, $product_id, $qty)
    {
        $stmt = $this->db->prepare("
            INSERT INTO orderdetails (order_id, product_id, qty) 
            VALUES (?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Add order details prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iii", $order_id, $product_id, $qty);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Add order details execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $success;
    }

    /**
     * Record payment for an order
     * @param float $amount Payment amount
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @param string $currency Currency code (default: 'GHS')
     * @param string $payment_date Payment date (Y-m-d format)
     * @return int|false Payment ID on success, false on failure
     */
    public function recordPayment($amount, $customer_id, $order_id, $currency = 'GHS', $payment_date)
    {
        $stmt = $this->db->prepare("
            INSERT INTO payment (amt, customer_id, order_id, currency, payment_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            error_log("Record payment prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("diiss", $amount, $customer_id, $order_id, $currency, $payment_date);
        
        if ($stmt->execute()) {
            $payment_id = $this->db->insert_id;
            $stmt->close();
            error_log("Payment recorded successfully - Payment ID: " . $payment_id . ", Amount: " . $amount);
            return $payment_id;
        }
        
        error_log("Record payment execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Get all orders for a customer
     * @param int $customer_id Customer ID
     * @return array|false Array of orders or false on failure
     */
    public function getCustomerOrders($customer_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                c.customer_name,
                c.customer_email,
                COUNT(DISTINCT od.product_id) as total_items,
                SUM(od.qty) as total_quantity,
                p.amt as payment_amount,
                p.currency as payment_currency,
                p.payment_date
            FROM orders o
            INNER JOIN customer c ON o.customer_id = c.customer_id
            LEFT JOIN orderdetails od ON o.order_id = od.order_id
            LEFT JOIN payment p ON o.order_id = p.order_id
            WHERE o.customer_id = ?
            GROUP BY o.order_id
            ORDER BY o.order_date DESC, o.order_id DESC
        ");
        
        if (!$stmt) {
            error_log("Get customer orders prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        $stmt->close();
        return $orders;
    }

    /**
     * Get order by ID with full details
     * @param int $order_id Order ID
     * @return array|false Order data or false if not found
     */
    public function getOrderById($order_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                c.customer_name,
                c.customer_email,
                c.customer_contact,
                c.customer_country,
                c.customer_city,
                p.amt as payment_amount,
                p.currency as payment_currency,
                p.payment_date
            FROM orders o
            INNER JOIN customer c ON o.customer_id = c.customer_id
            LEFT JOIN payment p ON o.order_id = p.order_id
            WHERE o.order_id = ?
        ");
        
        if (!$stmt) {
            error_log("Get order by ID prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get order details (products) for an order
     * @param int $order_id Order ID
     * @return array|false Array of order items or false on failure
     */
    public function getOrderDetails($order_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                od.*,
                p.product_title,
                p.product_price,
                p.product_image,
                p.product_desc,
                cat.cat_name,
                b.brand_name,
                (od.qty * p.product_price) as subtotal
            FROM orderdetails od
            INNER JOIN products p ON od.product_id = p.product_id
            LEFT JOIN categories cat ON p.product_cat = cat.cat_id
            LEFT JOIN brands b ON p.product_brand = b.brand_id
            WHERE od.order_id = ?
        ");
        
        if (!$stmt) {
            error_log("Get order details prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        $stmt->close();
        return $items;
    }

    /**
     * Update order status
     * @param int $order_id Order ID
     * @param string $order_status New status
     * @return bool Success status
     */
    public function updateOrderStatus($order_id, $order_status)
    {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET order_status = ? 
            WHERE order_id = ?
        ");
        
        if (!$stmt) {
            error_log("Update order status prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("si", $order_status, $order_id);
        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Update order status execute failed: " . $stmt->error);
        }
        
        $stmt->close();
        return $success;
    }

    /**
     * Generate unique invoice number
     * @return int Unique invoice number
     */
    public function generateInvoiceNumber()
    {
        // Get the highest invoice number
        $sql = "SELECT MAX(invoice_no) as max_invoice FROM orders";
        $result = $this->db_fetch_one($sql);
        
        $max_invoice = $result ? (int)$result['max_invoice'] : 0;
        
        // Generate new invoice number
        // Format: YYYYMMDD + sequential number (e.g., 202511120001)
        $date_prefix = date('Ymd');
        $new_invoice = (int)($date_prefix . '0001');
        
        // If there are existing invoices today, increment
        if ($max_invoice >= $new_invoice) {
            $new_invoice = $max_invoice + 1;
        }
        
        return $new_invoice;
    }

    /**
     * Get all orders (Admin function)
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array|false Array of orders or false on failure
     */
    public function getAllOrders($limit = 50, $offset = 0)
    {
        $sql = "
            SELECT 
                o.*,
                c.customer_name,
                c.customer_email,
                COUNT(DISTINCT od.product_id) as total_items,
                SUM(od.qty) as total_quantity,
                p.amt as payment_amount,
                p.currency as payment_currency
            FROM orders o
            INNER JOIN customer c ON o.customer_id = c.customer_id
            LEFT JOIN orderdetails od ON o.order_id = od.order_id
            LEFT JOIN payment p ON o.order_id = p.order_id
            GROUP BY o.order_id
            ORDER BY o.order_date DESC, o.order_id DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        
        return $this->db_fetch_all($sql);
    }

    /**
     * Calculate order total from order details
     * @param int $order_id Order ID
     * @return float Total amount
     */
    public function calculateOrderTotal($order_id)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(od.qty * p.product_price) as total
            FROM orderdetails od
            INNER JOIN products p ON od.product_id = p.product_id
            WHERE od.order_id = ?
        ");
        
        if (!$stmt) {
            error_log("Calculate order total prepare failed: " . $this->db->error);
            return 0;
        }

        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result ? (float)$result['total'] : 0;
    }
}
?>