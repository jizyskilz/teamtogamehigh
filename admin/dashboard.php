<?php
$page_title = 'Admin Dashboard';
require_once 'functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$stats = get_admin_stats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gaming Controllers TZ</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Admin mobile menu styles */
        .admin-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: #2c3e50;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 101;
        }
        
        @media (max-width: 768px) {
            .admin-menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <button class="admin-menu-toggle" id="adminMenuToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="admin-sidebar" id="adminSidebar">
        <div style="padding: 2rem; text-align: center; border-bottom: 1px solid #34495e;">
            <h3 style="color: white; margin: 0;">Admin Panel</h3>
            <p style="color: #bdc3c7; margin: 0.5rem 0 0 0;">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
        </div>
        
        <ul class="admin-nav">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-gamepad"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="../index.php"><i class="fas fa-home"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <h1 style="margin-bottom: 2rem;">Dashboard Overview</h1>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Total Products</div>
                <i class="fas fa-gamepad" style="font-size: 2rem; color: #667eea; margin-top: 1rem;"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Orders</div>
                <i class="fas fa-shopping-cart" style="font-size: 2rem; color: #2ed573; margin-top: 1rem;"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Customers</div>
                <i class="fas fa-users" style="font-size: 2rem; color: #ff6b6b; margin-top: 1rem;"></i>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo format_currency($stats['total_revenue']); ?></div>
                <div class="stat-label">Total Revenue</div>
                <i class="fas fa-money-bill-wave" style="font-size: 2rem; color: #feca57; margin-top: 1rem;"></i>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="products.php?action=add" class="btn btn-primary" style="text-decoration: none; text-align: center;">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
                <a href="orders.php" class="btn btn-success" style="text-decoration: none; text-align: center;">
                    <i class="fas fa-eye"></i> View Orders
                </a>
                <a href="users.php" class="btn" style="background: #6c757d; text-decoration: none; text-align: center;">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="categories.php?action=add" class="btn" style="background: #17a2b8; text-decoration: none; text-align: center;">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 1.5rem;">Recent Orders</h3>
            
            <?php
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT o.*, u.full_name FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (empty($recent_orders)): ?>
                <p style="text-align: center; color: #666;">No orders yet.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Order ID</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Customer</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Amount</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Status</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">#<?php echo $order['id']; ?></td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo format_currency($order['total_amount']); ?></td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                        <span style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; 
                                                     background: <?php echo $order['order_status'] === 'completed' ? '#d4edda' : '#fff3cd'; ?>; 
                                                     color: <?php echo $order['order_status'] === 'completed' ? '#155724' : '#856404'; ?>;">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Admin mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const adminMenuToggle = document.getElementById('adminMenuToggle');
            const adminSidebar = document.getElementById('adminSidebar');
            
            if (adminMenuToggle && adminSidebar) {
                adminMenuToggle.addEventListener('click', function() {
                    adminSidebar.classList.toggle('open');
                    
                    // Change icon based on sidebar state
                    const icon = adminMenuToggle.querySelector('i');
                    if (adminSidebar.classList.contains('open')) {
                        icon.className = 'fas fa-times';
                    } else {
                        icon.className = 'fas fa-bars';
                    }
                });
            }
        });
    </script>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>