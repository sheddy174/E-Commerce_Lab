<?php
session_start();
$_SESSION['customer_id'] = 2; // Your test customer ID
$_SESSION['user_role'] = 2;

require_once '../controllers/cart_controller.php';

// Test adding to cart
$result = add_to_cart_ctr(3, 2, '127.0.0.1', 1);
echo "Add to cart: " . ($result ? "SUCCESS" : "FAILED") . "<br>";

// Test getting cart
$cart = get_user_cart_ctr(2);
echo "Cart items: " . count($cart) . "<br>";
print_r($cart);
?>