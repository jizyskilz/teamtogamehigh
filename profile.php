<?php
$page_title = 'My Profile';
require_once 'templates/header.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        // Update basic profile info
        $query = "UPDATE users SET full_name = :full_name, phone = :phone, address = :address WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            
            // Handle password change if provided
            if (!empty($current_password) && !empty($new_password)) {
                // Verify current password
                $query = "SELECT password FROM users WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            
                            $query = "UPDATE users SET password = :password WHERE id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':password', $hashed_password);
                            $stmt->bindParam(':id', $_SESSION['user_id']);
                            
                            if ($stmt->execute()) {
                                $success = 'Profile and password updated successfully';
                            } else {
                                $error = 'Failed to update password';
                            }
                        } else {
                            $error = 'New password must be at least 6 characters long';
                        }
                    } else {
                        $error = 'New passwords do not match';
                    }
                } else {
                    $error = 'Current password is incorrect';
                }
            } else {
                $success = 'Profile updated successfully';
            }
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Get user data
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user orders
$query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 2rem;">My Profile</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Profile Information -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 1.5rem;">Profile Information</h3>
            
            <?php if ($error): ?>
                <div style="background: #ff4757; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #2ed573; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" class="form-control" readonly 
                           value="<?php echo htmlspecialchars($user['username']); ?>">
                    <small style="color: #666;">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" readonly 
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                    <small style="color: #666;">Email cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                
                <hr style="margin: 2rem 0;">
                
                <h4 style="margin-bottom: 1rem;">Change Password</h4>
                <small style="color: #666; display: block; margin-bottom: 1rem;">Leave blank if you don't want to change your password</small>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>
        
        <!-- Order History -->
        <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 1.5rem;">Order History</h3>
            
            <?php if (empty($orders)): ?>
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No orders yet</p>
                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($orders as $order): ?>
                        <div style="border: 1px solid #eee; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong>Order #<?php echo $order['id']; ?></strong>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; 
                                             background: <?php echo $order['order_status'] === 'completed' ? '#d4edda' : '#fff3cd'; ?>; 
                                             color: <?php echo $order['order_status'] === 'completed' ? '#155724' : '#856404'; ?>;">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">
                                <p>Amount: <?php echo format_currency($order['total_amount']); ?></p>
                                <p>Payment: <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                <p>Date: <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>