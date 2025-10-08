<?php
//Database credentials
// Settings/db_cred.php

// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'dbforlab');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined("SERVER")) {
    define("SERVER", "localhost");
}

if (!defined("USERNAME")) {
    define("USERNAME", "shadrack.berdah");
}

if (!defined("PASSWD")) {
    define("PASSWD", '$h@dr@ck123');
}

if (!defined("DATABASE")) {
    // Use the database name from the provided SQL dump
    define("DATABASE", "ecommerce_2025A_shadrack_berdah");  //ecommerce_2025A_shadrack_berdah
}
?>