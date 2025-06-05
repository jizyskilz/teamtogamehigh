<?php
// Completely prevent any HTML output
ini_set('display_errors', 0);
error_reporting(0);

// Clear any existing output
while (ob_get_level()) {
    ob_end_clean();
}

// Start output buffering
ob_start();

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache');

// Simple function to output JSON and exit
function outputJson($data) {
    ob_clean();
    echo json_encode($data);
    ob_end_flush();
    exit;
}

// Log errors to file instead of displaying
function logError($msg) {
    error_log(date('Y-m-d H:i:s') . " Cart Remove: " . $msg);
}

try {
    logError("Starting remove request");
    
    // Check if we can include functions
    $functionsFile = 'functions.php';
    if (!file_exists($functionsFile)) {
        logError("Functions file missing: " . $functionsFile);
        outputJson(['success' => false, 'message' => 'System error']);
    }
    
    require_once $functionsFile;
    
    // Start session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Check login
    if (!isset($_SESSION['user_id'])) {
        logError("User not logged in");
        outputJson(['success' => false, 'message' => 'Please login first']);
    }
    
    // Check method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError("Wrong method: " . $_SERVER['REQUEST_METHOD']);
        outputJson(['success' => false, 'message' => 'Invalid request']);
    }
    
    // Get data
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    
    logError("Cart ID: $cart_id");
    
    // Validate
    if ($cart_id <= 0) {
        logError("Invalid cart ID");
        outputJson(['success' => false, 'message' => 'Invalid item']);
    }
    
    // Database
    $database = new Database();
    $db = $database->getConnection();
    
    // Remove item
    $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$cart_id, $_SESSION['user_id']]) && $stmt->rowCount() > 0) {
        logError("Remove successful");
        outputJson(['success' => true, 'message' => 'Removed']);
    } else {
        logError("Remove failed");
        outputJson(['success' => false, 'message' => 'Remove failed']);
    }
    
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    outputJson(['success' => false, 'message' => 'Error occurred']);
}

// Should never reach here
outputJson(['success' => false, 'message' => 'Unknown error']);
?>
