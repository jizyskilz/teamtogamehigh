<?php
$page_title = 'Manage Users';
require_once 'functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Gaming Controllers TZ</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-sidebar">
        <div style="padding: 2rem; text-align: center; border-bottom: 1px solid #34495e;">
            <h3 style="color: white; margin: 0;">Admin Panel</h3>
            <p style="color: #bdc3c7; margin: 0.5rem 0 0 0;">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
        </div>
        
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-gamepad"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="../index.php"><i class="fas fa-home"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <h1 style="margin-bottom: 2rem;">Manage Users</h1>
        
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">ID</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Full Name</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Username</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Email</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Phone</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Role</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo $user['id']; ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                    <span style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; 
                                                 background: <?php echo $user['role'] === 'admin' ? '#d4edda' : '#e3f2fd'; ?>; 
                                                 color: <?php echo $user['role'] === 'admin' ? '#155724' : '#1976d2'; ?>;">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>