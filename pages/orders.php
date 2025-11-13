<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../includes/config.php';
require_once '../includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();
$selectedOrderId = $_GET['order_id'] ?? null;

// Get orders for user
$orders = getOrdersByUser($userId);

// Get selected order details if order_id is provided
$selectedOrder = null;
$orderItems = [];
if ($selectedOrderId) {
    // Verify order belongs to user
    foreach ($orders as $order) {
        if ($order['id'] == $selectedOrderId) {
            $selectedOrder = $order;
            break;
        }
    }
    
    if ($selectedOrder) {
        $orderItems = getOrderItems($selectedOrderId);
    } else {
        $_SESSION['error_message'] = 'Order not found or access denied.';
        header('Location: orders.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $selectedOrder ? 'Order Details - ' . $selectedOrder['order_number'] : 'My Orders' ?> - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <style>
        body {
            background: var(--gradient-primary);
            min-height: 100vh;
            padding: var(--spacing-xl);
        }
        
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .nav-bar {
            background: var(--bg-overlay);
            backdrop-filter: blur(20px);
            padding: var(--spacing-lg) var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }
        
        .nav-links {
            display: flex;
            gap: var(--spacing-xl);
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-family: var(--font-accent);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }
        
        .nav-links a:hover {
            background: var(--primary-light);
            color: var(--text-primary);
            transform: translateY(-2px);
        }
        
        .page-header {
            background: var(--gradient-header);
            color: var(--text-primary);
            padding: var(--spacing-3xl);
            text-align: center;
        }
        
        .page-header h1 {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }
        
        .content {
            padding: var(--spacing-3xl);
        }
        
        .orders-grid {
            display: grid;
            gap: var(--spacing-xl);
        }
        
        .order-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }
        
        .order-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            font-family: var(--font-heading);
        }
        
        .order-status {
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .order-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-xs);
        }
        
        .meta-value {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .btn {
            padding: var(--spacing-md) var(--spacing-lg);
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-family: var(--font-accent);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            justify-content: center;
        }
        
        .btn-primary {
            background: var(--gradient-button);
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background: var(--white);
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: var(--text-primary);
        }
        
        .order-items {
            background: var(--light-gray);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            margin-top: var(--spacing-xl);
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .item-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius);
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .item-details h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--text-primary);
        }
        
        .item-details p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .item-price {
            text-align: right;
        }
        
        .unit-price {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .total-price {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        
        .order-total {
            background: var(--primary-light);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius);
            margin-top: var(--spacing-lg);
            text-align: right;
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: var(--spacing-lg);
            color: var(--primary);
        }
        
        .alert {
            padding: var(--spacing-lg);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-meta {
                grid-template-columns: 1fr 1fr;
            }
            
            .item-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <div class="nav-bar">
        <div class="nav-links">
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="products.php"><i class="fas fa-shopping-bag"></i> Products</a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="orders-container">
        <div class="page-header">
            <h1>
                <i class="fas fa-list-alt"></i> 
                <?= $selectedOrder ? 'Order Details' : 'My Orders' ?>
            </h1>
            <?php if ($selectedOrder): ?>
                <p>Order #<?= htmlspecialchars($selectedOrder['order_number'] ?? $selectedOrder['id']) ?></p>
            <?php else: ?>
                <p>Track and manage your orders</p>
            <?php endif; ?>
        </div>
        
        <div class="content">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if ($selectedOrder): ?>
                <!-- Order Details View -->
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">
                            Order #<?= htmlspecialchars($selectedOrder['order_number'] ?? $selectedOrder['id']) ?>
                        </div>
                        <div class="order-status status-<?= $selectedOrder['status'] ?? 'pending' ?>">
                            <?= ucfirst($selectedOrder['status'] ?? 'pending') ?>
                        </div>
                    </div>
                    
                    <div class="order-meta">
                        <div class="meta-item">
                            <div class="meta-label">Order Date</div>
                            <div class="meta-value"><?= date('F j, Y', strtotime($selectedOrder['created_at'])) ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Total Amount</div>
                            <div class="meta-value">$<?= number_format($selectedOrder['total'], 2) ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Payment Method</div>
                            <div class="meta-value"><?= htmlspecialchars($selectedOrder['payment_method'] ?? 'Cash on Delivery') ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Items</div>
                            <div class="meta-value"><?= count($orderItems) ?> items</div>
                        </div>
                    </div>
                    
                    <?php if (!empty($orderItems)): ?>
                        <div class="order-items">
                            <h3><i class="fas fa-box"></i> Order Items</h3>
                            <?php foreach ($orderItems as $item): ?>
                                <div class="item-row">
                                    <div class="item-info">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="../<?= htmlspecialchars($item['image']) ?>" alt="Product" class="item-image">
                                        <?php else: ?>
                                            <div class="item-image" style="background: var(--primary); display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: white;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <h4><?= htmlspecialchars($item['product_name'] ?? $item['name']) ?></h4>
                                            <p>Quantity: <?= $item['quantity'] ?></p>
                                        </div>
                                    </div>
                                    <div class="item-price">
                                        <div class="unit-price">$<?= number_format($item['unit_price'] ?? $item['price'], 2) ?> each</div>
                                        <div class="total-price">$<?= number_format(($item['unit_price'] ?? $item['price']) * $item['quantity'], 2) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="order-total">
                                <strong>Total: $<?= number_format($selectedOrder['total'], 2) ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: var(--spacing-xl); display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        <?php if (($selectedOrder['status'] ?? 'pending') === 'pending'): ?>
                            <button class="btn btn-primary" onclick="alert('Order cancellation feature coming soon!')">
                                <i class="fas fa-times"></i> Cancel Order
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Orders List View -->
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No Orders Yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="orders-grid">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-number">
                                        Order #<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?>
                                    </div>
                                    <div class="order-status status-<?= $order['status'] ?? 'pending' ?>">
                                        <?= ucfirst($order['status'] ?? 'pending') ?>
                                    </div>
                                </div>
                                
                                <div class="order-meta">
                                    <div class="meta-item">
                                        <div class="meta-label">Date</div>
                                        <div class="meta-value"><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
                                    </div>
                                    <div class="meta-item">
                                        <div class="meta-label">Total</div>
                                        <div class="meta-value">$<?= number_format($order['total'], 2) ?></div>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap;">
                                    <a href="orders.php?order_id=<?= $order['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <?php if (($order['status'] ?? 'pending') === 'delivered'): ?>
                                        <button class="btn btn-secondary" onclick="alert('Reorder feature coming soon!')">
                                            <i class="fas fa-redo"></i> Reorder
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
