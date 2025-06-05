<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_GET['order_id']) || !is_logged_in()) {
    redirect('checkout.php');
}

$order_id = (int)$_GET['order_id'];
$order = get_order_by_id($order_id);

if (!$order) {
    $_SESSION['error'] = 'Order not found or access denied.';
    redirect('checkout.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container { margin-top: 2rem; max-width: 800px; margin-left: auto; margin-right: auto; }
        .card { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .success-message { background: #2ed573; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .order-details { margin-top: 1rem; }
        .order-item { display: flex; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #eee; }
        .order-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Thank you! Your order has been placed successfully.
            </div>
            
            <h2>Order Confirmation #<?php echo htmlspecialchars($order_id); ?></h2>
            <div class="order-details">
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Total Amount:</strong> <?php echo format_currency($order['total_amount']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                <p><strong>Order Date:</strong> <?php echo format_date($order['created_at']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                
                <h3>Order Items</h3>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo $item['image'] ? 'assets/uploads/' . htmlspecialchars($item['image']) : 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <div>
                            <div><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></div>
                            <div>Qty: <?php echo htmlspecialchars($item['quantity']); ?></div>
                            <div>Price: <?php echo format_currency($item['price']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>