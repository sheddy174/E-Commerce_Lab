<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
require_once '../classes/customer_class.php';

/**
 * Customer Controller - Coordinates between Views and Models in MVC architecture
 */

/**
 * Register a new customer
 */
function register_customer_ctr($name, $email, $password, $country, $city, $contact, $role = 2)
{
    try {
        $customer = new Customer();
        $result = $customer->addCustomer($name, $email, $password, $country, $city, $contact, $role);
        
        error_log("Registration attempt for email: " . $email . " - Result: " . ($result ? 'Success' : 'Failed'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Registration exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Login customer with email and password
 */
function login_customer_ctr($email, $password)
{
    try {
        $customer = new Customer();
        
        error_log("Controller: Attempting login for email: " . $email);
        
        $login_result = $customer->validateLogin($email, $password);
        
        error_log("Controller: Validation result - Success: " . ($login_result['success'] ? 'true' : 'false'));
        
        if ($login_result['success']) {
            $update_result = $customer->updateLastLogin($login_result['customer']['customer_id']);
            error_log("Controller: Last login update result: " . ($update_result ? 'Success' : 'Failed'));
        }
        
        return $login_result;
        
    } catch (Exception $e) {
        error_log("Login controller exception: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'System error occurred during login',
            'customer' => null
        ];
    }
}

/**
 * Get customer by email address
 */
function get_customer_by_email_ctr($email)
{
    try {
        $customer = new Customer();
        return $customer->getCustomerByEmail($email);
    } catch (Exception $e) {
        error_log("Get customer by email exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if email already exists
 */
function check_email_exists_ctr($email)
{
    try {
        $customer = new Customer();
        $exists = $customer->emailExists($email);
        
        error_log("Email check for " . $email . ": " . ($exists ? 'EXISTS' : 'AVAILABLE'));
        
        return $exists;
    } catch (Exception $e) {
        error_log("Email check exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Edit customer information
 */
function edit_customer_ctr($customer_id, $name, $email, $country, $city, $contact)
{
    try {
        $customer = new Customer();
        return $customer->editCustomer($customer_id, $name, $email, $country, $city, $contact);
    } catch (Exception $e) {
        error_log("Edit customer exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete customer
 */
function delete_customer_ctr($customer_id)
{
    try {
        $customer = new Customer();
        return $customer->deleteCustomer($customer_id);
    } catch (Exception $e) {
        error_log("Delete customer exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all customers
 */
function get_all_customers_ctr()
{
    try {
        $customer = new Customer();
        return $customer->getAllCustomers();
    } catch (Exception $e) {
        error_log("Get all customers exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Get customer by ID
 */
function get_customer_by_id_ctr($customer_id)
{
    try {
        return new Customer($customer_id);
    } catch (Exception $e) {
        error_log("Get customer by ID exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Test database connection (for debugging)
 */
function test_db_connection_ctr()
{
    try {
        $customer = new Customer();
        $connection = $customer->db_conn();
        
        if ($connection) {
            return [
                'success' => true,
                'message' => 'Database connection successful',
                'server_info' => mysqli_get_server_info($connection)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Database connection failed'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database connection exception: ' . $e->getMessage()
        ];
    }
}
?>
