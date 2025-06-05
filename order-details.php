<?php
$page_title = 'Order Details';
require_once 'templates/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    redirect('orders.php');
}

$order = get_order_by_id($order_id);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    redirect('orders.php');
}

$order_items = get_order_items($order_id);
?>

<div class="container" style="margin-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <a href="orders.php" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
    
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <!-- Order Header -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="margin-bottom: 0.5rem;">Order #<?php echo $order['id']; ?></h1>
                <p style="color: #666; margin: 0;">
                    Placed on <?php echo format_date($order['created_at']); ?>
                </p>
                <div style="margin-top: 1rem;">
                    <span style="background: <?php echo get_order_status_color($order['status']); ?>; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: bold;">
                        <?php echo get_order_status_text($order['status']); ?>
                    </span>
                </div>
            </div>
            
            <!-- Order Information -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                    <h3 style="margin-bottom: 1rem; color: #333;">
                        <i class="fas fa-credit-card"></i> Payment Information
                    </h3>
                    <p><strong>Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                    <p><strong>Total:</strong> <?php echo format_currency($order['total_amount']); ?></p>
                    <p><strong>Status:</strong> 
                        <span style="color: <?php echo get_order_status_color($order['status']); ?>;">
                            <?php echo get_order_status_text($order['status']); ?>
                        </span>
                    </p>
                </div>
                
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                    <h3 style="margin-bottom: 1rem; color: #333;">
                        <i class="fas fa-truck"></i> Delivery Information
                    </h3>
                    <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>
            </div>
            
            <!-- Order Items -->
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">
                    <i class="fas fa-box"></i> Order Items
                </h3>
                
                <?php if (!empty($order_items)): ?>
                    <div style="border: 1px solid #eee; border-radius: 10px; overflow: hidden;">
                        <?php foreach ($order_items as $index => $item): ?>
                            <div style="display: flex; align-items: center; padding: 1.5rem; <?php echo $index > 0 ? 'border-top: 1px solid #eee;' : ''; ?>">
                                <img src="<?php echo $item['image'] ? 'assets/uploads/' . htmlspecialchars($item['image']) : '/placeholder.svg?height=80&width=80'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 1.5rem;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 0.5rem 0; color: #333;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </h4>
                                    <p style="margin: 0; color: #666;">
                                        Quantity: <?php echo $item['quantity']; ?> × <?php echo format_currency($item['price']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: bold; color: #667eea;">
                                        <?php echo format_currency($item['price'] * $item['quantity']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Order Total -->
                        <div style="background: #f8f9fa; padding: 1.5rem; border-top: 2px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 1.2rem; font-weight: bold;">Total:</span>
                                <span style="font-size: 1.3rem; font-weight: bold; color: #667eea;">
                                    <?php echo format_currency($order['total_amount']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 2rem;">No items found for this order.</p>
                <?php endif; ?>
            </div>
            
            <!-- Order Actions -->
            <div style="text-align: center; border-top: 1px solid #eee; padding-top: 2rem;">
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <?php if ($order['status'] === 'pending' && $order['payment_method'] === 'clickpesa'): ?>
                        <a href="process-clickpesa.php?order_id=<?php echo $order['id']; ?>" class="btn" style="background: #f39c12;">
                            <i class="fas fa-credit-card"></i> Complete Payment
                        </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($order['status'], ['delivered'])): ?>
                        <button class="btn" style="background: #2ed573;" onclick="reorderItems(<?php echo $order['id']; ?>)">
                            <i class="fas fa-redo"></i> Reorder Items
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn" style="background: #17a2b8;" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Order
                    </button>
                    
                    <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn" style="background: #dc3545;" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function reorderItems(orderId) {
    if (confirm('Add all items from this order to your cart?')) {
        // Implementation for reordering items
        showNotification('Items added to cart!', 'success');
    }
}

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        // Implementation for cancelling order
        showNotification('Order cancelled successfully', 'success');
        setTimeout(() => {
            window.location.href = 'orders.php';
        }, 1500);
    }
}

function showNotification(message, type = 'info') {
    // Same notification function as in orders.php
    const existing = document.querySelectorAll('.notification');
    existing.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `<span>${message}</span><button onclick="this.parentElement.remove()">&times;</button>`;
    
    const bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff';
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 1000;
        background: ${bgColor}; color: white; padding: 15px 20px;
        border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        display: flex; align-items: center; gap: 10px;
        font-family: Arial, sans-serif; font-size: 14px;
    `;
    
    const closeBtn = notification.querySelector('button');
    closeBtn.style.cssText = `
        background: none; border: none; color: white; font-size: 18px;
        cursor: pointer; padding: 0; margin-left: 10px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php require_once 'templates/footer.php'; ?>
