<?php
session_start();
$page_title = 'Login';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}

require_once 'templates/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Login to Your Account</h2>
        
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
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p>Don't have an account? <a href="register.php" style="color: #667eea;">Register here</a></p>
        </div>
        
        <!-- Demo credentials -->
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 2rem;">
            <h4>Demo Credentials:</h4>
            <p><strong>Admin:</strong> admin@gamingtz.com / password</p>
            <p><strong>Customer:</strong> Register a new account</p>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>