<?php
require_once '../includes/functions.php';
require_once '../includes/review_system.php';

$pdo = $GLOBALS['pdo'];
$topRated = ReviewSystem::getTopRatedProducts($pdo);
$bestSelling = ReviewSystem::getBestSellingProducts($pdo);

include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/reviews.css">

<div class="featured-products">
    <div class="featured-header">
        <h2>Top Rated Products</h2>
        <p>Our highest rated products based on customer reviews</p>
    </div>
    
    <div class="featured-grid">
        <?php foreach ($topRated as $product): ?>
            <div class="featured-item">
                <a href="product_detail.php?id=<?= $product['id'] ?>">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="featured-image">
                    <div class="featured-details">
                        <h3 class="featured-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="featured-rating">
                            <?= ReviewSystem::renderStarRating($product['avg_rating']) ?>
                            <span>(<?= number_format($product['review_count']) ?> reviews)</span>
                        </div>
                        <div class="featured-price">$<?= number_format($product['price'], 2) ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="featured-products">
    <div class="featured-header">
        <h2>Best Selling Products</h2>
        <p>Our most popular products by sales</p>
    </div>
    
    <div class="featured-grid">
        <?php foreach ($bestSelling as $product): ?>
            <div class="featured-item">
                <a href="product_detail.php?id=<?= $product['id'] ?>">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="featured-image">
                    <div class="featured-details">
                        <h3 class="featured-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="featured-rating">
                            <?= ReviewSystem::renderStarRating($product['avg_rating']) ?>
                        </div>
                        <div class="featured-price">$<?= number_format($product['price'], 2) ?></div>
                        <div class="order-count"><?= $product['order_count'] ?> orders</div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
