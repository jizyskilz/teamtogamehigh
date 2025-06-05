<?php
// Simple error checking file
echo "<h2>Error Check Results</h2>";

// Check if constants are defined
echo "<h3>Constants Check:</h3>";
echo "CLICKPESA_API_URL defined: " . (defined('CLICKPESA_API_URL') ? 'YES' : 'NO') . "<br>";
echo "CLICKPESA_TOKEN defined: " . (defined('CLICKPESA_TOKEN') ? 'YES' : 'NO') . "<br>";
echo "CLICKPESA_SECRET defined: " . (defined('CLICKPESA_SECRET') ? 'YES' : 'NO') . "<br>";

// Check if functions exist
echo "<h3>Functions Check:</h3>";
echo "is_admin() exists: " . (function_exists('is_admin') ? 'YES' : 'NO') . "<br>";
echo "is_logged_in() exists: " . (function_exists('is_logged_in') ? 'YES' : 'NO') . "<br>";
echo "initiate_clickpesa_payment() exists: " . (function_exists('initiate_clickpesa_payment') ? 'YES' : 'NO') . "<br>";

// Check file includes
echo "<h3>File Includes Check:</h3>";
$files_to_check = [
    'includes/functions.php',
    'includes/clickpesa-functions.php',
    'includes/order-functions.php',
    'config/db.php'
];

foreach ($files_to_check as $file) {
    echo "$file exists: " . (file_exists($file) ? 'YES' : 'NO') . "<br>";
}

// Test session
session_start();
echo "<h3>Session Check:</h3>";
echo "Session started: YES<br>";
echo "User logged in: " . (isset($_SESSION['user_id']) ? 'YES (ID: ' . $_SESSION['user_id'] . ')' : 'NO') . "<br>";

// Include functions to test
try {
    require_once 'includes/functions.php';
    echo "<h3>Functions Loaded Successfully!</h3>";
} catch (Exception $e) {
    echo "<h3>Error Loading Functions:</h3>";
    echo $e->getMessage();
}
?>
