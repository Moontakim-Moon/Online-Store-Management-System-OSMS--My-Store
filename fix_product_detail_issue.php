<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

echo "<h2>Fixing Product Detail Issues</h2>";

// Check if product_reviews table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_reviews'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ product_reviews table missing - creating it...</p>";
        
        $createReviewsTable = "
        CREATE TABLE product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            review_text TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $pdo->exec($createReviewsTable);
        echo "<p style='color: green;'>✅ Created product_reviews table</p>";
    } else {
        echo "<p style='color: green;'>✅ product_reviews table exists</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error with product_reviews table: " . $e->getMessage() . "</p>";
}

// Test basic product retrieval
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = 1");
    $stmt->execute();
    $product = $stmt->fetch();
    
    if ($product) {
        echo "<p style='color: green;'>✅ Product ID 1 found: " . htmlspecialchars($product['name']) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No product with ID 1 found</p>";
        
        // Add a sample product
        $insertProduct = "INSERT INTO products (name, description, price, image, category_id, stock_quantity, status) 
                         VALUES ('Sample Product', 'This is a sample product for testing', 29.99, 'https://via.placeholder.com/400x300', 1, 50, 'active')";
        $pdo->exec($insertProduct);
        echo "<p style='color: green;'>✅ Added sample product</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing product: " . $e->getMessage() . "</p>";
}

// Test getProductById function
try {
    require_once 'includes/functions.php';
    $product = getProductById(1);
    if ($product) {
        echo "<p style='color: green;'>✅ getProductById(1) works</p>";
    } else {
        echo "<p style='color: red;'>❌ getProductById(1) returns null</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getProductById error: " . $e->getMessage() . "</p>";
}

echo "<h3>Test Links:</h3>";
echo "<p><a href='pages/product_detail.php?id=1' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Product Detail Page</a></p>";
echo "<p><a href='pages/products.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View All Products</a></p>";
?>
