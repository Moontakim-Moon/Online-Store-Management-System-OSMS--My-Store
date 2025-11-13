<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/email_functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();
$error = '';
$success = '';
$order_id = 0;
$step = $_GET['step'] ?? 'cart_review';

// Get cart items for review
$cartItems = getCartItems($userId);
if (empty($cartItems) && $step === 'cart_review') {
    header('Location: cart.php');
    exit;
}

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'place_order') {
        // Create order without OTP for now (simplified)
        try {
            // First, ensure the order_items table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(10,2) NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Check if orders table has required columns and add them if missing
            try {
                $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_number VARCHAR(50) UNIQUE AFTER id");
            } catch (Exception $e) {
                // Column might already exist
            }
            
            try {
                $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'Cash on Delivery'");
            } catch (Exception $e) {
                // Column might already exist
            }
            
            try {
                $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0.00");
            } catch (Exception $e) {
                // Column might already exist
            }
            
            $orderNumber = 'ORD-' . date('Y') . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-' . time();
            
            // Try to insert with all columns, fall back to basic if needed
            try {
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total, subtotal, status, payment_method, created_at) VALUES (?, ?, ?, ?, 'pending', 'Cash on Delivery', NOW())");
                $stmt->execute([$userId, $orderNumber, $total, $total]);
            } catch (Exception $e) {
                // Fallback to basic order insertion
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'pending', NOW())");
                $stmt->execute([$userId, $total]);
            }
            
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$order_id, $item['id'], $item['name'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);
                } catch (Exception $e) {
                    // If order_items table doesn't work, log the error but continue
                    error_log("Order items insertion failed: " . $e->getMessage());
                }
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Send order confirmation email
            sendOrderConfirmationEmail($userId, $order_id, $orderNumber, $total, $cartItems);
            
            $success = "Order placed successfully! Order Number: $orderNumber";
            $step = 'success';
            
            // Store order details for success page
            $_SESSION['last_order'] = [
                'id' => $order_id,
                'number' => $orderNumber,
                'total' => $total,
                'items' => $cartItems
            ];
        } catch (Exception $e) {
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <style>
        body {
            background: var(--gradient-primary);
            min-height: 100vh;
            padding: var(--spacing-xl);
        }
        
        .checkout-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .checkout-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-header);
        }
        
        .checkout-header {
            background: var(--gradient-header);
            color: var(--text-primary);
            padding: var(--spacing-3xl);
            text-align: center;
        }
        
        .checkout-header h1 {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }
        
        .checkout-header p {
            font-family: var(--font-accent);
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .checkout-content {
            padding: var(--spacing-2xl);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: var(--spacing-2xl);
            gap: var(--spacing-lg);
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: 25px;
            background: var(--light-gray);
            color: var(--text-secondary);
            font-weight: 500;
            font-family: var(--font-accent);
            transition: var(--transition);
            border: 2px solid transparent;
        }
        
        .step.active {
            background: var(--gradient-button);
            color: var(--text-primary);
            border-color: var(--primary);
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }
        
        .step.completed {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border-color: #27ae60;
        }
        
        .order-summary {
            background: var(--light-gray);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-lg) 0;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition-fast);
        }
        
        .order-item:hover {
            background: rgba(244, 208, 63, 0.05);
            padding-left: var(--spacing-sm);
            padding-right: var(--spacing-sm);
            border-radius: var(--border-radius-sm);
        }
        
        .order-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-family: var(--font-heading);
            font-size: 1.3rem;
            color: var(--text-primary);
            background: var(--primary-light);
            padding: var(--spacing-lg);
            border-radius: var(--border-radius);
            margin-top: var(--spacing-md);
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius);
            object-fit: cover;
            border: 3px solid var(--primary);
            transition: var(--transition);
        }
        
        .product-image:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-md);
        }
        
        .checkout-btn {
            padding: var(--spacing-lg) var(--spacing-2xl);
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
            min-height: 56px;
            position: relative;
            overflow: hidden;
        }
        
        .checkout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .checkout-btn:hover::before {
            left: 100%;
        }
        
        .checkout-btn-primary {
            background: var(--gradient-button);
            color: var(--text-primary);
            box-shadow: var(--shadow-md);
        }
        
        .checkout-btn-secondary {
            background: var(--white);
            color: var(--primary-dark);
            border: 2px solid var(--primary);
            box-shadow: var(--shadow-sm);
        }
        
        .checkout-btn-secondary:hover {
            background: var(--primary);
            color: var(--text-primary);
        }
        
        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .alert {
            padding: var(--spacing-lg);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-family: var(--font-accent);
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .success-animation {
            text-align: center;
            padding: var(--spacing-3xl);
        }
        
        .success-icon {
            font-size: 5rem;
            color: #27ae60;
            margin-bottom: var(--spacing-lg);
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
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
        
        @media (max-width: 768px) {
            .step-indicator {
                flex-direction: column;
                align-items: center;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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

    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-credit-card"></i> Checkout</h1>
            <p>Complete your order securely</p>
        </div>
        
        <div class="checkout-content">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?= $step === 'cart_review' ? 'active' : ($step === 'place_order' || $step === 'success' ? 'completed' : '') ?>">
                    <i class="fas fa-shopping-cart"></i> Review Cart
                </div>
                <div class="step <?= $step === 'place_order' ? 'active' : ($step === 'success' ? 'completed' : '') ?>">
                    <i class="fas fa-credit-card"></i> Place Order
                </div>
                <div class="step <?= $step === 'success' ? 'active' : '' ?>">
                    <i class="fas fa-check-circle"></i> Complete
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 'cart_review'): ?>
                <!-- Cart Review Step -->
                <div class="order-summary">
                    <h3 style="margin-bottom: 1rem; color: #333;"><i class="fas fa-list"></i> Order Summary</h3>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="product-info">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="../<?= htmlspecialchars($item['image']) ?>" alt="Product" class="product-image">
                                <?php else: ?>
                                    <div class="product-image" style="background: var(--primary); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: var(--text-primary);"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($item['name']) ?></div>
                                    <div style="color: #666; font-size: 0.9rem;">Qty: <?= $item['quantity'] ?></div>
                                </div>
                            </div>
                            <div style="font-weight: 600;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                    <div class="order-item">
                        <div>Total Amount:</div>
                        <div>$<?= number_format($total, 2) ?></div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: space-between; flex-wrap: wrap;">
                    <a href="cart.php" class="checkout-btn checkout-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                    <a href="checkout.php?step=place_order" class="checkout-btn checkout-btn-primary">
                        <i class="fas fa-arrow-right"></i> Continue to Payment
                    </a>
                </div>
                
            <?php elseif ($step === 'place_order'): ?>
                <!-- Place Order Step -->
                <div class="order-summary">
                    <h3 style="margin-bottom: 1rem; color: #333;"><i class="fas fa-credit-card"></i> Confirm Your Order</h3>
                    <div class="order-item">
                        <div>Total Items:</div>
                        <div><?= count($cartItems) ?> items</div>
                    </div>
                    <div class="order-item">
                        <div>Total Amount:</div>
                        <div>$<?= number_format($total, 2) ?></div>
                    </div>
                </div>
                
                <form method="post" action="checkout.php?step=place_order">
                    <div style="display: flex; gap: 1rem; justify-content: space-between; flex-wrap: wrap;">
                        <a href="checkout.php?step=cart_review" class="checkout-btn checkout-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Review
                        </a>
                        <button type="submit" name="place_order" class="checkout-btn checkout-btn-primary">
                            <i class="fas fa-credit-card"></i> Place Order
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 'success'): ?>
                <!-- Success Step -->
                <div class="success-animation">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 style="color: #27ae60; margin-bottom: 1rem;">Order Placed Successfully!</h2>
                    <p style="color: #666; margin-bottom: 1rem;">Thank you for your purchase. Your order has been confirmed.</p>
                    
                    <?php if (isset($_SESSION['last_order'])): ?>
                        <div class="order-confirmation-details" style="background: var(--light-gray); padding: var(--spacing-xl); border-radius: var(--border-radius-lg); margin: var(--spacing-xl) 0; text-align: left;">
                            <h3 style="color: var(--text-primary); margin-bottom: var(--spacing-lg);"><i class="fas fa-receipt"></i> Order Details</h3>
                            <div class="order-info" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
                                <div><strong>Order Number:</strong> <?= htmlspecialchars($_SESSION['last_order']['number']) ?></div>
                                <div><strong>Total Amount:</strong> $<?= number_format($_SESSION['last_order']['total'], 2) ?></div>
                            </div>
                            
                            <h4 style="margin-bottom: var(--spacing-md);">Items Ordered:</h4>
                            <div class="ordered-items">
                                <?php foreach ($_SESSION['last_order']['items'] as $item): ?>
                                    <div class="order-item-detail" style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border-color);">
                                        <div>
                                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            <span style="color: var(--text-secondary);"> × <?= $item['quantity'] ?></span>
                                        </div>
                                        <div>$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div style="background: var(--primary-light); padding: var(--spacing-md); border-radius: var(--border-radius); margin-top: var(--spacing-md);">
                                <p style="margin: 0; color: var(--text-primary);"><i class="fas fa-info-circle"></i> A confirmation email has been sent to your registered email address.</p>
                            </div>
                        </div>
                        <?php unset($_SESSION['last_order']); ?>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="orders.php" class="checkout-btn checkout-btn-primary">
                            <i class="fas fa-list"></i> View My Orders
                        </a>
                        <a href="products.php" class="checkout-btn checkout-btn-secondary">
                            <i class="fas fa-shopping-bag"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
