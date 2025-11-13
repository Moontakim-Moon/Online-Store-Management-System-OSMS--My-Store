<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../includes/db.php';
require_once '../includes/review_system.php';
require_once '../includes/config.php';

$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: products.php');
    exit;
}

$product = getProductById($productId);
if (!$product) {
    $_SESSION['error_message'] = 'Product not found.';
    header('Location: products.php');
    exit;
}

// Check if product is available
if (isset($product['status']) && $product['status'] !== 'active') {
    $_SESSION['error_message'] = 'This product is currently unavailable.';
    header('Location: products.php');
    exit;
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
    
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    // Validate quantity
    if ($quantity < 1) {
        $error_message = 'Please enter a valid quantity.';
    } elseif (isset($product['stock']) && $product['stock'] > 0 && $quantity > $product['stock']) {
        $error_message = 'Sorry, only ' . $product['stock'] . ' items available in stock.';
    } else {
        // Check if product is still available
        $currentProduct = getProductById($productId);
        if (!$currentProduct) {
            $error_message = 'Product is no longer available.';
        } elseif (addToCart(currentUserId(), $productId, $quantity)) {
            $success_message = 'Product added to cart successfully!';
            // Redirect to prevent form resubmission
            header('Location: product_detail.php?id=' . $productId . '&added=1');
            exit;
        } else {
            $error_message = 'Failed to add product to cart. Please try again.';
        }
    }
}

// Check for success message from redirect
if (isset($_GET['added']) && $_GET['added'] == '1') {
    $success_message = 'Product added to cart successfully!';
}

// Handle review submission
$review_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        $review_error = 'You must be logged in to submit a review.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $review_text = trim($_POST['review_text'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $review_error = 'Rating must be between 1 and 5.';
        } else {
            add_product_review($pdo, $productId, currentUserId(), $rating, $review_text);
            header('Location: product_detail.php?id=' . $productId);
            exit;
        }
    }
}

