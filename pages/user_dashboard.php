<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();

// Get user information
try {
    require_once '../includes/db.php';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // Get user's recent orders (if orders table exists)
    $orders = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        // Orders table might not exist yet
    }
    
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <style>
        .dashboard-wrapper {
            min-height: 100vh;
            background: var(--gradient-primary);
        }
        
        .dashboard-nav {
            background: var(--bg-overlay);
            backdrop-filter: blur(20px);
            padding: var(--spacing-lg) var(--spacing-xl);
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        
        .nav-brand h2 {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            margin: 0;
        }
        
        .nav-brand i {
            color: var(--primary);
            margin-right: var(--spacing-sm);
            font-size: 2rem;
        }
        
        .nav-links {
            display: flex;
            gap: var(--spacing-lg);
            align-items: center;
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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: var(--spacing-3xl);
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            padding: var(--spacing-3xl);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-header);
        }
        
        .welcome-section h1 {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }
        
        .welcome-section i {
            color: var(--primary);
            font-size: 2.5rem;
        }
        
        .welcome-text {
            font-size: 1.2rem;
            color: var(--text-secondary);
            font-family: var(--font-accent);
            margin: 0;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: var(--spacing-2xl);
            margin-bottom: var(--spacing-2xl);
        }
        
        .dashboard-card {
            background: var(--bg-card);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-header);
        }
        
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .dashboard-card.full-width {
            grid-column: 1 / -1;
        }
        
        .card-header {
            background: var(--gradient-header);
            color: var(--text-primary);
            padding: var(--spacing-xl);
            font-weight: 600;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-family: var(--font-heading);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .card-content {
            padding: var(--spacing-xl);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .label {
            font-weight: 600;
            color: var(--text-primary);
            font-family: var(--font-accent);
        }
        
        .value {
            color: var(--text-secondary);
        }
        
        .status-active {
            color: #27ae60 !important;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md) var(--spacing-lg);
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-family: var(--font-accent);
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .action-btn:hover::before {
            left: 100%;
        }
        
        .action-btn.primary {
            background: var(--gradient-button);
            color: var(--text-primary);
            box-shadow: var(--shadow-md);
        }
        
        .action-btn.secondary {
            background: var(--white);
            color: var(--primary-dark);
            border: 2px solid var(--primary);
            box-shadow: var(--shadow-sm);
        }
        
        .action-btn.tertiary {
            background: var(--secondary);
            color: var(--white);
            box-shadow: var(--shadow-md);
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .action-btn.secondary:hover {
            background: var(--primary);
            color: var(--text-primary);
        }
        
        .orders-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-md) 0;
            border-bottom: 1px solid var(--border-color);
            border-left: 4px solid var(--primary);
            padding-left: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            background: var(--light-gray);
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            transition: var(--transition-fast);
        }
        
        .order-item:hover {
            background: var(--primary-light);
            transform: translateX(4px);
        }
        
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .order-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xs);
        }
        
        .order-id {
            font-weight: 600;
            color: var(--text-primary);
            font-family: var(--font-accent);
        }
        
        .order-date {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .status-badge {
            background: var(--gradient-button);
            color: var(--text-primary);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: var(--font-accent);
        }
        
        .no-orders {
            text-align: center;
            padding: var(--spacing-3xl) var(--spacing-md);
            color: var(--text-secondary);
        }
        
        .no-orders i {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: var(--spacing-lg);
            opacity: 0.5;
        }
        
        .no-orders p {
            margin-bottom: var(--spacing-xl);
            font-size: 1.1rem;
        }
        
        .shop-now-btn {
            display: inline-block;
            background: var(--gradient-button);
            color: var(--text-primary);
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-family: var(--font-accent);
            transition: var(--transition);
            box-shadow: var(--shadow-md);
        }
        
        .shop-now-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: var(--text-primary);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: var(--spacing-md);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-xl);
            border: 1px solid #f5c6cb;
            font-family: var(--font-accent);
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: var(--spacing-lg);
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-lg);
            }
            
            .nav-links {
                flex-wrap: wrap;
                gap: var(--spacing-sm);
            }
            
            .welcome-section {
                padding: var(--spacing-xl);
            }
            
            .welcome-section h1 {
                font-size: 2.5rem;
            }
            
            .action-buttons {
                gap: var(--spacing-sm);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Navigation Header -->
        <nav class="dashboard-nav">
            <div class="nav-brand">
                <h2><i class="fas fa-store"></i> <?= SITE_NAME ?></h2>
            </div>
            <div class="nav-links">
                <a href="../index.php"><i class="fas fa-home"></i> Home</a>
                <a href="products.php"><i class="fas fa-shopping-bag"></i> Products</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <div class="dashboard-container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1><i class="fas fa-user-circle"></i> Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
                <p class="welcome-text">Manage your account and view your activity from your personal dashboard.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Account Info Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Account Information</h3>
                    </div>
                    <div class="card-content">
                        <div class="info-row">
                            <span class="label">Username:</span>
                            <span class="value"><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Email:</span>
                            <span class="value"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Member Since:</span>
                            <span class="value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Account Status:</span>
                            <span class="value status-active">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div class="action-buttons">
                            <a href="products.php" class="action-btn primary">
                                <i class="fas fa-shopping-bag"></i>
                                Browse Products
                            </a>
                            <a href="cart.php" class="action-btn secondary">
                                <i class="fas fa-shopping-cart"></i>
                                View Cart
                            </a>
                            <a href="account.php" class="action-btn tertiary">
                                <i class="fas fa-cog"></i>
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Card -->
                <div class="dashboard-card full-width">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($orders)): ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <span class="order-id">Order #<?= $order['id'] ?></span>
                                            <span class="order-date"><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                                        </div>
                                        <div class="order-status">
                                            <span class="status-badge"><?= ucfirst($order['status']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-orders">
                                <i class="fas fa-shopping-bag"></i>
                                <p>No recent orders found.</p>
                                <a href="products.php" class="shop-now-btn">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
