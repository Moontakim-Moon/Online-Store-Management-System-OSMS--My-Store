<?php
session_start();
include "../includes/db.php";
include "../includes/functions.php";

if (!isset($_SESSION["user_id"]) || !$_SESSION["is_admin"]) {
    header("Location: admin_login.php");
    exit;
}

// Fetch comprehensive dashboard data
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
$activeProducts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$pendingOrders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = FALSE");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE status IN ('confirmed', 'delivered')");
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Recent orders
$stmt = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->query("SELECT * FROM products WHERE stock < 10 AND status = 'active' ORDER BY stock ASC LIMIT 5");
$lowStockProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - My Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> My Store Admin</h2>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="header-actions">
                    <a href="../index.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Store
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <div style="margin-bottom: 2rem;">
                    <h2 style="color: var(--admin-text); margin-bottom: 0.5rem;">Welcome back!</h2>
                    <p style="color: var(--admin-text-light);">Here's what's happening with your store.</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $totalProducts ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon confirmed">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $totalOrders ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $totalUsers ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?= number_format($totalRevenue, 2) ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="products.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </a>
                            <a href="orders.php" class="btn btn-success">
                                <i class="fas fa-eye"></i> View Orders (<?= $pendingOrders ?> pending)
                            </a>
                            <a href="users.php" class="btn btn-warning">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                            <a href="../pages/products.php" class="btn btn-outline" target="_blank">
                                <i class="fas fa-external-link-alt"></i> View Store
                            </a>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <!-- Recent Orders -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Orders</h3>
                            <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentOrders)): ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: var(--admin-text-light);">No orders yet</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>#<?= $order['order_number'] ?></td>
                                                <td><?= htmlspecialchars($order['username']) ?></td>
                                                <td>$<?= number_format($order['total'], 2) ?></td>
                                                <td>
                                                    <span class="badge <?= $order['status'] ?>">
                                                        <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Low Stock Alert</h3>
                            <a href="products.php" class="btn btn-sm btn-outline">Manage</a>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lowStockProducts)): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center; color: var(--admin-text-light);">All products well stocked</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lowStockProducts as $product): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($product['name']) ?></td>
                                                <td>
                                                    <span class="badge danger">
                                                        <?= $product['stock'] ?> left
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="products.php?action=edit&id=<?= $product['id'] ?>" class="action-btn edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        });

        // Mobile responsive
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>
</body>
</html>