// Fetch reviews and rating info
$reviews = get_product_reviews($pdo, $productId);
$ratingInfo = ReviewSystem::getProductRating($pdo, $productId);
$averageRating = $ratingInfo['avg_rating'] ?? 0;
$totalReviews = $ratingInfo['total_reviews'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - <?= SITE_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <style>
        body {
            background: var(--gradient-primary);
            min-height: 100vh;
            padding: var(--spacing-xl);
        }
        
        .product-container {
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
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-3xl);
            padding: var(--spacing-3xl);
        }
        
        .product-image {
            text-align: center;
        }
        
        .product-image img {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
        }
        
        .product-image img:hover {
            transform: scale(1.05);
        }
        
        .product-info h1 {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            color: var(--text-primary);
            margin-bottom: var(--spacing-lg);
        }
        
        .product-meta {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
            flex-wrap: wrap;
        }
        
        .price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            font-family: var(--font-heading);
        }
        
        .rating-display {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .star-rating {
            color: #ffd700;
            font-size: 1.2rem;
        }
        
        .category-badge {
            background: var(--gradient-button);
            color: var(--text-primary);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: var(--spacing-xl);
            font-size: 1.1rem;
        }
        
        .add-to-cart-form {
            background: var(--light-gray);
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--border-color);
        }
        
        .quantity-input {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .quantity-input input {
            width: 80px;
            padding: var(--spacing-sm);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            text-align: center;
            font-size: 1.1rem;
        }
        
        .btn {
            padding: var(--spacing-lg) var(--spacing-xl);
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
            min-height: 50px;
        }
        
        .btn-primary {
            background: var(--gradient-button);
            color: var(--text-primary);
            box-shadow: var(--shadow-md);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .alert {
            padding: var(--spacing-lg);
            border-radius: var(--border-radius);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
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
        
        .reviews-section {
            padding: var(--spacing-3xl);
            border-top: 1px solid var(--border-color);
        }
        
        .review-summary {
            background: var(--light-gray);
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-xl);
            text-align: center;
        }
        
        .average-rating {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
        }
        
        .review-item {
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-md);
        }
        
        .review-form {
            background: var(--light-gray);
            padding: var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            margin-top: var(--spacing-xl);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: inherit;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: var(--spacing-xl);
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
            <?php if (isLoggedIn()): ?>
                <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="product-detail">
            <div class="product-image">
                <?php
                    $imageSrc = $product['image'] ?? 'assets/images/default.png';
                    
                    // Handle different image sources
                    if (preg_match('/^https?:\/\//', $imageSrc)) {
                        // External URL
                        $imgUrl = $imageSrc;
                    } elseif (strpos($imageSrc, 'assets/') === 0) {
                        // Relative path starting with assets/
                        $imgUrl = '../' . $imageSrc;
                    } else {
                        // Other relative paths
                        $imgUrl = '../assets/images/' . basename($imageSrc);
                    }
                    
                    // Fallback image if original doesn't exist
                    $fallbackImg = '../assets/images/default-product.png';
                ?>
                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onerror="this.src='<?= htmlspecialchars($fallbackImg) ?>'; this.onerror=null;"
                     loading="lazy">
            </div>
            
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="product-meta">
                    <div class="price">$<?= number_format($product['price'], 2) ?></div>
                    <div class="rating-display">
                        <div class="star-rating">
                            <?= ReviewSystem::renderStarRating($averageRating) ?>
                        </div>
                        <span>(<?= $totalReviews ?> reviews)</span>
                    </div>
                    <div class="category-badge">
                        <i class="fas fa-tag"></i>
                        <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                    </div>
                </div>
                
                <div class="description">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?>
                </div>
                
                <div class="add-to-cart-form">
                    <h3><i class="fas fa-shopping-cart"></i> Add to Cart</h3>
                    <form method="post">
                        <div class="quantity-input">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                   max="<?= isset($product['stock']) && $product['stock'] > 0 ? $product['stock'] : 99 ?>" 
                                   required>
                            <span class="stock-info">
                                <?php if (isset($product['stock'])): ?>
                                    <?php if ($product['stock'] > 0): ?>
                                        <i class="fas fa-check-circle" style="color: #27ae60;"></i> 
                                        In Stock: <?= $product['stock'] ?> available
                                    <?php else: ?>
                                        <i class="fas fa-times-circle" style="color: #e74c3c;"></i> 
                                        Out of Stock
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-info-circle" style="color: #3498db;"></i> 
                                    Available
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if (!isset($product['stock']) || $product['stock'] > 0): ?>
                            <button type="submit" name="add_to_cart" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-times"></i> Out of Stock
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Reviews Section -->
        <div class="reviews-section">
            <h2><i class="fas fa-star"></i> Customer Reviews</h2>
            
            <div class="review-summary">
                <div class="average-rating"><?= number_format($averageRating, 1) ?></div>
                <div class="star-rating">
                    <?= ReviewSystem::renderStarRating($averageRating) ?>
                </div>
                <div class="total-reviews">Based on <?= $totalReviews ?> reviews</div>
            </div>

            <?php if (count($reviews) === 0): ?>
                <div class="review-item">
                    <p style="text-align: center; color: var(--text-secondary); font-style: italic;">
                        <i class="fas fa-comment-slash"></i> No reviews yet. Be the first to review this product!
                    </p>
                </div>
            <?php else: ?>
                <div class="review-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                                    <div class="star-rating">
                                        <?= ReviewSystem::renderStarRating($review['rating']) ?>
                                    </div>
                                </div>
                                <div class="review-date">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('F j, Y', strtotime($review['created_at'])) ?>
                                </div>
                            </div>
                            <div class="review-text">
                                <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Review Submission Form -->
            <div class="review-form">
                <h3><i class="fas fa-edit"></i> Write a Review</h3>
                <?php if (!isLoggedIn()): ?>
                    <p style="text-align: center;">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Log in to write a review
                        </a>
                    </p>
                <?php else: ?>
                    <?php if ($review_error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($review_error) ?>
                        </div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="form-group">
                            <label for="rating">Rating:</label>
                            <select name="rating" id="rating" required>
                                <option value="">Select a rating</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="review_text">Your Review:</label>
                            <textarea name="review_text" id="review_text" rows="4" maxlength="1000" 
                                    placeholder="Share your experience with this product..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
