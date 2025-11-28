<?php
// Include the database connection file
require_once(__DIR__ . '/../settings/db_class.php');

/**
 * Order Class - handles all order-related database operations
 * This class extends the official database connection class
 * COMPLETE VERSION - Includes all methods + Paystack support + Delivery Tracking
 */
class order_class extends db_connection
{

    /**
     * Create a new order - UPDATED with payment and delivery status
     * @param int $customer_id - Customer ID
     * @param string $invoice_no - Unique invoice number
     * @param string $order_date - Order date (YYYY-MM-DD)
     * @param string $order_status - Order status (for backward compatibility)
     * @param string $payment_status - Payment status: pending, completed, failed
     * @param string $delivery_status - Delivery status: pending, processing, shipped, delivered, cancelled
     * @return int|false - Returns order_id if successful, false if failed
     */
    public function create_order(
        $customer_id,
        $invoice_no,
        $order_date,
        $order_status = 'Pending',
        $payment_status = 'pending',
        $delivery_status = 'pending'
    ) {
        error_log("=== CREATE_ORDER METHOD CALLED ===");
        try {
            $conn = $this->db_conn();

            if (!$conn) {
                error_log("Failed to get database connection");
                return false;
            }

            $customer_id = (int)$customer_id;
            $invoice_no = mysqli_real_escape_string($conn, $invoice_no);
            $order_date = mysqli_real_escape_string($conn, $order_date);
            $order_status = mysqli_real_escape_string($conn, $order_status);
            $payment_status = mysqli_real_escape_string($conn, $payment_status);
            $delivery_status = mysqli_real_escape_string($conn, $delivery_status);

            $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status, payment_status, order_delivery_status) 
                VALUES ($customer_id, '$invoice_no', '$order_date', '$order_status', '$payment_status', '$delivery_status')";

            error_log("Executing SQL: $sql");

            $result = mysqli_query($conn, $sql);

            if ($result) {
                $order_id = mysqli_insert_id($conn);
                error_log("Order created successfully with ID: $order_id");

                if ($order_id > 0) {
                    return $order_id;
                } else {
                    error_log("Insert succeeded but ID is 0");
                    return false;
                }
            } else {
                $error = mysqli_error($conn);
                error_log("Order creation failed. MySQL error: " . $error);
                return false;
            }
        } catch (Exception $e) {
            error_log("Exception in create_order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add order details (products) to an order
     * @param int $order_id - Order ID
     * @param int $product_id - Product ID
     * @param int $qty - Quantity ordered
     * @return bool - Returns true if successful, false if failed
     */
    public function add_order_details($order_id, $product_id, $qty)
    {
        try {
            $order_id = (int)$order_id;
            $product_id = (int)$product_id;
            $qty = (int)$qty;

            $sql = "INSERT INTO orderdetails (order_id, product_id, qty) 
                    VALUES ($order_id, $product_id, $qty)";

            error_log("Adding order detail - Order: $order_id, Product: $product_id, Qty: $qty");

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error adding order details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record a payment for an order WITH PAYSTACK SUPPORT
     * 
     * @param float $amount - Payment amount
     * @param int $customer_id - Customer ID
     * @param int $order_id - Order ID
     * @param string $currency - Currency code (e.g., 'GHS', 'USD')
     * @param string $payment_date - Payment date (YYYY-MM-DD)
     * @param string $payment_method - Payment method (e.g., 'paystack', 'cash', 'bank_transfer')
     * @param string $transaction_ref - Transaction reference/ID from payment gateway
     * @param string $authorization_code - Authorization code from payment gateway
     * @param string $payment_channel - Payment channel (e.g., 'card', 'mobile_money')
     * @return int|false - Returns payment_id if successful, false if failed
     */
    public function record_payment(
        $amount,
        $customer_id,
        $order_id,
        $currency,
        $payment_date,
        $payment_method = 'direct',
        $transaction_ref = null,
        $authorization_code = null,
        $payment_channel = null
    ) {
        error_log("=== RECORD_PAYMENT METHOD CALLED ===");
        try {
            $conn = $this->db_conn();

            $amount = (float)$amount;
            $customer_id = (int)$customer_id;
            $order_id = (int)$order_id;
            $currency = mysqli_real_escape_string($conn, $currency);
            $payment_date = mysqli_real_escape_string($conn, $payment_date);
            $payment_method = mysqli_real_escape_string($conn, $payment_method);

            // Build SQL with optional fields
            $columns = "amt, customer_id, order_id, currency, payment_date, payment_method";
            $values = "$amount, $customer_id, $order_id, '$currency', '$payment_date', '$payment_method'";

            if ($transaction_ref) {
                $transaction_ref = mysqli_real_escape_string($conn, $transaction_ref);
                $columns .= ", transaction_ref";
                $values .= ", '$transaction_ref'";
            }

            if ($authorization_code) {
                $authorization_code = mysqli_real_escape_string($conn, $authorization_code);
                $columns .= ", authorization_code";
                $values .= ", '$authorization_code'";
            }

            if ($payment_channel) {
                $payment_channel = mysqli_real_escape_string($conn, $payment_channel);
                $columns .= ", payment_channel";
                $values .= ", '$payment_channel'";
            }

            $sql = "INSERT INTO payment ($columns) VALUES ($values)";

            error_log("Executing SQL: $sql");

            if ($this->db_write_query($sql)) {
                // Get the last insert ID immediately
                $payment_id = mysqli_insert_id($conn);
                error_log("Payment recorded successfully with ID: $payment_id");
                return $payment_id;
            } else {
                $error = mysqli_error($conn);
                error_log("Payment recording failed. MySQL error: " . $error);
                return false;
            }
        } catch (Exception $e) {
            error_log("Error recording payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all orders for a user
     * @param int $customer_id - Customer ID
     * @return array|false - Returns array of orders or false if failed
     */
    public function get_user_orders($customer_id)
    {
        try {
            $customer_id = (int)$customer_id;

            $sql = "SELECT 
                        o.order_id,
                        o.invoice_no,
                        o.order_date,
                        o.order_status,
                        o.order_delivery_status,
                        o.payment_status,
                        o.tracking_number,
                        o.shipped_date,
                        o.delivered_date,
                        o.delivery_notes,
                        p.amt as total_amount,
                        p.currency,
                        p.payment_method,
                        p.transaction_ref,
                        COUNT(od.product_id) as item_count
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    LEFT JOIN orderdetails od ON o.order_id = od.order_id
                    WHERE o.customer_id = $customer_id
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC, o.order_id DESC";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get details of a specific order
     * @param int $order_id - Order ID
     * @param int $customer_id - Customer ID (for security check)
     * @return array|false - Returns order details or false if not found
     */
    public function get_order_details($order_id, $customer_id)
    {
        try {
            $order_id = (int)$order_id;
            $customer_id = (int)$customer_id;

            $sql = "SELECT 
                        o.order_id,
                        o.invoice_no,
                        o.order_date,
                        o.order_status,
                        o.order_delivery_status,
                        o.payment_status,
                        o.tracking_number,
                        o.shipped_date,
                        o.delivered_date,
                        o.delivery_notes,
                        o.customer_id,
                        p.amt as total_amount,
                        p.currency,
                        p.payment_date,
                        p.payment_method,
                        p.transaction_ref,
                        p.payment_channel
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE o.order_id = $order_id AND o.customer_id = $customer_id";

            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all products in a specific order
     * @param int $order_id - Order ID
     * @return array|false - Returns array of products in the order or false if failed
     */
    public function get_order_products($order_id)
    {
        try {
            $order_id = (int)$order_id;

            $sql = "SELECT 
                        od.product_id,
                        od.qty,
                        p.product_title,
                        p.product_price,
                        p.product_image,
                        (od.qty * p.product_price) as subtotal
                    FROM orderdetails od
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = $order_id";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting order products: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order status - UPDATED to support delivery tracking
     * @param int $order_id - Order ID
     * @param string $order_status - New order status
     * @param string|null $tracking_number - Optional tracking number
     * @param string|null $notes - Optional delivery notes
     * @return bool - Returns true if successful, false if failed
     */
    public function update_order_status($order_id, $order_status, $tracking_number = null, $notes = null)
    {
        try {
            $conn = $this->db_conn();
            $order_id = (int)$order_id;
            $order_status = mysqli_real_escape_string($conn, $order_status);
            
            // Build dynamic SQL
            $updates = ["order_delivery_status = '$order_status'"];
            
            // Set shipped_date when status changes to 'shipped'
            if ($order_status === 'shipped') {
                $updates[] = "shipped_date = NOW()";
            }
            
            // Set delivered_date when status changes to 'delivered'
            if ($order_status === 'delivered') {
                $updates[] = "delivered_date = NOW()";
            }
            
            // Add tracking number if provided
            if ($tracking_number !== null) {
                $tracking_number = mysqli_real_escape_string($conn, $tracking_number);
                $updates[] = "tracking_number = '$tracking_number'";
            }
            
            // Add notes if provided
            if ($notes !== null) {
                $notes = mysqli_real_escape_string($conn, $notes);
                $updates[] = "delivery_notes = '$notes'";
            }
            
            $sql = "UPDATE orders SET " . implode(", ", $updates) . " WHERE order_id = $order_id";
            
            error_log("Updating order: $sql");

            return $this->db_write_query($sql);
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique invoice number - CRITICAL FOR CHECKOUT!
     * @return string - Unique invoice number
     */
    public function generate_invoice_number()
    {
        try {
            // Get the highest invoice number
            $sql = "SELECT MAX(invoice_no) as max_invoice FROM orders";
            $result = $this->db_fetch_one($sql);

            $max_invoice = $result ? (int)$result['max_invoice'] : 0;

            // Generate new invoice number
            // Format: GTUNES-YYYYMMDD-XXXX (e.g., GTUNES-20251120-0001)
            $date_prefix = date('Ymd');
            $new_invoice = 'GTUNES-' . $date_prefix . '-' . str_pad(($max_invoice % 10000) + 1, 4, '0', STR_PAD_LEFT);

            error_log("Generated invoice number: $new_invoice");

            return $new_invoice;
        } catch (Exception $e) {
            error_log("Error generating invoice number: " . $e->getMessage());
            // Fallback to timestamp-based
            return 'GTUNES-' . time();
        }
    }

    /**
     * Get all orders (Admin function) - UPDATED with delivery info
     * @param int $limit - Limit number of results
     * @param int $offset - Offset for pagination
     * @param string|null $status - Filter by delivery status
     * @param string|null $search - Search by customer name/email
     * @return array|false - Returns array of orders or false if failed
     */
    public function get_all_orders($limit = 50, $offset = 0, $status = null, $search = null)
    {
        try {
            $limit = (int)$limit;
            $offset = (int)$offset;

            $sql = "SELECT 
                        o.order_id,
                        o.customer_id,
                        o.invoice_no,
                        o.order_date,
                        o.order_status,
                        o.order_delivery_status,
                        o.payment_status,
                        o.tracking_number,
                        o.shipped_date,
                        o.delivered_date,
                        c.customer_name,
                        c.customer_email,
                        COUNT(DISTINCT od.product_id) as total_items,
                        SUM(od.qty) as total_quantity,
                        p.amt as payment_amount,
                        p.currency as payment_currency,
                        p.payment_method
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    LEFT JOIN orderdetails od ON o.order_id = od.order_id
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE 1=1";
            
            // Add status filter if provided
            if ($status) {
                $status = mysqli_real_escape_string($this->db_conn(), $status);
                $sql .= " AND o.order_delivery_status = '$status'";
            }
            
            // Add search filter if provided
            if ($search) {
                $search = mysqli_real_escape_string($this->db_conn(), $search);
                $sql .= " AND (c.customer_name LIKE '%$search%' OR c.customer_email LIKE '%$search%')";
            }
            
            $sql .= " GROUP BY o.order_id
                    ORDER BY o.order_date DESC, o.order_id DESC
                    LIMIT $limit OFFSET $offset";

            return $this->db_fetch_all($sql);
        } catch (Exception $e) {
            error_log("Error getting all orders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate order total from order details
     * @param int $order_id - Order ID
     * @return float - Total amount
     */
    public function calculate_order_total($order_id)
    {
        try {
            $order_id = (int)$order_id;

            $sql = "SELECT SUM(od.qty * p.product_price) as total
                    FROM orderdetails od
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = $order_id";

            $result = $this->db_fetch_one($sql);

            return $result ? (float)$result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error calculating order total: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get customer orders (alias for get_user_orders for backward compatibility)
     * @param int $customer_id - Customer ID
     * @return array|false - Returns array of orders or false if failed
     */
    public function get_customer_orders($customer_id)
    {
        return $this->get_user_orders($customer_id);
    }

    /**
     * Get order by ID (without customer check - for admin)
     * @param int $order_id - Order ID
     * @return array|false - Returns order data or false if not found
     */
    public function get_order_by_id($order_id)
    {
        try {
            $order_id = (int)$order_id;

            $sql = "SELECT 
                        o.*,
                        c.customer_name,
                        c.customer_email,
                        c.customer_contact,
                        c.customer_country,
                        c.customer_city,
                        p.amt as payment_amount,
                        p.currency as payment_currency,
                        p.payment_date,
                        p.payment_method,
                        p.transaction_ref
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE o.order_id = $order_id";

            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error getting order by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order statistics
     * @return array Statistics
     */
    public function get_order_stats()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN order_delivery_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(CASE WHEN order_delivery_status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                        SUM(CASE WHEN order_delivery_status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                        SUM(CASE WHEN order_delivery_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                        SUM(CASE WHEN order_delivery_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                        SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) as today_orders
                    FROM orders";
            
            return $this->db_fetch_one($sql);
        } catch (Exception $e) {
            error_log("Error getting order stats: " . $e->getMessage());
            return [];
        }
    }
}
?>