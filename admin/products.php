<?php
$page_title = 'Manage Products';
require_once 'functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Create uploads directory if it doesn't exist
$upload_dir = '../assets/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle product deletion
if ($action === 'delete' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    $query = "UPDATE products SET status = 'inactive' WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    
    if ($stmt->execute()) {
        $success = 'Product deleted successfully';
        $action = 'list';
    } else {
        $error = 'Failed to delete product';
    }
}

// Handle product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $category_id = (int)$_POST['category_id'];
    $brand = sanitize_input($_POST['brand']);
    $compatibility = sanitize_input($_POST['compatibility']);
    
    if (empty($name) || empty($description) || $price <= 0 || $stock_quantity < 0 || $category_id <= 0) {
        $error = 'Please fill in all required fields correctly';
    } else {
        // Handle image upload
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Image uploaded successfully
                } else {
                    $error = 'Failed to upload image';
                    $image_name = null;
                }
            } else {
                $error = 'Invalid image type. Please upload JPG, PNG, or GIF files only.';
            }
        }
        
        if (empty($error)) {
            if ($action === 'add') {
                // Add new product
                $query = "INSERT INTO products (name, description, price, stock_quantity, category_id, brand, compatibility, image) 
                          VALUES (:name, :description, :price, :stock_quantity, :category_id, :brand, :compatibility, :image)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':stock_quantity', $stock_quantity);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':brand', $brand);
                $stmt->bindParam(':compatibility', $compatibility);
                $stmt->bindParam(':image', $image_name);
                
                if ($stmt->execute()) {
                    $success = 'Product added successfully';
                    $action = 'list';
                } else {
                    $error = 'Failed to add product';
                }
            } else {
                // Edit existing product
                $product_id = (int)$_POST['product_id'];
                
                if ($image_name) {
                    $query = "UPDATE products SET name = :name, description = :description, price = :price, 
                              stock_quantity = :stock_quantity, category_id = :category_id, brand = :brand, 
                              compatibility = :compatibility, image = :image WHERE id = :id";
                } else {
                    $query = "UPDATE products SET name = :name, description = :description, price = :price, 
                              stock_quantity = :stock_quantity, category_id = :category_id, brand = :brand, 
                              compatibility = :compatibility WHERE id = :id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':stock_quantity', $stock_quantity);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':brand', $brand);
                $stmt->bindParam(':compatibility', $compatibility);
                $stmt->bindParam(':id', $product_id);
                
                if ($image_name) {
                    $stmt->bindParam(':image', $image_name);
                }
                
                if ($stmt->execute()) {
                    $success = 'Product updated successfully';
                    $action = 'list';
                } else {
                    $error = 'Failed to update product';
                }
            }
        }
    }
}

// Get product data for editing
$product = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $product = get_product_by_id($product_id);
    
    if (!$product) {
        $error = 'Product not found';
        $action = 'list';
    }
}

// Get all categories
$categories = get_categories();

// Get all products for list view
$products = [];
if ($action === 'list') {
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active' 
              ORDER BY p.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
            <li><a href="products.php" class="active"><i class="fas fa-gamepad"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="../index.php"><i class="fas fa-home"></i> View Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <?php if ($action === 'list'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Manage Products</h1>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
            
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
            
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 4rem; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                    <i class="fas fa-gamepad" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>No products found</h3>
                    <p>Start by adding your first product.</p>
                    <a href="?action=add" class="btn btn-primary">Add New Product</a>
                </div>
            <?php else: ?>
                <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">ID</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Image</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Name</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Price</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Stock</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Category</th>
                                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #dee2e6;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo $product['id']; ?></td>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                            <img src="<?php echo $product['image'] ? '../assets/uploads/' . $product['image'] : '/placeholder.svg?height=50&width=50'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        </td>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo format_currency($product['price']); ?></td>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                            <?php if ($product['stock_quantity'] > 0): ?>
                                                <span style="color: #2ed573;"><?php echo $product['stock_quantity']; ?></span>
                                            <?php else: ?>
                                                <span style="color: #ff4757;">Out of stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;"><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td style="padding: 1rem; border-bottom: 1px solid #dee2e6;">
                                            <a href="?action=edit&id=<?php echo $product['id']; ?>" class="btn" style="background: #17a2b8; padding: 0.25rem 0.5rem; margin-right: 0.5rem;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem;"
                                               onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Add/Edit Product Form -->
            <h1><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h1>
            
            <?php if ($error): ?>
                <div style="background: #ff4757; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?php echo $action === 'edit' ? htmlspecialchars($product['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required><?php echo $action === 'edit' ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="price">Price (TSh) *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required 
                                   value="<?php echo $action === 'edit' ? $product['price'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity *</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" min="0" required 
                                   value="<?php echo $action === 'edit' ? $product['stock_quantity'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($action === 'edit' && $product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input type="text" id="brand" name="brand" class="form-control" 
                                   value="<?php echo $action === 'edit' ? htmlspecialchars($product['brand']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="compatibility">Compatibility</label>
                            <input type="text" id="compatibility" name="compatibility" class="form-control" 
                                   value="<?php echo $action === 'edit' ? htmlspecialchars($product['compatibility']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
                        <small style="color: #666;">Recommended size: 800x800 pixels. Max size: 5MB. Allowed: JPG, PNG, GIF</small>
                    </div>
                    
                    <?php if ($action === 'edit' && $product['image']): ?>
                        <div style="margin-bottom: 1rem;">
                            <p>Current Image:</p>
                            <img src="../assets/uploads/<?php echo $product['image']; ?>" alt="Current Image" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-bottom: 1rem;">
                        <img id="image-preview" src="#" alt="Image Preview" 
                             style="max-width: 200px; max-height: 200px; border-radius: 5px; display: none;">
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                        </button>
                        <a href="products.php" class="btn" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>