<?php
function create_order($user_id, $total_amount, $payment_method, $shipping_address, $phone) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $db->beginTransaction();
        
        $query = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, phone, created_at, updated_at, status, payment_status, order_status) 
                  VALUES (:user_id, :total_amount, :payment_method, :shipping_address, :phone, NOW(), NOW(), 'pending', 'pending', 'pending')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':total_amount' => $total_amount,
            ':payment_method' => $payment_method,
            ':shipping_address' => $shipping_address,
            ':phone' => $phone
        ]);
        $order_id = $db->lastInsertId();
        
        $cart_items = get_cart_items($user_id);
        if (empty($cart_items)) {
            throw new Exception("No cart items found for user ID: $user_id");
        }
        
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $db->prepare($query);
        foreach ($cart_items as $item) {
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $item['product_id'],
                ':quantity' => $item['quantity'],
                ':price' => $item['price']
            ]);
        }
        
        $db->commit();
        error_log("Order created successfully with ID: $order_id at " . date('Y-m-d H:i:s'));
        return $order_id;
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return false;
    }
}

function clear_user_cart($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([':user_id' => $user_id]);
        error_log("Cart cleared for user ID: $user_id at " . date('Y-m-d H:i:s'));
        return $result;
    } catch (Exception $e) {
        error_log("Clear cart failed: " . $e->getMessage());
        return false;
    }
}

function get_order_by_id($order_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT o.*, u.full_name as customer_name 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE o.id = :order_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':order_id' => $order_id]);
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            error_log("Order not found for ID: $order_id at " . date('Y-m-d H:i:s'));
            return false;
        }
        
        // Fetch order items
        $query = "SELECT oi.*, p.name as product_name 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = :order_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':order_id' => $order_id]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $order;
    } catch (Exception $e) {
        error_log("Get order by ID error: " . $e->getMessage());
        return false;
    }
}
?>