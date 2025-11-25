<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

$response = array();

// Check if already logged in
if (isset($_SESSION['customer_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/customer_controller.php';
require_once '../controllers/artisan_controller.php';

// Validate all required fields
$required_fields = ['customer_name', 'customer_email', 'customer_pass', 'customer_country', 
                   'customer_city', 'customer_contact', 'shop_name', 'craft_specialty', 
                   'years_experience', 'workshop_location', 'bio'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $response['status'] = 'error';
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit();
    }
}

// Sanitize personal data
$full_name = trim($_POST['customer_name']);
$email = trim($_POST['customer_email']);
$password = $_POST['customer_pass'];
$country = trim($_POST['customer_country']);
$city = trim($_POST['customer_city']);
$contact_number = trim($_POST['customer_contact']);
$user_role = 3; // Artisan role

// Sanitize artisan data
$shop_name = trim($_POST['shop_name']);
$craft_specialty = trim($_POST['craft_specialty']);
$years_experience = intval($_POST['years_experience']);
$workshop_location = trim($_POST['workshop_location']);
$bio = trim($_POST['bio']);

// Validation
$errors = [];

// Validate name
if (strlen($full_name) < 2 || !preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
    $errors[] = 'Full name must be at least 2 characters';
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Validate password
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
}

// Validate shop name
if (strlen($shop_name) < 3) {
    $errors[] = 'Shop name must be at least 3 characters';
}

// Validate bio
if (strlen($bio) < 50) {
    $errors[] = 'Bio must be at least 50 characters';
}

// Return validation errors
if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = implode('. ', $errors);
    echo json_encode($response);
    exit();
}

// Check if email exists
if (check_email_exists_ctr($email)) {
    $response['status'] = 'error';
    $response['message'] = 'Email address is already registered';
    echo json_encode($response);
    exit();
}

// Register customer with artisan role
$customer_id = register_customer_ctr($full_name, $email, $password, $country, $city, $contact_number, $user_role);

if ($customer_id) {
    // Create artisan profile
    $artisan_data = [
        'customer_id' => $customer_id,
        'shop_name' => $shop_name,
        'craft_specialty' => $craft_specialty,
        'years_experience' => $years_experience,
        'workshop_location' => $workshop_location,
        'bio' => $bio
    ];
    
    $artisan_profile_created = create_artisan_profile_ctr($artisan_data);
    
    if ($artisan_profile_created) {
        $response['status'] = 'success';
        $response['message'] = 'Artisan registration successful! Your account is pending verification. Please login to continue.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Account created but profile setup failed. Please contact support.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Registration failed. Please try again.';
}

echo json_encode($response);
?>