<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "gaming_controllers_tz");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_status, $new_status, $order_id);
    
    if ($stmt->execute()) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Error updating order status: " . $conn->error;
    }
    $stmt->close();
}

// Fetch orders with user and item details
$sql = "SELECT o.id, o.user_id, o.total_amount, o.payment_method, o.payment_status, 
        o.order_status, o.shipping_address, o.phone, o.created_at, o.updated_at, 
        o.status, u.full_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            min-height: 100vh;
        }
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 #e5e7eb;
        }
        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .table-container::-webkit-scrollbar-track {
            background: #e5e7eb;
        }
        .table-container::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 4px;
        }
        .status-select {
            transition: all 0.3s ease;
        }
        .status-select:hover {
            transform: scale(1.05);
        }
        .update-btn {
            transition: all 0.3s ease;
        }
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header-nav a, .footer-nav a {
            transition: all 0.3s ease;
        }
        .header-nav a:hover, .footer-nav a:hover {
            color: #f97316;
            transform: translateY(-2px);
        }
    </style>
</head><?php

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "gaming_controllers_tz");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_status, $new_status, $order_id);
    
    if ($stmt->execute()) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Error updating order status: " . $conn->error;
    }
    $stmt->close();
}

// Fetch orders with user and item details
$sql = "SELECT o.id, o.user_id, o.total_amount, o.payment_method, o.payment_status, 
        o.order_status, o.shipping_address, o.phone, o.created_at, o.updated_at, 
        o.status, u.full_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            min-height: 100vh;
        }
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 #e5e7eb;
        }
        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .table-container::-webkit-scrollbar-track {
            background: #e5e7eb;
        }
        .table-container::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 4px;
        }
        .status-select {
            transition: all 0.3s ease;
        }
        .status-select:hover {
            transform: scale(1.05);
        }
        .update-btn {
            transition: all 0.3s ease;
        }
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Include your header template -->

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-white mb-6 text-center">Manage Orders</h1>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow-xl rounded-lg table-container">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Total (TZS)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Payment Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Order Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Shipping Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($row['full_name']); ?><br>
                                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($row['email']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($row['total_amount'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['payment_method']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded text-xs <?php echo $row['payment_status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $row['payment_status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded text-xs <?php echo $row['order_status'] == 'delivered' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $row['order_status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($row['shipping_address']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['phone']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['created_at']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" class="inline-flex items-center space-x-2">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <select name="order_status" class="status-select border rounded p-2 bg-gray-100 focus:ring-2 focus:ring-blue-500">
                                        <option value="pending" <?php echo $row['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $row['order_status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $row['order_status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $row['order_status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $row['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="update-btn bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include your footer template -->
    <?php include '../templates/footer.php'; ?>

</body>
</html>
