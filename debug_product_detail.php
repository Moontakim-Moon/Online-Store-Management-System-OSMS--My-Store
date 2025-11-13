<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Product Detail Debug</h2>";

// Test basic PHP
echo "<p>✅ PHP is working</p>";

// Test database connection
try {
    require_once 'includes/db.php';
    echo "<p>✅ Database connection loaded</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $count = $stmt->fetch()['count'];
    echo "<p>✅ Found $count products in database</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test functions.php
try {
    require_once 'includes/functions.php';
    echo "<p>✅ functions.php loaded</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ functions.php error: " . $e->getMessage() . "</p>";
    exit;
}

// Test getProductById function
try {
    $product = getProductById(1);
    if ($product) {
        echo "<p>✅ getProductById works - Found: " . htmlspecialchars($product['name']) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No product found with ID 1</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getProductById error: " . $e->getMessage() . "</p>";
}

// Test review system
echo "<h3>Review System Test:</h3>";
if (!file_exists('includes/review_system.php')) {
    echo "<p style='color: red;'>❌ review_system.php missing - Creating it...</p>";
    
    $reviewSystemCode = '<?php
class ReviewSystem {
    public static function getProductRating($pdo, $product_id) {
        try {
            $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                                  FROM product_reviews WHERE product_id = ?");
            $stmt->execute([$product_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["avg_rating" => 0, "total_reviews" => 0];
        }
    }

    public static function renderStarRating($rating) {
        $rating = floatval($rating);
        $stars = "";
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $stars .= "★";
            } elseif ($rating >= $i - 0.5) {
                $stars .= "☆";
            } else {
                $stars .= "☆";
            }
        }
        return $stars;
    }
}
?>';
    
    file_put_contents('includes/review_system.php', $reviewSystemCode);
    echo "<p>✅ Created review_system.php</p>";
}

// Test direct product detail access
echo "<h3>Direct Access Test:</h3>";
echo "<p><a href='pages/product_detail.php?id=1' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Product Detail Page</a></p>";

// Show first few products
echo "<h3>Available Products:</h3>";
try {
    $stmt = $pdo->query("SELECT id, name, price FROM products LIMIT 5");
    $products = $stmt->fetchAll();
    
    foreach ($products as $product) {
        echo "<p>ID: {$product['id']} - <a href='pages/product_detail.php?id={$product['id']}' target='_blank'>{$product['name']}</a> - \${$product['price']}</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
