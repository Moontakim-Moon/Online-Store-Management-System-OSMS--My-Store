<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h2>Product Detail Debug Test</h2>";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $count = $stmt->fetch()['count'];
    echo "<p style='color: green;'>✅ Database connected. Found $count products.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Test getProductById function
echo "<h3>Testing getProductById function:</h3>";
try {
    $product = getProductById(1);
    if ($product) {
        echo "<p style='color: green;'>✅ getProductById(1) works:</p>";
        echo "<ul>";
        echo "<li>ID: " . $product['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($product['name']) . "</li>";
        echo "<li>Price: $" . number_format($product['price'], 2) . "</li>";
        echo "<li>Image: " . htmlspecialchars($product['image'] ?? 'No image') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No product found with ID 1</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getProductById error: " . $e->getMessage() . "</p>";
}

// List all products
echo "<h3>All Products in Database:</h3>";
try {
    $stmt = $pdo->query("SELECT id, name, price, image, status FROM products LIMIT 10");
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "<p style='color: orange;'>⚠️ No products found in database</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Status</th><th>Image</th><th>Test Link</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>$" . number_format($product['price'], 2) . "</td>";
            echo "<td>" . $product['status'] . "</td>";
            echo "<td>" . (empty($product['image']) ? 'No image' : 'Has image') . "</td>";
            echo "<td><a href='pages/product_detail.php?id=" . $product['id'] . "' target='_blank'>View Detail</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error listing products: " . $e->getMessage() . "</p>";
}

// Test review system
echo "<h3>Testing Review System:</h3>";
try {
    if (file_exists('includes/review_system.php')) {
        require_once 'includes/review_system.php';
        echo "<p style='color: green;'>✅ review_system.php loaded</p>";
        
        // Test ReviewSystem class
        if (class_exists('ReviewSystem')) {
            echo "<p style='color: green;'>✅ ReviewSystem class exists</p>";
            $rating = ReviewSystem::getProductRating($pdo, 1);
            echo "<p>Product 1 rating: " . ($rating['avg_rating'] ?? 0) . " (" . ($rating['total_reviews'] ?? 0) . " reviews)</p>";
        } else {
            echo "<p style='color: red;'>❌ ReviewSystem class not found</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ review_system.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Review system error: " . $e->getMessage() . "</p>";
}

echo "<h3>Direct Product Detail Test:</h3>";
echo "<p><a href='pages/product_detail.php?id=1' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Product Detail Page</a></p>";
?>
