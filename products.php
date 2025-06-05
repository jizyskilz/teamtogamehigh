<?php
$page_title = 'Products';
require_once 'templates/header.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : null;

$products = get_products(null, $category_id, $search);
$categories = get_categories();

$current_category = null;
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $current_category = $cat;
            break;
        }
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1><?php echo $current_category ? htmlspecialchars($current_category['name']) : 'All Products'; ?></h1>
        <?php if ($search): ?>
            <p>Search results for: "<?php echo htmlspecialchars($search); ?>"</p>
        <?php endif; ?>
    </div>
    
    <!-- Filters and Search -->
    <div style="background: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: end;">
            <!-- Search -->
            <div>
                <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Search Products</label>
                <form method="GET" action="" style="display: flex; gap: 1rem;">
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Search for controllers..." 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <?php if ($category_id): ?>
                        <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
            
            <!-- Category Filter -->
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Filter by Category</label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="products.php" class="btn <?php echo !$category_id ? 'btn-primary' : ''; ?>" 
                       style="<?php echo !$category_id ? '' : 'background: #6c757d;'; ?>">All</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo $category['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo $category_id == $category['id'] ? 'btn-primary' : ''; ?>"
                           style="<?php echo $category_id == $category['id'] ? '' : 'background: #6c757d;'; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 10px;">
            <i class="fas fa-search" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3>No products found</h3>
            <p>Try adjusting your search criteria or browse all products.</p>
            <a href="products.php" class="btn btn-primary">View All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card fade-in">
                    <img src="<?php echo $product['image'] ? 'assets/uploads/' . $product['image'] : '/placeholder.svg?height=250&width=300'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <div style="margin: 1rem 0;">
                            <span style="background: #e3f2fd; color: #1976d2; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem;">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>
                        </div>
                        <div class="product-price"><?php echo format_currency($product['price']); ?></div>
                        <div class="product-actions">
                            <?php if (is_logged_in()): ?>
                                <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login to Buy
                                </a>
                            <?php endif; ?>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn" style="background: #6c757d;">
                                <i class="fas fa-eye"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>