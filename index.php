<?php
$page_title = 'Home';
require_once 'templates/header.php';

// Get featured products
$featured_products = get_products(8);
$categories = get_categories();
?>

<!-- Hero Section with Background Image and Animations -->
<section class="hero">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <div class="floating-controllers">
            <div class="controller controller-1">🎮</div>
            <div class="controller controller-2">🕹️</div>
            <div class="controller controller-3">🎮</div>
            <div class="controller controller-4">🕹️</div>
            <div class="controller controller-5">🎮</div>
        </div>
    </div>
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">
                <span class="title-line">Premium Gaming</span>
                <span class="title-line">Controllers</span>
            </h1>
            <p class="hero-subtitle">Discover the best gaming controllers for PlayStation, Xbox, PC, and Nintendo in Tanzania</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary btn-hero">
                    <i class="fas fa-shopping-cart"></i>
                    Shop Now
                </a>
                <a href="#featured" class="btn btn-secondary btn-hero">
                    <i class="fas fa-star"></i>
                    View Featured
                </a>
            </div>
        </div>
        <div class="hero-image">
            <div class="controller-showcase">
                <img src="assets/img/gaming-6092074.jpg" alt="Gaming Controller" class="showcase-controller">
                <div class="glow-effect"></div>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="scroll-indicator">
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section" id="categories">
    <div class="container">
        <h2 class="section-title animate-on-scroll">Shop by Category</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $index => $category): ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card animate-on-scroll" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="category-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                    <div class="category-hover-effect"></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="products-section" id="featured">
    <div class="container">
        <h2 class="section-title animate-on-scroll">Featured Products</h2>
        <div class="products-grid">
            <?php foreach ($featured_products as $index => $product): ?>
                <div class="product-card animate-on-scroll" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="product-image-container">
                        <img src="<?php echo $product['image'] ? 'assets/uploads/' . $product['image'] : '/placeholder.svg?height=250&width=300'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                        <div class="product-overlay">
                            <div class="quick-view">
                                <i class="fas fa-eye"></i>
                                Quick View
                            </div>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
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
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="products.php" class="btn btn-primary btn-large animate-on-scroll">View All Products</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title animate-on-scroll">Why Choose Us?</h2>
        <div class="features-grid">
            <div class="feature-card animate-on-scroll" style="animation-delay: 0.1s;">
                <div class="feature-icon delivery">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Quick delivery across Tanzania within 1-3 business days</p>
            </div>
            <div class="feature-card animate-on-scroll" style="animation-delay: 0.2s;">
                <div class="feature-icon security">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure Payments</h3>
                <p>Multiple payment options including ClickPesa and LIPA NAMBA</p>
            </div>
            <div class="feature-card animate-on-scroll" style="animation-delay: 0.3s;">
                <div class="feature-icon support">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Round-the-clock customer support for all your needs</p>
            </div>
            <div class="feature-card animate-on-scroll" style="animation-delay: 0.4s;">
                <div class="feature-icon quality">
                    <i class="fas fa-medal"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>100% authentic products with warranty coverage</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content animate-on-scroll">
            <h2>Stay Updated</h2>
            <p>Get the latest gaming controller deals and news delivered to your inbox</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Subscribe
                </button>
            </form>
        </div>
    </div>
</section>

<style>
/* Hero Section Styles */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    background-size: cover, cover, 50px 50px;
    animation: backgroundShift 20s ease-in-out infinite;
}

@keyframes backgroundShift {
    0%, 100% { transform: translateX(0) translateY(0); }
    25% { transform: translateX(-10px) translateY(-5px); }
    50% { transform: translateX(10px) translateY(5px); }
    75% { transform: translateX(-5px) translateY(10px); }
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
}

