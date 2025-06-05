<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

try {
    // Include functions
    require_once __DIR__ . '/../../includes/functions.php';
    
    // Check session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        echo json_encode(['count' => 0]);
        exit;
    }
    
    // Get cart count
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $count = $result['count'] ? (int)$result['count'] : 0;
    
    ob_clean();
    echo json_encode(['count' => $count]);
    
} catch (Exception $e) {
    error_log("Cart count error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['count' => 0]);
}

ob_end_flush();
?>
