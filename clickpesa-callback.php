<?php
// ClickPesa payment callback handler (simplified without checksum verification)
header('Content-Type: application/json');

// Log all incoming data for debugging
error_log("ClickPesa Callback received: " . file_get_contents('php://input'));

try {
    // Get callback data
    $input = file_get_contents('php://input');
    $callback_data = json_decode($input, true);
    
    if (!$callback_data) {
        // Try to get data from POST if JSON parsing fails
        $callback_data = $_POST;
    }
    
    if (empty($callback_data)) {
        throw new Exception('No callback data received');
    }
    
    // Include necessary functions
    require_once 'includes/functions.php';
    
    // Extract order ID from reference
    $order_reference = $callback_data['orderReference'] ?? $callback_data['order_reference'] ?? '';
    if (preg_match('/ORDER_(\d+)_/', $order_reference, $matches)) {
        $order_id = (int)$matches[1];
    } else {
        throw new Exception('Invalid order reference: ' . $order_reference);
    }
    
    // Get order details
    $order = get_order_by_id($order_id);
    if (!$order) {
        throw new Exception('Order not found: ' . $order_id);
    }
    
    // Process payment status
    $payment_status = $callback_data['status'] ?? $callback_data['payment_status'] ?? '';
    $transaction_id = $callback_data['transactionId'] ?? $callback_data['transaction_id'] ?? '';
    $amount = $callback_data['amount'] ?? '';
    
    // Log the callback
    log_payment_attempt($order_id, 'clickpesa', $order_reference, 'callback_received', $input);
    
    switch (strtolower($payment_status)) {
        case 'success':
        case 'successful':
        case 'completed':
        case 'paid':
        case 'confirmed':
            // Payment successful
            update_order_status($order_id, 'paid');
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'payment_success', $input);
            
            // Optional: Add transaction ID to order
            if ($transaction_id) {
                try {
                    $database = new Database();
                    $db = $database->getConnection();
                    $query = "UPDATE orders SET transaction_id = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$transaction_id, $order_id]);
                } catch (Exception $e) {
                    error_log("Failed to update transaction ID: " . $e->getMessage());
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Payment confirmed']);
            break;
            
        case 'failed':
        case 'failure':
        case 'cancelled':
        case 'canceled':
        case 'expired':
        case 'declined':
            // Payment failed
            update_order_status($order_id, 'payment_failed');
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'payment_failed', $input);
            
            echo json_encode(['status' => 'failed', 'message' => 'Payment failed']);
            break;
            
        case 'pending':
        case 'processing':
        case 'initiated':
            // Payment still pending
            update_order_status($order_id, 'payment_pending');
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'payment_pending', $input);
            
            echo json_encode(['status' => 'pending', 'message' => 'Payment pending']);
            break;
            
        default:
            // Unknown status - log it but don't fail
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'unknown_status', "Unknown status: $payment_status - " . $input);
            echo json_encode(['status' => 'received', 'message' => 'Status received: ' . $payment_status]);
    }
    
} catch (Exception $e) {
    error_log("ClickPesa Callback Error: " . $e->getMessage());
    
    // Log the error
    if (isset($order_id)) {
        log_payment_attempt($order_id, 'clickpesa', $order_reference ?? '', 'callback_error', $e->getMessage());
    }
    
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
