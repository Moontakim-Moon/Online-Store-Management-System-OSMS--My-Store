<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();

// Handle add, update, remove actions
$action = $_GET['action'] ?? '';
$productId = $_GET['id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if ($action === 'add' && $productId) {
    addToCart($userId, $productId, (int)$quantity);
    header('Location: cart.php');
    exit;
}
if ($action === 'update' && isset($_POST['cart_item_id']) && isset($_POST['quantity'])) {
    $cartItemIds = $_POST['cart_item_id'];
    $quantities = $_POST['quantity'];
    foreach ($cartItemIds as $index => $cartItemId) {
        $qty = (int)$quantities[$index];
        updateCartItem($cartItemId, $qty);
    }
    header('Location: cart.php');
    exit;
}
if ($action === 'remove' && isset($_GET['cart_item_id'])) {
    $cartItemId = $_GET['cart_item_id'];
    removeCartItem($cartItemId);
    header('Location: cart.php');
    exit;
}

$cartItems = getCartItems($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <style>
        body {
            background: var(--gradient-primary);
            min-height: 100vh;
            padding: var(--spacing-xl);
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .cart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-header);
        }
        
        .cart-header {
            background: var(--gradient-header);
            color: var(--text-primary);
            padding: var(--spacing-3xl);
            text-align: center;
        }
        
        .cart-header h1 {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }
        
        .cart-header p {
            font-family: var(--font-accent);
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .cart-content {
            padding: var(--spacing-2xl);
        }
        
        .empty-cart {
            text-align: center;
            padding: var(--spacing-3xl) var(--spacing-xl);
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: var(--text-muted);
            margin-bottom: var(--spacing-lg);
            opacity: 0.5;
        }
        
        .empty-cart h3 {
            font-family: var(--font-heading);
            color: var(--text-primary);
            margin-bottom: var(--spacing-lg);
            font-size: 2rem;
        }
        
        .empty-cart p {
            font-family: var(--font-accent);
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: var(--spacing-xl);
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: var(--spacing-xl);
            background: var(--white);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }
        
        .cart-table th {
            background: var(--gradient-header);
            color: var(--text-primary);
            padding: var(--spacing-lg);
            font-weight: 600;
            font-family: var(--font-accent);
            text-align: left;
            font-size: 1.1rem;
        }
        
        .cart-table td {
            padding: var(--spacing-xl) var(--spacing-lg);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: var(--border-radius);
            object-fit: cover;
            border: 3px solid var(--primary);
            transition: var(--transition);
        }
        
        .product-image:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }
        
        .product-name {
            font-weight: 600;
            font-family: var(--font-accent);
            color: var(--text-primary);
            font-size: 1.1rem;
        }
        
        .quantity-input {
            width: 90px;
            padding: var(--spacing-sm);
            border: 2px solid var(--primary);
            border-radius: var(--border-radius);
            text-align: center;
            font-weight: 600;
            font-family: var(--font-accent);
            background: var(--white);
            color: var(--text-primary);
            transition: var(--transition-fast);
        }
        
        .quantity-input:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(244, 208, 63, 0.2);
            transform: translateY(-1px);
        }
        
        .remove-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-family: var(--font-accent);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .remove-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .remove-btn:hover::before {
            left: 100%;
        }
        
        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        
        .cart-total {
            background: var(--gradient-header);
            color: var(--text-primary);
            font-weight: 700;
            font-family: var(--font-heading);
            font-size: 1.3rem;
        }
        
        .cart-actions {
            display: flex;
            gap: var(--spacing-lg);
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .cart-btn {
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
            justify-content: center;
            gap: var(--spacing-sm);
            min-height: 56px;
            position: relative;
            overflow: hidden;
        }
        
        .cart-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .cart-btn:hover::before {
            left: 100%;
        }
        
        .cart-btn-primary {
            background: var(--gradient-button);
            color: var(--text-primary);
            box-shadow: var(--shadow-md);
        }
        
        .cart-btn-secondary {
            background: var(--white);
            color: var(--primary-dark);
            border: 2px solid var(--primary);
            box-shadow: var(--shadow-sm);
        }
        
        .cart-btn-secondary:hover {
            background: var(--primary);
            color: var(--text-primary);
        }
        
        .cart-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
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
            .cart-table {
                font-size: 0.9rem;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .cart-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                justify-content: center;
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
            <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
            <p>Review your items before checkout</p>
        </div>
        
        <div class="cart-content">
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Start shopping to add items to your cart</p>
                    <a href="products.php" class="cart-btn cart-btn-primary">
                        <i class="fas fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <form method="post" action="cart.php?action=update">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($cartItems as $item):
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="../<?= htmlspecialchars($item['image']) ?>" alt="Product" class="product-image">
                                        <?php else: ?>
                                            <div class="product-image" style="background: var(--primary); display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: var(--text-primary);"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="product-name"><?= htmlspecialchars($item['name']) ?></span>
                                    </div>
                                </td>
                                <td><strong>$<?= number_format($item['price'], 2) ?></strong></td>
                                <td>
                                    <input type="number" name="quantity[]" value="<?= $item['quantity'] ?>" min="1" required class="quantity-input">
                                    <input type="hidden" name="cart_item_id[]" value="<?= $item['id'] ?>">
                                </td>
                                <td><strong>$<?= number_format($subtotal, 2) ?></strong></td>
                                <td>
                                    <button type="button" class="remove-btn" onclick="if(confirm('Remove this item?')) window.location.href='cart.php?action=remove&cart_item_id=<?= $item['id'] ?>'">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="cart-total">
                                <td colspan="3">Total Amount:</td>
                                <td colspan="2">$<?= number_format($total, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <button type="submit" class="cart-btn cart-btn-secondary">
                            <i class="fas fa-sync-alt"></i> Update Cart
                        </button>
                        <a href="checkout.php" class="cart-btn cart-btn-primary">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
