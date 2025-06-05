<?php
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    redirect('products.php');
}

$product = get_product_by_id($product_id);

if (!$product) {
    redirect('products.php');
}

$page_title = $product['name'];
require_once 'templates/header.php';

// Get related products from same category
$related_products = get_products(4, $product['category_id']);
$related_products = array_filter($related_products, function($p) use ($product_id) {
    return $p['id'] != $product_id;
});
?>

<div class="container" style="margin-top: 2rem;">
    <!-- Breadcrumb -->
    <nav style="margin-bottom: 2rem;">
        <a href="index.php" style="color: #667eea; text-decoration: none;">Home</a> > 
        <a href="products.php" style="color: #667eea; text-decoration: none;">Products</a> > 
        <a href="products.php?category=<?php echo $product['category_id']; ?>" style="color: #667eea; text-decoration: none;">
            <?php echo htmlspecialchars($product['category_name']); ?>
        </a> > 
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>
    
    <!-- Product Details -->
    <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 3rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0;">
            <!-- Product Image -->
            <div style="padding: 2rem;">
                <img src="<?php echo $product['image'] ? 'assets/uploads/' . $product['image'] : '/placeholder.svg?height=400&width=400'; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     style="width: 100%; height: 400px; object-fit: cover; border-radius: 10px;">
            </div>
            
            <!-- Product Info -->
            <div style="padding: 2rem;">
                <h1 style="font-size: 2rem; margin-bottom: 1rem; color: #333;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                
                <div style="margin-bottom: 1rem;">
                    <span style="background: #e3f2fd; color: #1976d2; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                </div>
                
                <?php if ($product['brand']): ?>
                    <p style="margin-bottom: 1rem;"><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></p>
                <?php endif; ?>
                
                <?php if ($product['compatibility']): ?>
                    <p style="margin-bottom: 1rem;"><strong>Compatibility:</strong> <?php echo htmlspecialchars($product['compatibility']); ?></p>
                <?php endif; ?>
                
                <div style="font-size: 2.5rem; font-weight: bold; color: #667eea; margin: 1.5rem 0;">
                    <?php echo format_currency($product['price']); ?>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span style="color: #2ed573; font-weight: bold;">
                            <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock_quantity']; ?> available)
                        </span>
                    <?php else: ?>
                        <span style="color: #ff4757; font-weight: bold;">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <?php if (is_logged_in() && $product['stock_quantity'] > 0): ?>
                        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                            <label for="quantity" style="font-weight: bold;">Quantity:</label>
                            <select id="quantity" style="padding: 0.5rem; border: 2px solid #e1e8ed; border-radius: 5px;">
                                <?php for ($i = 1; $i <= min(10, $product['stock_quantity']); $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary" style="width: 100%; font-size: 1.2rem; padding: 1rem;" 
                                onclick="addToCartWithQuantity(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="login.php" class="btn btn-primary" style="width: 100%; font-size: 1.2rem; padding: 1rem; text-align: center; display: block; text-decoration: none;">
                            <i class="fas fa-sign-in-alt"></i> Login to Purchase
                        </a>
                    <?php else: ?>
                        <button class="btn" style="width: 100%; font-size: 1.2rem; padding: 1rem; background: #6c757d; cursor: not-allowed;" disabled>
                            <i class="fas fa-times"></i> Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Features -->
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                    <h4 style="margin-bottom: 1rem;">Why Buy From Us?</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-shield-alt" style="color: #2ed573; margin-right: 0.5rem;"></i> 100% Authentic Products</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-shipping-fast" style="color: #2ed573; margin-right: 0.5rem;"></i> Fast Delivery in Tanzania</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fas fa-undo" style="color: #2ed573; margin-right: 0.5rem;"></i> 30-Day Return Policy</li>
                        <li><i class="fas fa-headset" style="color: #2ed573; margin-right: 0.5rem;"></i> 24/7 Customer Support</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Description -->
    <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 3rem;">
        <h3 style="margin-bottom: 1.5rem;">Product Description</h3>
        <p style="line-height: 1.8; color: #666;">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div>
            <h3 style="margin-bottom: 2rem; text-align: center;">Related Products</h3>
            <div class="products-grid">
                <?php foreach (array_slice($related_products, 0, 4) as $related): ?>
                    <div class="product-card">
                        <img src="<?php echo $related['image'] ? 'assets/uploads/' . $related['image'] : '/placeholder.svg?height=250&width=300'; ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>" 
                             class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="product-price"><?php echo format_currency($related['price']); ?></div>
                            <div class="product-actions">
                                <a href="product-details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    
    fetch('modules/cart/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding to cart', 'error');
    });
}
</script>

<?php require_once 'templates/footer.php'; ?>