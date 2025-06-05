<?php
// ClickPesa API configuration (without checksum requirement)
if (!defined('CLICKPESA_API_URL')) {
    define('CLICKPESA_API_URL', 'https://api.clickpesa.com/third-parties/payments/initiate-ussd-push-request');
}

if (!defined('CLICKPESA_TOKEN')) {
    define('CLICKPESA_TOKEN', 'your_clickpesa_bearer_token_here'); // Replace with your actual token
}

function initiate_clickpesa_payment($order_id, $amount, $phone_number) {
    try {
        // Generate unique order reference
        $order_reference = 'ORDER_' . $order_id . '_' . time();
        
        // Prepare payment data (without checksum since it's not allowed)
        $payment_data = [
            'amount' => (string)$amount,
            'currency' => 'TZS',
            'orderReference' => $order_reference,
            'phoneNumber' => $phone_number
        ];
        
        // Log payment attempt
        log_payment_attempt($order_id, 'clickpesa', $order_reference, 'initiated', json_encode($payment_data));
        
        // For testing purposes, check if we have real credentials
        if (CLICKPESA_TOKEN === 'your_clickpesa_bearer_token_here') {
            error_log("ClickPesa: Using test mode - payment would be initiated for order $order_id");
            
            // Simulate successful response for testing
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'test_success', 'Test mode response');
            return [
                'success' => true, 
                'message' => 'Payment request sent successfully (TEST MODE)',
                'reference' => $order_reference
            ];
        }
        
        // Initialize cURL for real API call
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => CLICKPESA_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payment_data),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . CLICKPESA_TOKEN,
                "Content-Type: application/json"
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            error_log("ClickPesa cURL Error: " . $err);
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'curl_error', $err);
            return [
                'success' => false, 
                'message' => 'Payment service temporarily unavailable. Please try again.'
            ];
        }
        
        // Parse response
        $response_data = json_decode($response, true);
        
        // Log the response
        log_payment_attempt($order_id, 'clickpesa', $order_reference, 'response_received', $response);
        
        // Check if request was successful
        if ($http_code === 200 || $http_code === 201) {
            // Check for success indicators in response
            if (isset($response_data['status'])) {
                $status = strtolower($response_data['status']);
                if (in_array($status, ['success', 'pending', 'initiated', 'sent'])) {
                    log_payment_attempt($order_id, 'clickpesa', $order_reference, 'ussd_sent', $response);
                    return [
                        'success' => true, 
                        'message' => 'Payment request sent to your phone. Please check your mobile money menu.',
                        'reference' => $order_reference,
                        'response' => $response_data
                    ];
                }
            } else if (isset($response_data['message']) && stripos($response_data['message'], 'success') !== false) {
                // Some APIs return success in message field
                log_payment_attempt($order_id, 'clickpesa', $order_reference, 'ussd_sent', $response);
                return [
                    'success' => true, 
                    'message' => 'Payment request sent to your phone. Please check your mobile money menu.',
                    'reference' => $order_reference,
                    'response' => $response_data
                ];
            } else if (empty($response_data) || !isset($response_data['error'])) {
                // If no error field, assume success
                log_payment_attempt($order_id, 'clickpesa', $order_reference, 'ussd_sent', $response);
                return [
                    'success' => true, 
                    'message' => 'Payment request sent to your phone. Please check your mobile money menu.',
                    'reference' => $order_reference
                ];
            }
            
            // Handle API errors
            $error_message = isset($response_data['message']) ? $response_data['message'] : 
                           (isset($response_data['error']) ? $response_data['error'] : 'Payment initiation failed');
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'api_error', $response);
            return [
                'success' => false, 
                'message' => $error_message
            ];
        } else {
            error_log("ClickPesa API HTTP Error: $http_code - $response");
            log_payment_attempt($order_id, 'clickpesa', $order_reference, 'http_error', "HTTP $http_code: $response");
            
            // Try to extract error message from response
            if ($response_data && isset($response_data['message'])) {
                $error_message = $response_data['message'];
            } else if ($response_data && isset($response_data['error'])) {
                $error_message = $response_data['error'];
            } else {
                $error_message = 'Payment service error. Please try again or use Cash on Delivery.';
            }
            
            return [
                'success' => false, 
                'message' => $error_message
            ];
        }
        
    } catch (Exception $e) {
        error_log("ClickPesa Exception: " . $e->getMessage());
        log_payment_attempt($order_id, 'clickpesa', '', 'exception', $e->getMessage());
        return [
            'success' => false, 
            'message' => 'Payment service error. Please try again.'
        ];
    }
}

function log_payment_attempt($order_id, $method, $reference, $status, $response_data = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO payment_logs (order_id, payment_method, reference, status, response_data, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id, $method, $reference, $status, $response_data]);
    } catch (Exception $e) {
        error_log("Payment log error: " . $e->getMessage());
    }
}

function get_payment_logs($order_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM payment_logs WHERE order_id = ? ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get payment logs error: " . $e->getMessage());
        return [];
    }
}

function check_payment_status($order_reference) {
    // Function to check payment status if ClickPesa provides a status check endpoint
    // This would need to be implemented based on ClickPesa's API documentation
    try {
        // If ClickPesa has a status check endpoint, implement it here
        // For now, we'll rely on callbacks or manual status updates
        return ['status' => 'pending', 'message' => 'Status check not implemented'];
    } catch (Exception $e) {
        error_log("Payment status check error: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Status check failed'];
    }
}
?>
