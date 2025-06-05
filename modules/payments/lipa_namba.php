<?php
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

$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

if (!$amount || !$phone || !$order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// LIPA NAMBA API integration
$api_key = LIPA_NAMBA_API_KEY;
$secret = LIPA_NAMBA_SECRET;

// Prepare payment data
$payment_data = array(
    'amount' => $amount,
    'phone_number' => $phone,
    'order_id' => $order_id,
    'currency' => 'TZS',
    'callback_url' => SITE_URL . '/modules/payments/lipa_namba_callback.php'
);

// Make API call to LIPA NAMBA
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.lipanamba.com/v1/payments');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $result = json_decode($response, true);
    
    if ($result && isset($result['success']) && $result['success']) {
        // Update order with transaction ID
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE orders SET transaction_id = :transaction_id, payment_status = 'pending' 
                  WHERE id = :order_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':transaction_id', $result['transaction_id']);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Payment initiated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment initiation failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Payment service unavailable']);
}
?>