<?php
header('Content-Type: application/json');



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../controllers/customer_controller.php';

$response = array();

if (!isset($_POST['customer_email'])) {
    $response['exists'] = false;
    $response['message'] = 'No email provided';
    echo json_encode($response);
    exit();
}

$email = trim($_POST['customer_email']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['exists'] = false;
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit();
}

// Check if email exists
$exists = check_email_exists_ctr($email);

$response['exists'] = $exists;
$response['message'] = $exists ? 'Email already exists' : 'Email is available';

echo json_encode($response);
