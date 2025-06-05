<?php
session_start();
require_once 'includes/functions.php'; // Ensure this path is correct relative to process-clickpesa.php

if (!is_logged_in()) {
    error_log("Unauthorized access to process-clickpesa.php: No user session");
    $_SESSION['error'] = "You must be logged in to process a payment.";
    redirect('checkout.php');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) {
    error_log("Invalid or missing order_id: " . print_r($_GET, true));
    $_SESSION['error'] = "Invalid order ID.";
    redirect('checkout.php');
}

// Database connection
$conn = new mysqli("127.0.0.1", "root", "", "gaming_controllers_tz");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    $_SESSION['error'] = "Database connection failed.";
    redirect('checkout.php');
}

// Fetch order details
$stmt = $conn->prepare("SELECT total_amount, phone, user_id, payment_status, order_status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    error_log("Order not found for order_id: $order_id, user_id: " . $_SESSION['user_id']);
    $_SESSION['error'] = "Order not found.";
    redirect('checkout.php');
}
if ($order['payment_status'] !== 'pending' || $order['order_status'] !== 'pending') {
    error_log("Order already processed: ID $order_id, Status: " . $order['payment_status'] . "/" . $order['order_status']);
    $_SESSION['error'] = "Order has already been processed.";
    redirect('checkout.php');
}

// ClickPesa API configuration (REPLACE WITH YOUR ACTUAL CREDENTIALS)
$api_key = 'SKYsudtmZyFHtplbQHfG9cewybahUxkyLiZks3jDyX'; // Obtain from ClickPesa dashboard
$api_url = 'https://api.clickpesa.com/third-parties/payments/initiate-ussd-push-request'; // Live API endpoint
$callback_url = 'http://localhost/portal/clickpesa-callback.php'; // Update for live environment

// Validate payment data
$amount = number_format($order['total_amount'], 2, '.', '');
$phone = $order['phone'];
if (!preg_match('/^255[67][0-9]{8}$/', $phone)) {
    error_log("Invalid phone number format: $phone");
    $_SESSION['error'] = "Invalid phone number format for ClickPesa.";
    redirect('checkout.php');
}

$payment_data = [
    'amount' => $amount,
    'currency' => 'TZS',
    'orderReference' => 'ORDER_' . $order_id . '_' . time(),
    'phoneNumber' => $phone,
    'callbackUrl' => $callback_url
];

error_log("ClickPesa USSD push request: " . json_encode($payment_data));

// Initialize cURL
$ch = curl_init($api_url);
if (!$ch) {
    error_log("cURL initialization failed");
    $_SESSION['error'] = "Payment processing failed. Please try again.";
    redirect('checkout.php');
}

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payment_data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY4Mzk3MzgxNTUxZmI3NDdhMjZjZDlhZSIsImFwcGxpY2F0aW9uX2NsaWVudF9pZCI6IklEYjI1Rjd0SzBjV2hQNWpKMkpDbVd0UGVRZzA5MVo1IiwidmVyaWZpZWQiOnRydWUsImFwaV9hY2Nlc3MiOnRydWUsInVzZXJOYW1lIjoiMDBiZGE1NGYtYTE4MS00OTI0LWIxOGItYjIyNjVhMGRmMzFiIiwiaWF0IjoxNzQ5MDc2MjgwLCJleHAiOjE3NDkwNzk4ODB9.S8uFJ19hct-jTR7URXN8FH6L5UWdgVS4TiykeqGSAMs ' . $api_key
    ],
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    error_log("ClickPesa API request failed: HTTP $http_code, Error: $error, Response: " . ($response ?: 'No response'));
    $_SESSION['error'] = "Payment initiation failed with HTTP $http_code. Please try again.";
    redirect('checkout.php');
}

$response_data = json_decode($response, true);
error_log("ClickPesa API response: " . $response);

// Check for successful response (adjust based on ClickPesa's API response structure)
if (!isset($response_data['status']) || $response_data['status'] !== 'success') {
    error_log("USSD push initiation failed: " . $response);
    $_SESSION['error'] = "Failed to initiate USSD push. Please try again.";
    redirect('checkout.php');
}

// Log payment attempt
$stmt = $conn->prepare("INSERT INTO payment_logs (order_id, payment_method, reference, status, response_data, created_at) VALUES (?, 'clickpesa', ?, 'initiated', ?, NOW())");
$reference = $payment_data['orderReference'];
$response_json = json_encode($response_data);
$stmt->bind_param("iss", $order_id, $reference, $response_json);
$stmt->execute();
$stmt->close();

$conn->close();

// Redirect to a success page or inform user of USSD push initiation
$_SESSION['success'] = "Payment request sent. Please check your phone for the USSD prompt to complete the payment.";
redirect('checkout.php');
?>