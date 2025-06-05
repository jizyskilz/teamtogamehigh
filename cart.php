<?php
$page_title = 'Shopping Cart';
require_once 'templates/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$cart_items = get_cart_items($_SESSION['user_id']);
$cart_total = calculate_cart_total($_SESSION['user_id']);
?>

<div class="container" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 2rem;">Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3>Your cart is empty</h3>
            <p>Add some amazing gaming controllers to get started!</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                        <img src="<?php echo $item['image'] ? 'assets/uploads/' . $item['image'] : '/placeholder.svg?height=80&width=80'; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="cart-item-info">
                            <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="cart-item-price"><?php echo format_currency($item['price']); ?></div>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')">-</button>
                                <span class="quantity" id="quantity-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">+</button>
                                <button class="btn btn-danger" style="margin-left: 1rem; padding: 0.25rem 0.5rem;" 
                                        onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div style="font-weight: bold; margin-top: 0.5rem;" id="subtotal-<?php echo $item['id']; ?>">
                                Subtotal: <?php echo format_currency($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); height: fit-content;">
                <h3 style="margin-bottom: 1.5rem;">Order Summary</h3>
                
                <div style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal:</span>
                        <span id="cart-total"><?php echo format_currency($cart_total); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Tax:</span>
                        <span>Included</span>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; margin-bottom: 2rem;">
                    <span>Total:</span>
                    <span id="final-total"><?php echo format_currency($cart_total); ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; display: block; text-decoration: none; font-size: 1.1rem;">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </a>
                
                <a href="products.php" style="display: block; text-align: center; margin-top: 1rem; color: #667eea; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Improved cart functions with better error handling
async function updateQuantity(cartId, action) {
    console.log('Updating quantity:', cartId, action);
    
    try {
        const quantityElement = document.getElementById(`quantity-${cartId}`);
        if (!quantityElement) {
            throw new Error('Quantity element not found');
        }
        
        let currentQuantity = parseInt(quantityElement.textContent);
        let newQuantity = currentQuantity;
        
        if (action === 'increase') {
            newQuantity = currentQuantity + 1;
        } else if (action === 'decrease') {
            newQuantity = currentQuantity - 1;
            if (newQuantity < 1) {
                removeFromCart(cartId);
                return;
            }
        }
        
        console.log('New quantity:', newQuantity);
        
        // Disable buttons
        const buttons = document.querySelectorAll(`#cart-item-${cartId} .quantity-btn`);
        buttons.forEach(btn => btn.disabled = true);
        
        // Make request
        const response = await fetch('modules/cart/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${newQuantity}`
        });
        
        console.log('Response status:', response.status);
        
        // Get response text
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        // Check if response is HTML (error page)
        if (responseText.trim().startsWith('<') || responseText.includes('<!DOCTYPE')) {
            console.error('Server returned HTML instead of JSON');
            console.log('HTML response:', responseText.substring(0, 200));
            throw new Error('Server error - check console for details');
        }
        
        // Check if response is empty
        if (!responseText || responseText.trim() === '') {
            throw new Error('Empty response from server');
        }
        
        // Parse JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.log('Response that failed to parse:', responseText);
            throw new Error('Invalid response format');
        }
        
        console.log('Parsed response:', data);
        
        if (data.success) {
            // Update display
            quantityElement.textContent = newQuantity;
            showNotification('Cart updated!', 'success');
            
            // Reload page to update totals
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showNotification(data.message || 'Update failed', 'error');
        }
        
    } catch (error) {
        console.error('Update error:', error);
        showNotification('Error: ' + error.message, 'error');
    } finally {
        // Re-enable buttons
        const buttons = document.querySelectorAll(`#cart-item-${cartId} .quantity-btn`);
        buttons.forEach(btn => btn.disabled = false);
    }
}

async function removeFromCart(cartId) {
    if (!confirm('Remove this item from cart?')) {
        return;
    }
    
    try {
        const response = await fetch('modules/cart/remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        });
        
        const responseText = await response.text();
        console.log('Remove response:', responseText);
        
        // Check for HTML response
        if (responseText.trim().startsWith('<')) {
            throw new Error('Server error - check console');
        }
        
        const data = JSON.parse(responseText);
        
        if (data.success) {
            showNotification('Item removed!', 'success');
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showNotification(data.message || 'Remove failed', 'error');
        }
        
    } catch (error) {
        console.error('Remove error:', error);
        showNotification('Error: ' + error.message, 'error');
    }
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.notification');
    existing.forEach(n => n.remove());
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `<span>${message}</span><button onclick="this.parentElement.remove()">&times;</button>`;
    
    // Style notification
    const bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff';
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 1000;
        background: ${bgColor}; color: white; padding: 15px 20px;
        border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        display: flex; align-items: center; gap: 10px;
        font-family: Arial, sans-serif; font-size: 14px;
    `;
    
    // Style close button
    const closeBtn = notification.querySelector('button');
    closeBtn.style.cssText = `
        background: none; border: none; color: white; font-size: 18px;
        cursor: pointer; padding: 0; margin-left: 10px;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php require_once 'templates/footer.php'; ?>
