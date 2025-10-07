<?php
/**
 * Login Action Handler - AJAX Version
 * Returns JSON responses instead of redirects
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/customer_controller.php';

$response = array();

// Check if already logged in
if (isset($_SESSION['customer_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    $response['redirect'] = '../index.php';
    echo json_encode($response);
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit();
}

// Get and validate input
$email = trim($_POST['customer_email'] ?? '');
$password = $_POST['customer_pass'] ?? '';

// Validate required fields
if (empty($email) || empty($password)) {
    $response['status'] = 'error';
    $response['message'] = 'Email and password are required';
    echo json_encode($response);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['status'] = 'error';
    $response['message'] = 'Please enter a valid email address';
    echo json_encode($response);
    exit();
}

// Attempt login
try {
    $login_result = login_customer_ctr($email, $password);
    
    if ($login_result['success']) {
        // Set session variables
        $_SESSION['customer_id'] = $login_result['customer']['customer_id'];
        $_SESSION['customer_name'] = $login_result['customer']['customer_name'];
        $_SESSION['customer_email'] = $login_result['customer']['customer_email'];
        $_SESSION['user_role'] = $login_result['customer']['user_role'];
        $_SESSION['customer_country'] = $login_result['customer']['customer_country'];
        $_SESSION['customer_city'] = $login_result['customer']['customer_city'];
        $_SESSION['customer_contact'] = $login_result['customer']['customer_contact'];
        $_SESSION['login_time'] = time();
        
        // Log successful login
        error_log("Successful login for user: " . $email);
        
        // Determine redirect URL based on user role
        $redirect_url = '../index.php';
        if (isset($login_result['customer']['user_role']) && $login_result['customer']['user_role'] == 1) {
            // Admin user - could redirect to admin dashboard if you have one
            $redirect_url = '../index.php'; // or '../admin/dashboard.php' if exists
        }
        
        // Return success response
        $response['status'] = 'success';
        $response['message'] = 'Login successful! Redirecting...';
        $response['redirect'] = $redirect_url;
        $response['user'] = [
            'id' => $login_result['customer']['customer_id'],
            'name' => $login_result['customer']['customer_name'],
            'email' => $login_result['customer']['customer_email'],
            'role' => $login_result['customer']['user_role'],
            'is_admin' => ($login_result['customer']['user_role'] == 1)
        ];
        
        echo json_encode($response);
        exit();
        
    } else {
        // Log failed attempt
        error_log("Failed login attempt for email: " . $email . " - " . $login_result['message']);
        
        // Return error response
        $response['status'] = 'error';
        $response['message'] = $login_result['message'];
        echo json_encode($response);
        exit();
    }
    
} catch (Exception $e) {
    error_log("Login exception: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = 'A system error occurred. Please try again later.';
    echo json_encode($response);
    exit();
}
?>