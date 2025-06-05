<?php
session_start();
$page_title = 'Checkout';
require_once 'templates/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$cart_items = get_cart_items($_SESSION['user_id']);
$cart_total = calculate_cart_total($_SESSION['user_id']);

if (empty($cart_items)) {
    redirect('cart.php');
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']); // Clear session messages after displaying

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("Checkout form submitted at " . date('Y-m-d H:i:s'));
    
    $payment_method = sanitize_input($_POST['payment_method']);
    $shipping_address = sanitize_input($_POST['shipping_address']);
    $phone = sanitize_input($_POST['phone']);
    
    error_log("Form data - Payment: $payment_method, Address: $shipping_address, Phone: $phone, User ID: " . $_SESSION['user_id']);
    
    if (empty($payment_method) || empty($shipping_address) || empty($phone)) {
        $error = 'Please fill in all required fields';
        error_log("Validation failed - missing fields");
    } else {
        if ($payment_method === 'clickpesa') {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (!preg_match('/^(255|0)[67][0-9]{8}$/', $phone)) {
                $error = 'Please enter a valid Tanzanian mobile number (e.g., +255712345678)';
            } else {
                if (substr($phone, 0, 1) === '0') {
                    $phone = '255' . substr($phone, 1);
                }
            }
        }
        
        if (!$error) {
            error_log("Creating order...");
            
            $debug_cart = get_cart_items($_SESSION['user_id']);
            error_log("Cart items before order creation: " . print_r($debug_cart, true));
            
            $order_id = create_order($_SESSION['user_id'], $cart_total, $payment_method, $shipping_address, $phone);
            
            if ($order_id) {
                error_log("Order created successfully with ID: $order_id");
                
                if ($payment_method === 'cash_on_delivery') {
                    if (clear_user_cart($_SESSION['user_id'])) {
                        error_log("Cart cleared successfully");
                        redirect('order-confirmation.php?order_id=' . $order_id);
                    } else {
                        error_log("Failed to clear cart for user ID: " . $_SESSION['user_id']);
                        $error = 'Order created but failed to clear cart';
                    }
                } else if ($payment_method === 'clickpesa') {  
                    error_log("Redirecting to ClickPesa");
                    redirect('process-clickpesa.php?order_id=' . $order_id);
                }
            } else {
                error_log("Order creation failed at " . date('Y-m-d H:i:s'));
                $error = 'Failed to create order. Please try again.';
            }
        }
    }
}
?>

<!-- Add debug information in development -->
<?php if (isset($_GET['debug'])): ?>
<div style="background: #f8f9fa; padding: 1rem; margin: 1rem; border-radius: 5px; font-family: monospace; font-size: 0.8rem;">
    <strong>Debug Info:</strong><br>
    User ID: <?php echo $_SESSION['user_id']; ?><br>
    Cart Items: <?php echo count($cart_items); ?><br>
    Cart Total: <?php echo $cart_total; ?><br>
    Functions Available: 
    create_order=<?php echo function_exists('create_order') ? 'YES' : 'NO'; ?>,
    get_cart_items=<?php echo function_exists('get_cart_items') ? 'YES' : 'NO'; ?>,
    calculate_cart_total=<?php echo function_exists('calculate_cart_total') ? 'YES' : 'NO'; ?>,
    clear_user_cart=<?php echo function_exists('clear_user_cart') ? 'YES' : 'NO'; ?><br>
</div>
<?php endif; ?>

