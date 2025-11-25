<?php
header('Content-Type: application/json');
session_start();

require_once '../settings/core.php';
require_once '../controllers/artisan_controller.php';

$response = array();

// Check if user is artisan
if (!is_artisan()) {
    $response['status'] = 'error';
    $response['message'] = 'Artisan access required';
    echo json_encode($response);
    exit();
}

// Validate input
$required_fields = ['artisan_id', 'shop_name', 'craft_specialty', 'years_experience', 'workshop_location', 'bio'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $response['status'] = 'error';
        $response['message'] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        echo json_encode($response);
        exit();
    }
}

$artisan_id = (int)$_POST['artisan_id'];
$data = [
    'shop_name' => trim($_POST['shop_name']),
    'craft_specialty' => trim($_POST['craft_specialty']),
    'years_experience' => (int)$_POST['years_experience'],
    'workshop_location' => trim($_POST['workshop_location']),
    'bio' => trim($_POST['bio'])
];

// Update profile
$result = update_artisan_profile_ctr($artisan_id, $data);

if ($result) {
    $response['status'] = 'success';
    $response['message'] = 'Profile updated successfully';
    log_activity("Artisan profile updated - ID: {$artisan_id}", 'info');
} else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to update profile';
}

echo json_encode($response);
?>