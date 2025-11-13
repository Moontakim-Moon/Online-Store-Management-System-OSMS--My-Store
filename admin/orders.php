<?php
session_start();
include "../includes/db.php";
include "../includes/functions.php";

// Check if user is admin
if (!isset($_SESSION["user_id"]) || !$_SESSION["is_admin"]) {
    header("Location: admin_login.php");
    exit;
}

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$order_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $id = intval($_POST['order_id']);
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$status, $id])) {
                $success = "Order status updated successfully!";
            }
        } catch (PDOException $e) {
            $error = "Failed to update order: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_order'])) {
        $id = intval($_POST['order_id']);
        try {
            // Delete order items first
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);
            
            // Delete order
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Order deleted successfully!";
            }
        } catch (PDOException $e) {
            $error = "Failed to delete order: " . $e->getMessage();
        }
    }
}

// Get order details for viewing
$order = null;
$order_items = [];
if ($action === 'view' && $order_id) {
    $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
    } else {
        $error = "Order not found!";
        $action = 'list';
    }
}

// Get all orders with pagination
$page = intval($_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(o.id LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_clause ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_clause");
$count_stmt->execute($params);
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Get order statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(total) as total_revenue
    FROM orders
");
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> Admin Panel</h2>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
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
                <h1><i class="fas fa-shopping-cart"></i> Order Management</h1>
                <div class="header-actions">
                    <a href="../index.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Store
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'view'): ?>
                    <!-- Order Details View -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Order #<?= $order['id'] ?></h2>
                            <a href="orders.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="order-details">
                                <div class="order-info-grid">
                                    <div class="info-group">
                                        <h3>Customer Information</h3>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? 'N/A') ?></p>
                                    </div>
                                    <div class="info-group">
                                        <h3>Order Information</h3>
                                        <p><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge badge-<?= strtolower($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </p>
                                        <p><strong>Total:</strong> $<?= number_format($order['total'], 2) ?></p>
                                    </div>
                                    <div class="info-group">
                                        <h3>Shipping Address</h3>
                                        <p><?= htmlspecialchars($order['address'] ?? 'N/A') ?></p>
                                        <p><?= htmlspecialchars($order['city'] ?? 'N/A') ?>, <?= htmlspecialchars($order['state'] ?? 'N/A') ?> <?= htmlspecialchars($order['zip'] ?? 'N/A') ?></p>
                                    </div>
                                </div>

                                <div class="order-items">
                                    <h3>Order Items</h3>
                                    <div class="table-responsive">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="product-info">
                                                                <?php if ($item['image']): ?>
                                                                    <img src="<?= htmlspecialchars($item['image']) ?>" 
                                                                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                                         class="product-thumbnail">
                                                                <?php endif; ?>
                                                                <span><?= htmlspecialchars($item['product_name']) ?></span>
                                                            </div>
                                                        </td>
                                                        <td>$<?= number_format($item['price'] ?? 0, 2) ?></td>
                                                        <td><?= $item['quantity'] ?></td>
                                                        <td>$<?= number_format(($item['price'] ?? 0) * $item['quantity'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="order-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Order Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= number_format($stats['total_orders']) ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= number_format($stats['pending_orders']) ?></h3>
                                <p>Pending Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon confirmed">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= number_format($stats['confirmed_orders']) ?></h3>
                                <p>Confirmed Orders</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3>$<?= number_format($stats['total_revenue'], 2) ?></h3>
                                <p>Total Revenue</p>
                            </div>
                        </div>
                    </div>

                    <!-- Orders List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Orders</h2>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter -->
                            <div class="filters">
                                <form method="GET" class="filter-form">
                                    <div class="filter-group">
                                        <input type="text" name="search" placeholder="Search orders..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                    <div class="filter-group">
                                        <select name="status">
                                            <option value="">All Status</option>
                                            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-outline">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="orders.php" class="btn btn-secondary">Clear</a>
                                </form>
                            </div>

                            <!-- Orders Table -->
                            <?php if (empty($orders)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart"></i>
                                    <h3>No orders found</h3>
                                    <p>No orders match your search criteria.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order_item): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?= $order_item['id'] ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="customer-info">
                                                            <strong><?= htmlspecialchars($order_item['username']) ?></strong>
                                                            <small><?= htmlspecialchars($order_item['email']) ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($order_item['created_at'])) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= strtolower($order_item['status']) ?>">
                                                            <?= ucfirst($order_item['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>$<?= number_format($order_item['total'], 2) ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="orders.php?action=view&id=<?= $order_item['id'] ?>" 
                                                               class="btn btn-sm btn-outline" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this order?')">
                                                                <input type="hidden" name="delete_order" value="1">
                                                                <input type="hidden" name="order_id" value="<?= $order_item['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                    <div class="pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" 
                                               class="btn btn-outline">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        
                                        <span class="page-info">
                                            Page <?= $page ?> of <?= $total_pages ?> (<?= $total_orders ?> orders)
                                        </span>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" 
                                               class="btn btn-outline">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>
