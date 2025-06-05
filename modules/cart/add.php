<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any unwanted output
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Log function for debugging
function debug_log($message) {
    error_log(date('Y-m-d H:i:s') . " - Cart Debug: " . $message);
}

try {
    debug_log("Cart add request started");
    
    // Check if the functions file exists
    $functions_file =  'functions.php';
    debug_log("Looking for functions file at: " . $functions_file);
    
    if (!file_exists($functions_file)) {
        debug_log("Functions file not found");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Functions file not found']);
        exit;
    }
    
    // Include the functions file
    require_once $functions_file;
    debug_log("Functions file included successfully");
    
    // Check if session is started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        debug_log("Session not active, starting session");
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        debug_log("User not logged in");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }
    
    debug_log("User logged in with ID: " . $_SESSION['user_id']);
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        debug_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    // Get POST data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    debug_log("Product ID: $product_id, Quantity: $quantity");
    
    if (!$product_id || $quantity < 1) {
        debug_log("Invalid product ID or quantity");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        exit;
    }
    
    // Check if product exists
    debug_log("Checking if product exists");
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        debug_log("Database connection failed");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $query = "SELECT * FROM products WHERE id = :id AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        debug_log("Product not found or inactive");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    debug_log("Product found: " . $product['name']);
    
    // Check stock
    if ($product['stock_quantity'] < $quantity) {
        debug_log("Insufficient stock. Available: " . $product['stock_quantity'] . ", Requested: " . $quantity);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }
    
    // Check if item already exists in cart
    $query = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Update existing cart item
        debug_log("Updating existing cart item");
        $query = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':product_id', $product_id);
        $result = $stmt->execute();
    } else {
        // Insert new cart item
        debug_log("Adding new cart item");
        $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $result = $stmt->execute();
    }
    
    if ($result) {
        debug_log("Cart operation successful");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
    } else {
        debug_log("Cart operation failed");
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
    }
    
} catch (Exception $e) {
    debug_log("Exception occurred: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    debug_log("Fatal error occurred: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}

// End output buffering
ob_end_flush();
?>