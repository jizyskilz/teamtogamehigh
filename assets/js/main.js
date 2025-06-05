// Main JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart
    updateCartCount();
    
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = 1;
            
            // Disable button during request
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            addToCart(productId, quantity, this);
        });
    });
    
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('open');
        });
    }
});

// Add to cart function with better error handling
async function addToCart(productId, quantity, buttonElement = null) {
    try {
        console.log('Adding to cart:', { productId, quantity });
        
        const response = await fetch('modules/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Get the response text first
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        // Check if response is empty
        if (!responseText || responseText.trim() === '') {
            throw new Error('Empty response from server');
        }
        
        // Try to parse JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            console.error('Response text:', responseText);
            throw new Error('Invalid JSON response: ' + responseText.substring(0, 100));
        }
        
        console.log('Parsed result:', result);
        
        if (result.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification(result.message || 'Error adding to cart', 'error');
        }
        
    } catch (error) {
        console.error('Cart error details:', error);
        showNotification(`Error adding to cart: ${error.message}`, 'error');
    } finally {
        // Re-enable button
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
        }
    }
}

// Update cart count in header
async function updateCartCount() {
    try {
        const response = await fetch('modules/cart/count.php');
        
        if (!response.ok) {
            console.error('Cart count request failed:', response.status);
            return;
        }
        
        const responseText = await response.text();
        
        if (!responseText || responseText.trim() === '') {
            console.error('Empty cart count response');
            return;
        }
        
        const result = JSON.parse(responseText);
        
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement && result.count > 0) {
            cartCountElement.textContent = result.count;
            cartCountElement.style.display = 'flex';
        } else if (cartCountElement) {
            cartCountElement.style.display = 'none';
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close">&times;</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#2ed573' : type === 'error' ? '#ff4757' : '#667eea'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideIn 0.3s ease-out;
        max-width: 400px;
        word-wrap: break-word;
    `;
    
    // Add close functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        margin-left: 1rem;
    `;
    
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .notification {
        animation: slideIn 0.3s ease-out;
    }
`;
document.head.appendChild(style);
// Navigation toggle for mobile
document.getElementById('navToggle').addEventListener('click', function () {
    document.querySelector('.nav-links').classList.toggle('show');
});
