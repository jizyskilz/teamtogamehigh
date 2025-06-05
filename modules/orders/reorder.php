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

// Get order items
$order_items = get_order_items($order_id);

if (empty($order_items)) {
    echo json_encode(['success' => false, 'message' => 'No items found in this order']);
    exit;
}

$added_count = 0;
$errors = [];

foreach ($order_items as $item) {
    // Check if product is still available
    $product = get_product_by_id($item['product_id']);
    if ($product && $product['status'] === 'active' && $product['stock_quantity'] >= $item['quantity']) {
        if (add_to_cart($_SESSION['user_id'], $item['product_id'], $item['quantity'])) {
            $added_count++;
        } else {
            $errors[] = "Failed to add " . $item['name'] . " to cart";
        }
    } else {
        $errors[] = $item['name'] . " is no longer available or out of stock";
    }
}

if ($added_count > 0) {
    $message = "$added_count item(s) added to cart";
    if (!empty($errors)) {
        $message .= ". Some items couldn't be added: " . implode(', ', $errors);
    }
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'No items could be added to cart']);
}
?>
