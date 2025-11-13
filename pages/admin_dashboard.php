<?php
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn() || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Fetch products, users, and orders for the dashboard
$products = getAllProducts();
$users = getUsers(); // Assuming a function to get all users
$orders = getAllOrders(); // Assuming a function to get all orders

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin_dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="admin-dashboard-container">
        <h1>Admin Dashboard</h1>
        <h2>Manage Products</h2>
        <button onclick="location.href='add_product.php'">Add Product</button>
        <h3>Product List</h3>
        <ul>
            <?php foreach ($products as $product): ?>
                <li>
                    <?= htmlspecialchars($product['name']) ?> 
                    <button onclick="location.href='delete_product.php?id=<?= $product['id'] ?>'">Delete</button>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Manage Users</h2>
        <h3>User List</h3>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <?= htmlspecialchars($user['username']) ?> 
                    <button onclick="location.href='delete_user.php?id=<?= $user['id'] ?>'">Delete</button>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Manage Orders</h2>
        <h3>Order List</h3>
        <ul>
            <?php foreach ($orders as $order): ?>
                <li>
                    Order #<?= htmlspecialchars($order['id']) ?> 
                    <button onclick="location.href='update_order.php?id=<?= $order['id'] ?>'">Update</button>
                    <button onclick="location.href='delete_order.php?id=<?= $order['id'] ?>'">Delete</button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
