<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
header('Content-Type: application/json');
session_start();

$response = array();

// Check if the user is already logged in
if (isset($_SESSION['customer_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/customer_controller.php';

// Validate POST data exists
if (!isset($_POST['customer_name']) || !isset($_POST['customer_email']) || !isset($_POST['customer_pass']) || 
    !isset($_POST['customer_country']) || !isset($_POST['customer_city']) || !isset($_POST['customer_contact'])) {
    $response['status'] = 'error';
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit();
}

// Sanitize input data
$full_name = trim($_POST['customer_name']);
$email = trim($_POST['customer_email']);
$password = $_POST['customer_pass'];
$country = trim($_POST['customer_country']);
$city = trim($_POST['customer_city']);
$contact_number = trim($_POST['customer_contact']);
$user_role = 2; // Default to customer role

// Server-side validation
$errors = [];

// Validate full name (minimum 2 characters, only letters and spaces)
if (strlen($full_name) < 2 || !preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
    $errors[] = 'Full name must be at least 2 characters and contain only letters and spaces';
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Validate password strength
if (strlen($password) < 8 || !preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/", $password)) {
    $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, number and special character';
}

// Validate country (minimum 2 characters, only letters and spaces)
if (strlen($country) < 2 || !preg_match("/^[a-zA-Z\s]+$/", $country)) {
    $errors[] = 'Country must be at least 2 characters and contain only letters and spaces';
}

// Validate city (minimum 2 characters, only letters and spaces)
if (strlen($city) < 2 || !preg_match("/^[a-zA-Z\s]+$/", $city)) {
    $errors[] = 'City must be at least 2 characters and contain only letters and spaces';
}

// Validate contact number (10-15 digits, may include country code)
if (!preg_match("/^\+?[1-9]\d{1,14}$/", $contact_number)) {
    $errors[] = 'Contact number must be between 10-15 digits';
}

// Return validation errors if any
if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = implode('. ', $errors);
    echo json_encode($response);
    exit();
}

// Check if email already exists
if (check_email_exists_ctr($email)) {
    $response['status'] = 'error';
    $response['message'] = 'Email address is already registered';
    echo json_encode($response);
    exit();
}

// Attempt to register customer
$customer_id = register_customer_ctr($full_name, $email, $password, $country, $city, $contact_number, $user_role);

if ($customer_id) {
    $response['status'] = 'success';
    $response['message'] = 'Registration successful! Please login to continue.';
    $response['customer_id'] = $customer_id;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Registration failed. Please try again.';
}

echo json_encode($response);
?>