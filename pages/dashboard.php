<?php
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();
$orders = getOrdersByUser($userId);

// Calculate summary data
$totalOrders = count($orders);
$pendingOrders = 0;
$totalSpent = 0.0;
$recentOrders = array_slice($orders, 0, 5); // Get only recent 5 orders

foreach ($orders as $order) {
    if (strtolower($order['status']) === 'pending') {
        $pendingOrders++;
    }
    if (in_array(strtolower($order['status']), ['paid', 'shipped', 'delivered'])) {
        $totalSpent += $order['total'];
    }
}

// Get user info
global $pdo;
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get cart items
$userCartItems = getCartItems($userId);
$totalCartItems = 0;
$totalCartPrice = 0.0;
$recentCartItems = array_slice($userCartItems, 0, 3); // Get only recent 3 cart items

foreach ($userCartItems as $item) {
    $totalCartItems += $item['quantity'];
    $totalCartPrice += $item['price'] * $item['quantity'];
}

// Get wishlist count
$wishlistCount = 0;
if (function_exists('getWishlistCount')) {
    $wishlistCount = getWishlistCount($userId);
}

// Get member since date
$memberSince = date('F Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/user-dashboard.css" rel="stylesheet">
</head>
<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-user-circle"></i> My Account</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                    <?php if ($wishlistCount > 0): ?>
                        <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist <span class="badge"><?= $wishlistCount ?></span></a></li>
                    <?php else: ?>
                        <li><a href="wishlist.php"><i class="far fa-heart"></i> Wishlist</a></li>
                    <?php endif; ?>
                    <li><a href="account.php"><i class="fas fa-user"></i> Account Details</a></li>
                    <li><a href="addresses.php"><i class="fas fa-address-book"></i> Addresses</a></li>
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
                        <i class="fas fa-external-link-alt"></i> Visit Store
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <!-- Welcome Section -->
                <div class="profile-section fade-in">
                    <?php
                    $profileImages = [
                        "https://i.pinimg.com/736x/f4/c7/1c/f4c71c4050c8b01d4ec39ab4185bd23a.jpg",
                        "https://i.pinimg.com/1200x/3a/63/e6/3a63e6e6de9a3b18239fbccc6ecd684a.jpg",
                        "https://i.pinimg.com/1200x/4f/44/b5/4f44b5604c68b01fe743ccda4c42d179.jpg"
                    ];
                    $randomImage = $profileImages[array_rand($profileImages)];
                    ?>
                    <img src="<?= $randomImage ?>" alt="Profile Picture" class="profile-avatar">
                    <div class="profile-info">
                        <h2>Welcome back, <?= htmlspecialchars($user['username']) ?>!</h2>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Member since <?= $memberSince ?></p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $totalOrders ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $pendingOrders ?></h3>
                            <p>Pending Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?= number_format($totalSpent, 2) ?></h3>
                            <p>Total Spent</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon info">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $totalCartItems ?></h3>
                            <p>Items in Cart</p>
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
                        <div class="card-body">
                            <?php if (empty($recentOrders)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-bag"></i>
                                    <h3>No Orders Yet</h3>
                                    <p>You haven't placed any orders yet.</p>
                                    <a href="../products.php" class="btn btn-primary" style="margin-top: 1rem;">Start Shopping</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $order): 
                                                $statusClass = 'status-' . strtolower($order['status']);
                                            ?>
                                                <tr>
                                                    <td><a href="order-details.php?id=<?= $order['id'] ?>">#<?= $order['order_number'] ?></a></td>
                                                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                                    <td><span class="<?= $statusClass ?>"><?= ucfirst($order['status']) ?></span></td>
                                                    <td>$<?= number_format($order['total'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Your Cart</h3>
                            <a href="cart.php" class="btn btn-sm btn-outline">View Cart</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentCartItems)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart"></i>
                                    <h3>Your Cart is Empty</h3>
                                    <p>Looks like you haven't added anything to your cart yet.</p>
                                    <a href="../products.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Products</a>
                                </div>
                            <?php else: ?>
                                <div class="cart-summary">
                                    <p><strong><?= $totalCartItems ?></strong> items in your cart</p>
                                    <p><strong>Total:</strong> $<?= number_format($totalCartPrice, 2) ?></p>
                                    <ul style="margin-top: 1rem; padding-left: 1rem;">
                                        <?php foreach ($recentCartItems as $item): ?>
                                            <li style="margin-bottom: 0.5rem;">
                                                <?= htmlspecialchars($item['name']) ?> 
                                                <span style="color: var(--text-light);">x<?= $item['quantity'] ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (count($userCartItems) > 3): ?>
                                            <li>...and <?= count($userCartItems) - 3 ?> more</li>
                                        <?php endif; ?>
                                    </ul>
                                    <div style="margin-top: 1.5rem;">
                                        <a href="cart.php" class="btn btn-primary" style="width: 100%;">
                                            <i class="fas fa-shopping-cart"></i> View Cart
                                        </a>
                                        <a href="checkout.php" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;">
                                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="../products.php" class="quick-action">
                                <i class="fas fa-shopping-bag"></i>
                                <div>
                                    <h3>Continue Shopping</h3>
                                    <p>Browse our latest products</p>
                                </div>
                            </a>
                            <a href="orders.php" class="quick-action">
                                <i class="fas fa-truck"></i>
                                <div>
                                    <h3>Track Order</h3>
                                    <p>Check your order status</p>
                                </div>
                            </a>
                            <a href="account.php" class="quick-action">
                                <i class="fas fa-user-edit"></i>
                                <div>
                                    <h3>Update Profile</h3>
                                    <p>Edit your account details</p>
                                </div>
                            </a>
                            <a href="settings.php" class="quick-action">
                                <i class="fas fa-cog"></i>
                                <div>
                                    <h3>Settings</h3>
                                    <p>Manage your preferences</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Add animation class to elements when they come into view
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (elementTop < windowHeight - 50) {
                    element.style.opacity = 1;
                    element.style.transform = 'translateY(0)';
                }
            });
        };

        // Initial check on page load
        document.addEventListener('DOMContentLoaded', () => {
            animateOnScroll();
        });

        // Check on scroll
        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>
