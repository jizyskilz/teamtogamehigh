<?php
// Include all function files
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/order-functions.php';
require_once __DIR__ . '/clickpesa-functions.php';

// Session management
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    if (headers_sent()) {
        echo "<script>window.location.href = '$url';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
        exit;
    } else {
        header("Location: $url");
        exit;
    }
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// User functions
function get_user_by_id($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        return false;
    }
}

// Product functions
function get_products($limit = null, $category_id = null, $search = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'active'";
        $params = [];
        
        if ($category_id) {
            $query .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get products error: " . $e->getMessage());
        return [];
    }
}

function get_product_by_id($product_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get product error: " . $e->getMessage());
        return false;
    }
}

function get_categories() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM categories ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get categories error: " . $e->getMessage());
        return [];
    }
}

// Cart functions
function get_cart_items($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT c.id, c.quantity, c.product_id, p.name, p.price, p.image, p.stock_quantity
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ? AND p.status = 'active'
                  ORDER BY c.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get cart items error: " . $e->getMessage());
        return [];
    }
}

function get_cart_count($user_id = null) {
    try {
        if ($user_id === null) {
            if (!isset($_SESSION['user_id'])) {
                return 0;
            }
            $user_id = $_SESSION['user_id'];
        }
        
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ? (int)$result['count'] : 0;
    } catch (Exception $e) {
        error_log("Get cart count error: " . $e->getMessage());
        return 0;
    }
}

function calculate_cart_total($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT SUM(c.quantity * p.price) as total 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ? AND p.status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ? (float)$result['total'] : 0;
    } catch (Exception $e) {
        error_log("Calculate cart total error: " . $e->getMessage());
        return 0;
    }
}

function add_to_cart($user_id, $product_id, $quantity) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $new_quantity = $existing['quantity'] + $quantity;
            $query = "UPDATE cart SET quantity = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            return $stmt->execute([$new_quantity, $existing['id']]);
        } else {
            $query = "INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            return $stmt->execute([$user_id, $product_id, $quantity]);
        }
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        return false;
    }
}

// Utility functions
function format_currency($amount) {
    return 'TZS ' . number_format($amount, 0, '.', ',');
}

function format_date($date) {
    if (empty($date)) {
        return 'N/A';
    }
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('M j, Y g:i A');
    } catch (Exception $e) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('M j, Y g:i A', $timestamp);
        }
        error_log("Date formatting error for date: $date - " . $e->getMessage());
        return htmlspecialchars($date);
    }
}

function format_date_simple($date) {
    if (empty($date)) {
        return 'N/A';
    }
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format('M j, Y');
    } catch (Exception $e) {
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('M j, Y', $timestamp);
        }
        return htmlspecialchars($date);
    }
}

function time_ago($date) {
    if (empty($date)) {
        return 'Unknown';
    }
    try {
        $dateTime = new DateTime($date);
        $now = new DateTime();
        $interval = $now->diff($dateTime);
        if ($interval->days > 0) {
            return $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    } catch (Exception $e) {
        return 'Unknown';
    }
}

function get_current_page() {
    return basename($_SERVER['PHP_SELF'], '.php');
}

function is_current_page($page) {
    return get_current_page() === $page;
}
?>