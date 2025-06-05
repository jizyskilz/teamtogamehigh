<?php
require_once '../config/db.php';


function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function get_products($limit = null, $category_id = null, $search = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active'";
    
    $params = array();
    
    if ($category_id) {
        $query .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    if ($search) {
        $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT :limit";
        $params[':limit'] = $limit;
    }
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        if ($key === ':limit') {
            $stmt->bindParam($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $value);
        }
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_product_by_id($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_categories() {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM categories ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_to_cart($user_id, $product_id, $quantity) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if item already exists in cart
    $query = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Update quantity
        $query = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id";
    } else {
        // Insert new item
        $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':quantity', $quantity);
    
    return $stmt->execute();
}

function get_cart_items($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT c.*, p.name, p.price, p.image FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_cart_count($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ? $result['count'] : 0;
}

function calculate_cart_total($user_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT SUM(c.quantity * p.price) as total FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ? $result['total'] : 0;
}

function create_order($user_id, $total_amount, $payment_method, $shipping_address, $phone) {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Create order
        $query = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, phone) 
                  VALUES (:user_id, :total_amount, :payment_method, :shipping_address, :phone)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':shipping_address', $shipping_address);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        
        $order_id = $db->lastInsertId();
        
        // Get cart items
        $cart_items = get_cart_items($user_id);
        
        // Create order items
        foreach ($cart_items as $item) {
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES (:order_id, :product_id, :quantity, :price)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
        }
        
        // Clear cart
        $query = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $db->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

function format_currency($amount) {
    return 'TSh ' . number_format($amount, 0, '.', ',');
}

function upload_image($file, $target_dir = 'assets/uploads/') {
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $uploadOk = 0;
    }
    
    // Generate unique filename
    $unique_name = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_name;
    
    if ($uploadOk == 1) {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $unique_name;
        }
    }
    
    return false;
}

function get_admin_stats() {
    $database = new Database();
    $db = $database->getConnection();
    
    $stats = array();
    
    // Total products
    $query = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total orders
    $query = "SELECT COUNT(*) as count FROM orders";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total users
    $query = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total revenue
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_revenue'] = $result['total'] ? $result['total'] : 0;
    
    return $stats;
}
?>