.floating-controllers {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.controller {
    position: absolute;
    font-size: 2rem;
    opacity: 0.1;
    animation: float 6s ease-in-out infinite;
}
img{
    border-radius: 20px;
}
.controller-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.controller-2 {
    top: 60%;
    left: 80%;
    animation-delay: 1s;
}

.controller-3 {
    top: 80%;
    left: 20%;
    animation-delay: 2s;
}

.controller-4 {
    top: 30%;
    left: 70%;
    animation-delay: 3s;
}

.controller-5 {
    top: 50%;
    left: 5%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(10deg); }
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.hero-text {
    animation: slideInLeft 1s ease-out;
}

.hero-title {
    font-size: 4rem;
    font-weight: 900;
    color: white;
    margin-bottom: 1.5rem;
    line-height: 1.1;
}

.title-line {
    display: block;
    opacity: 0;
    animation: titleReveal 1s ease-out forwards;
}

.title-line:nth-child(1) {
    animation-delay: 0.3s;
}

.title-line:nth-child(2) {
    animation-delay: 0.6s;
    background: linear-gradient(45deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3);
    background-size: 400% 400%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: titleReveal 1s ease-out forwards, gradientShift 3s ease-in-out infinite;
}

@keyframes titleReveal {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.hero-subtitle {
    font-size: 1.3rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2rem;
    line-height: 1.6;
    opacity: 0;
    animation: fadeInUp 1s ease-out 0.9s forwards;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    opacity: 0;
    animation: fadeInUp 1s ease-out 1.2s forwards;
}

.btn-hero {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.hero-image {
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 20px;
    animation: slideInRight 1s ease-out;
}

.controller-showcase {
    position: relative;
    animation: floatController 3s ease-in-out infinite;
}

.showcase-controller {
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
    transition: transform 0.3s ease;
}

.showcase-controller:hover {
    transform: scale(1.05) rotate(5deg);
}

.glow-effect {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 120%;
    height: 120%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes floatController {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.1); }
}

.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    animation: bounce 2s infinite;
}

.scroll-arrow {
    color: white;
    font-size: 1.5rem;
    opacity: 0.7;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

/* Animation Classes */
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease-out;
}

.animate-on-scroll.animate {
    opacity: 1;
    transform: translateY(0);
}

/* Enhanced Section Styles */
.categories-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.category-card {
    position: relative;
    background: white;
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: inherit;
}

.category-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #667eea;
    transition: all 0.3s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1) rotate(10deg);
    color: #764ba2;
}

.category-hover-effect {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.category-card:hover .category-hover-effect {
    left: 100%;
}

.products-section {
    padding: 5rem 0;
    background: white;
}

.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.quick-view {
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-outline {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline:hover {
    background: #667eea;
    color: white;
}

.features-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.feature-card {
    text-align: center;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    position: relative;
    overflow: hidden;
}

.feature-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.3));
    border-radius: 50%;
}

.delivery { background: linear-gradient(135deg, #2ed573, #17a2b8); }
.security { background: linear-gradient(135deg, #ff6b6b, #ee5a24); }
.support { background: linear-gradient(135deg, #feca57, #ff9ff3); }
.quality { background: linear-gradient(135deg, #48dbfb, #0abde3); }

.newsletter-section {
    padding: 4rem 0;
    background: #f8f9fa;
}

.newsletter-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-form {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.newsletter-form input {
    flex: 1;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 50px;
    font-size: 1rem;
}

.newsletter-form button {
    border-radius: 50px;
    padding: 1rem 1.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
}
</style>

<script>
// Loading animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);
    
    // Observe all elements with animate-on-scroll class
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Newsletter form submission
    document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // Simple validation
        if (email) {
            // Here you would typically send the email to your backend
            alert('Thank you for subscribing! We\'ll keep you updated with the latest gaming controller deals.');
            this.reset();
        }
    });
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            // Disable button temporarily
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            // Simulate API call (replace with actual implementation)
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check"></i> Added!';
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                }, 2000);
            }, 1000);
        });
    });
});

// Parallax effect for hero background
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const heroBackground = document.querySelector('.hero-background');
    
    if (heroBackground) {
        heroBackground.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});
</script>

<?php require_once 'templates/footer.php'; ?>
