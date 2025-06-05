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
    require_once  'functions.php';
    
    // Check session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    // Get cart items and calculate totals
    $database = new Database();
    $db = $database->getConnection();
    
    // Get cart items with product details
    $query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = 0;
    $items = [];
    
    foreach ($cart_items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
        
        $items[] = [
            'cart_id' => $item['cart_id'],
            'subtotal' => $subtotal,
            'formatted_subtotal' => format_currency($subtotal)
        ];
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'total' => $total,
        'formatted_total' => format_currency($total),
        'items' => $items
    ]);
    
} catch (Exception $e) {
    error_log("Cart totals error: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();
?>
