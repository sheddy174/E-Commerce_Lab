<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
require_once '../settings/db_class.php';

/**
 * Customer Model Class handles all customer-related database operations
 * Inherits from db_connection for database connectivity
 */
class Customer extends db_connection
{
    private $customer_id;
    private $customer_name;
    private $customer_email;
    private $customer_pass;
    private $customer_country;
    private $customer_city;
    private $customer_contact;
    private $customer_image;
    private $user_role;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct($customer_id = null)
    {
        parent::db_connect();
        if ($customer_id) {
            $this->customer_id = $customer_id;
            $this->loadCustomer();
        }
    }

    /**
     * Load customer data from database
     */
    private function loadCustomer($customer_id = null)
    {
        if ($customer_id) {
            $this->customer_id = $customer_id;
        }
        if (!$this->customer_id) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->customer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            $this->customer_name = $result['customer_name'];
            $this->customer_email = $result['customer_email'];
            $this->customer_country = $result['customer_country'];
            $this->customer_city = $result['customer_city'];
            $this->customer_contact = $result['customer_contact'];
            $this->customer_image = $result['customer_image'];
            $this->user_role = $result['user_role'];
            return true;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Create new customer
     */
    public function addCustomer($name, $email, $password, $country, $city, $contact, $role = 2)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, user_role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $country, $city, $contact, $role);
        
        if ($stmt->execute()) {
            $customer_id = $this->db->insert_id;
            $stmt->close();
            return $customer_id;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get customer by email for login validation process
     */
    public function getCustomerByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM customer WHERE customer_email = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }

    /**
     * Validate customer login credentials
     */
    public function validateLogin($email, $password)
    {
        $customer = $this->getCustomerByEmail($email);
        
        if (!$customer) {
            return [
                'success' => false,
                'message' => 'Invalid email or password',
                'customer' => null
            ];
        }

        if (password_verify($password, $customer['customer_pass'])) {
            unset($customer['customer_pass']);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'customer' => $customer
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid email or password',
                'customer' => null
            ];
        }
    }

    /**
     * Check if email exists
     */
    public function emailExists($email)
    {
        $stmt = $this->db->prepare("SELECT customer_id FROM customer WHERE customer_email = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }

    /**
     * Update last login time
     */
    public function updateLastLogin($customer_id)
    {
       return true;
    }

    /**
     * Edit customer information
     */
    public function editCustomer($customer_id, $name, $email, $country, $city, $contact)
    {
        $stmt = $this->db->prepare("UPDATE customer SET customer_name = ?, customer_email = ?, customer_country = ?, customer_city = ?, customer_contact = ? WHERE customer_id = ?");
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sssssi", $name, $email, $country, $city, $contact, $customer_id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Delete customer
     */
    public function deleteCustomer($customer_id)
    {
        $stmt = $this->db->prepare("DELETE FROM customer WHERE customer_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $customer_id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Get all customers
     */
    public function getAllCustomers()
    {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, user_role FROM customer ORDER BY customer_name ASC";
        return $this->db_fetch_all($sql);
    }

    /**
     * Update customer image
     */
    public function updateCustomerImage($customer_id, $image_path)
    {
        $stmt = $this->db->prepare("UPDATE customer SET customer_image = ? WHERE customer_id = ?");
        if (!$stmt) {
            error_log("UpdateCustomerImage prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("si", $image_path, $customer_id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
?>