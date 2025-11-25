<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

$response = array();

// Check if user is admin
if (!is_admin()) {
    $response['status'] = 'error';
    $response['message'] = 'Admin access required';
    echo json_encode($response);
    exit();
}

// Validate input
if (!isset($_GET['artisan_id'])) {
    $response['status'] = 'error';
    $response['message'] = 'Artisan ID required';
    echo json_encode($response);
    exit();
}

$artisan_id = (int)$_GET['artisan_id'];

// Get artisan details
$artisan = get_artisan_by_id_ctr($artisan_id);

if ($artisan) {
    $response['status'] = 'success';
    $response['data'] = $artisan;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Artisan not found';
}

echo json_encode($response);
?>