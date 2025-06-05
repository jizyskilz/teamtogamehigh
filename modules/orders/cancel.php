<?php
session_start();
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Verify order belongs to user
$order = get_order_by_id($order_id);
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Check if order can be cancelled
if (!in_array($order['status'], ['pending', 'payment_pending'])) {
    echo json_encode(['success' => false, 'message' => 'This order cannot be cancelled']);
    exit;
}

// Cancel the order
if (update_order_status($order_id, 'cancelled')) {
    // Restore product stock
    $order_items = get_order_items($order_id);
    foreach ($order_items as $item) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$item['quantity'], $item['product_id']]);
        } catch (Exception $e) {
            error_log("Error restoring stock for product " . $item['product_id'] . ": " . $e->getMessage());
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
}
?>
