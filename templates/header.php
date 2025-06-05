<?php
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'includes/functions.php';

$cart_count = 0;
if (is_logged_in()) {
    $cart_count = get_cart_count();
}
$current_page = get_current_page();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Gaming Controllers TZ' : 'Gaming Controllers TZ'; ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
            position: relative;
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo:hover {
            color: #f1f1f1;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .cart-icon {
            position: relative;
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-menu-toggle {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-menu-toggle:hover {
            background: rgba(255,255,255,0.2);
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            min-width: 200px;
            display: none;
            z-index: 1001;
        }

        .user-dropdown.show {
            display: block;
        }

        .user-dropdown a {
            display: block;
            padding: 0.75rem 1rem;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        .user-dropdown a:hover {
            background: #f8f9fa;
        }

        .user-dropdown a:last-child {
            border-bottom: none;
        }

        /* Mobile toggle button */
        .nav-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }

        @media (max-width: 768px) {
            .nav-toggle {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                width: 100%;
                background: #4b6cb7;
                margin-top: 1rem;
                padding: 1rem 0;
                border-radius: 10px;
            }

            .nav-links.show {
                display: flex;
            }

            .nav-links li {
                width: 100%;
                text-align: center;
            }

            .nav-links a {
                display: block;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <i class="fas fa-gamepad"></i>
                SnakeArt
            </a>

            <!-- 🌐 Mobile toggle button -->
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="products.php" class="<?php echo $current_page === 'products' ? 'active' : ''; ?>">Products</a></li>
                <li><a href="#footer" class="<?php echo $current_page === 'about' ? 'active' : ''; ?>">About</a></li>
                <li><a href="#footer" class="<?php echo $current_page === 'contact' ? 'active' : ''; ?>">Contact</a></li>

                <?php if (is_logged_in()): ?>
                    <li>
                        <a href="cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="user-menu">
                        <a href="#" class="user-menu-toggle" onclick="toggleUserMenu(event)">
                            <i class="fas fa-user"></i>
                            <span>Account</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                            <?php if(is_admin()):?>
                            <a href="orders.php"><i class="fas fa-box"></i> My Orders</a>
                            <?php endif;?>
                            <?php if (is_admin()): ?>
                                <a href="admin/dashboard.php"><i class="fas fa-cog"></i> Admin Panel</a>
                            <?php endif; ?>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="<?php echo $current_page === 'login' ? 'active' : ''; ?>">Login</a></li>
                    <li><a href="register.php" class="<?php echo $current_page === 'register' ? 'active' : ''; ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <script>
        function toggleUserMenu(event) {
            event.preventDefault();
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');

            if (dropdown && !userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        function updateCartCount() {
            if (typeof fetch !== 'undefined') {
                fetch('modules/cart/count.php')
                    .then(response => response.json())
                    .then(data => {
                        const cartIcon = document.querySelector('.cart-icon');
                        const existingCount = cartIcon.querySelector('.cart-count');

                        if (data.count > 0) {
                            if (existingCount) {
                                existingCount.textContent = data.count;
                            } else {
                                const countSpan = document.createElement('span');
                                countSpan.className = 'cart-count';
                                countSpan.textContent = data.count;
                                cartIcon.appendChild(countSpan);
                            }
                        } else {
                            if (existingCount) {
                                existingCount.remove();
                            }
                        }
                    })
                    .catch(error => console.log('Cart count update failed:', error));
            }
        }

        if (<?php echo is_logged_in() ? 'true' : 'false'; ?>) {
            setInterval(updateCartCount, 30000);
        }

        // 🔄 Toggle nav menu on small screens
        document.getElementById('navToggle').addEventListener('click', function () {
            document.getElementById('navLinks').classList.toggle('show');
        });
    </script>