<div class="container" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 2rem;">Checkout</h1>
    
    <?php if ($error): ?>
        <div style="background: #ff4757; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <br><small>If this problem persists, please <a href="debug-checkout.php" style="color: #fff; text-decoration: underline;">check debug info</a></small>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div style="background: #2ed573; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Checkout Form -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <form method="POST" action="" id="checkout-form">
                <h3 style="margin-bottom: 1.5rem;">
                    <i class="fas fa-shipping-fast"></i> Shipping Information
                </h3>
                
                <div class="form-group">
                    <label for="shipping_address">Delivery Address *</label>
                    <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" required 
                              placeholder="Enter your full delivery address including street, area, and city"><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required 
                           placeholder="+255712345678 or 0712345678"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    <small style="color: #666; font-size: 0.8rem;">
                        <i class="fas fa-info-circle"></i> For ClickPesa payments, use your mobile money number
                    </small>
                </div>
                
                <h3 style="margin: 2rem 0 1.5rem;">
                    <i class="fas fa-credit-card"></i> Payment Method
                </h3>
                
                <div class="payment-methods">
                    <div class="payment-method" data-method="clickpesa">
                        <div class="payment-icon">
                            <i class="fas fa-mobile-alt" style="font-size: 2rem; color: #667eea;"></i>
                        </div>
                        <div class="payment-info">
                            <h4>ClickPesa</h4>
                            <p>Pay with Vodacom M-Pesa, Tigo Pesa, Airtel Money</p>
                            <small style="color: #2ed573;">
                                <i class="fas fa-shield-alt"></i> Secure mobile payment
                            </small>
                        </div>
                        <input type="radio" name="payment_method" value="clickpesa" style="display: none;">
                    </div>
                    
                    <div class="payment-method" data-method="cash_on_delivery">
                        <div class="payment-icon">
                            <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: #2ed573;"></i>
                        </div>
                        <div class="payment-info">
                            <h4>Cash on Delivery</h4>
                            <p>Pay when you receive your order</p>
                            <small style="color: #f39c12;">
                                <i class="fas fa-truck"></i> Payment upon delivery
                            </small>
                        </div>
                        <input type="radio" name="payment_method" value="cash_on_delivery" style="display: none;">
                    </div>
                </div>
                
                <input type="hidden" id="selected_payment_method" name="payment_method" value="">
                
                <button type="submit" class="btn btn-primary" id="place-order-btn" style="width: 100%; margin-top: 2rem; font-size: 1.2rem; padding: 1rem;">
                    <i class="fas fa-check"></i> Place Order
                </button>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); height: fit-content;">
            <h3 style="margin-bottom: 1.5rem;">
                <i class="fas fa-receipt"></i> Order Summary
            </h3>
            
            <!-- Cart Items -->
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                <?php foreach ($cart_items as $item): ?>
                    <div style="display: flex; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                        <img src="<?php echo $item['image'] ? 'assets/uploads/' . $item['image'] : '/placeholder.svg?height=50&width=50'; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 1rem;">
                        <div style="flex: 1;">
                            <div style="font-weight: bold; font-size: 0.9rem;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="color: #666; font-size: 0.8rem;">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div style="font-weight: bold;">
                            <?php echo format_currency($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="border-top: 2px solid #eee; padding-top: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Subtotal:</span>
                    <span><?php echo format_currency($cart_total); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Delivery:</span>
                    <span style="color: #2ed573;">Free</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; color: #667eea; border-top: 1px solid #eee; padding-top: 0.5rem;">
                    <span>Total:</span>
                    <span><?php echo format_currency($cart_total); ?></span>
                </div>
            </div>
            
            <!-- Security Badge -->
            <div style="text-align: center; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <i class="fas fa-lock" style="color: #2ed573; margin-right: 0.5rem;"></i>
                <small style="color: #666;">Your payment information is secure and encrypted</small>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center;">
        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
        <p style="margin-top: 1rem; color: #333;">Processing your order...</p>
    </div>
</div>

<style>
.payment-methods {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

.payment-method {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.payment-method:hover {
    border-color: #667eea;
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.payment-method.selected {
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.payment-icon {
    margin-right: 1rem;
    min-width: 60px;
    text-align: center;
}

.payment-info {
    flex: 1;
}

.payment-info h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.1rem;
}

.payment-info p {
    margin: 0 0 0.25rem 0;
    color: #666;
    font-size: 0.9rem;
}

.payment-info small {
    font-size: 0.8rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

#place-order-btn {
    transition: all 0.3s ease;
}

#place-order-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

#place-order-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>

<script>
// Payment method selection
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
        this.classList.add('selected');
        const selectedMethod = this.dataset.method;
        document.getElementById('selected_payment_method').value = selectedMethod;
        this.querySelector('input[type="radio"]').checked = true;
        const button = document.getElementById('place-order-btn');
        if (selectedMethod === 'clickpesa') {
            button.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with ClickPesa';
            button.style.background = '#667eea';
        } else if (selectedMethod === 'cash_on_delivery') {
            button.innerHTML = '<i class="fas fa-truck"></i> Place Order (Cash on Delivery)';
            button.style.background = '#2ed573';
        }
    });
});

// Form validation and loading spinner
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    const paymentMethod = document.getElementById('selected_payment_method').value;
    const phone = document.getElementById('phone').value;
    const address = document.getElementById('shipping_address').value;
    
    if (!paymentMethod) {
        e.preventDefault();
        alert('Please select a payment method');
        return;
    }
    
    if (!phone.trim() || !address.trim()) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return;
    }
    
    if (paymentMethod === 'clickpesa') {
        const phoneRegex = /^(\+?255|0)[67][0-9]{8}$/;
        const cleanPhone = phone.replace(/\s+/g, '');
        
        if (!phoneRegex.test(cleanPhone)) {
            e.preventDefault();
            alert('Please enter a valid Tanzanian mobile number for ClickPesa payment\nExample: +255712345678 or 0712345678');
            return;
        }
    }
    
    // Show loading spinner
    document.getElementById('loading-overlay').style.display = 'flex';
    
    const button = document.getElementById('place-order-btn');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('255')) {
        value = '+' + value;
    }
    e.target.value = value;
});
</script>

<?php require_once 'templates/footer.php'; ?>