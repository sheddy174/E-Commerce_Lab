<?php
/**
 * Login Action Handler - AJAX Version with Role-Based Redirection
 * Returns JSON responses with appropriate redirect URLs for each role
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
        // Set session variables using the helper function
        set_user_session($login_result['customer']);
        
        // Determine redirect URL based on user role
        $user_role = $login_result['customer']['user_role'];
        $redirect_url = '../index.php'; // Default for customers
        $role_name = 'Customer';
        
        if ($user_role == 1) {
            // Admin user
            $redirect_url = '../admin/dashboard.php';
            $role_name = 'Administrator';
        } elseif ($user_role == 3) {
            // Artisan user
            $redirect_url = '../artisan/dashboard.php';
            $role_name = 'Artisan';
        } elseif ($user_role == 2) {
            // Customer user
            $redirect_url = '../index.php';
            $role_name = 'Customer';
        }
        
        // Log successful login with role information
        error_log("Successful login - Email: {$email}, Role: {$role_name}, Redirect: {$redirect_url}");
        log_activity("User logged in successfully as {$role_name}", 'info');
        
        // Return success response with role-specific data
        $response['status'] = 'success';
        $response['message'] = 'Login successful! Redirecting...';
        $response['redirect'] = $redirect_url;
        $response['user'] = [
            'id' => $login_result['customer']['customer_id'],
            'name' => $login_result['customer']['customer_name'],
            'email' => $login_result['customer']['customer_email'],
            'role' => $user_role,
            'role_name' => $role_name,
            'is_admin' => ($user_role == 1),
            'is_artisan' => ($user_role == 3),
            'is_customer' => ($user_role == 2)
        ];
        
        echo json_encode($response);
        exit();
        
    } else {
        // Log failed attempt
        error_log("Failed login attempt - Email: {$email}, Reason: {$login_result['message']}");
        log_activity("Failed login attempt for email: {$email}", 'warning');
        
        // Return error response
        $response['status'] = 'error';
        $response['message'] = $login_result['message'];
        echo json_encode($response);
        exit();
    }
    
} catch (Exception $e) {
    error_log("Login exception: " . $e->getMessage());
    log_activity("Login system error: " . $e->getMessage(), 'error');
    
    $response['status'] = 'error';
    $response['message'] = 'A system error occurred. Please try again later.';
    echo json_encode($response);
    exit();
}
?>