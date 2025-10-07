<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
/**
 * Logout Action Handler
 * Handles user logout requests
 */

session_start();

// Log the logout
if (isset($_SESSION['customer_email'])) {
    error_log("User logout: " . $_SESSION['customer_email'] . " at " . date('Y-m-d H:i:s'));
}

// Clear all session data
$_SESSION = array();

// Delete session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to index with logout message
header("Location: ../index.php?logout=success");
exit();
?>