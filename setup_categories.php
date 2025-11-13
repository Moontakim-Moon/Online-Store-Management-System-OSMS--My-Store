<?php
session_start();
require_once 'includes/db.php';

// Simple direct database setup without session checks
echo "<!DOCTYPE html><html><head><title>Setup Categories</title></head><body>";
echo "<h2>Setting up Categories and Subcategories</h2>";

try {
    // Clear existing categories first
    $pdo->exec("DELETE FROM categories");
    $pdo->exec("ALTER TABLE categories AUTO_INCREMENT = 1");
    
    echo "Cleared existing categories...<br>";
    
    // Insert main categories
    $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, NULL)");
    
    // Women category
    $stmt->execute(['Women']);
    $women_id = $pdo->lastInsertId();
    echo "Added Women category (ID: $women_id)<br>";
    
    // Men category
    $stmt->execute(['Men']);
    $men_id = $pdo->lastInsertId();
    echo "Added Men category (ID: $men_id)<br>";
    
    // Insert subcategories
    $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
    
    // Women subcategories
    $women_subcategories = ['Dress', 'Shoes', 'Accessories', 'Bags'];
    foreach ($women_subcategories as $subcat) {
        $stmt->execute([$subcat, $women_id]);
        echo "Added Women > $subcat<br>";
    }
    
    // Men subcategories
    $men_subcategories = ['Dress', 'Shoes', 'Bags', 'Accessories'];
    foreach ($men_subcategories as $subcat) {
        $stmt->execute([$subcat, $men_id]);
        echo "Added Men > $subcat<br>";
    }
    
    echo "<br><h3 style='color: green;'>✓ Categories Setup Complete!</h3>";
    echo "<p><strong>Main Categories Added:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Women</strong> (Dress, Shoes, Accessories, Bags)</li>";
    echo "<li><strong>Men</strong> (Dress, Shoes, Bags, Accessories)</li>";
    echo "</ul>";
    
    // Display all categories
    echo "<h3>All Categories in Database:</h3>";
    $stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
    $main_categories = $stmt->fetchAll();
    
    foreach ($main_categories as $main_cat) {
        echo "<h4>" . htmlspecialchars($main_cat['name']) . " (ID: " . $main_cat['id'] . ")</h4>";
        
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY name");
        $stmt->execute([$main_cat['id']]);
        $subcategories = $stmt->fetchAll();
        
        echo "<ul>";
        foreach ($subcategories as $subcat) {
            echo "<li>" . htmlspecialchars($subcat['name']) . " (ID: " . $subcat['id'] . ")</li>";
        }
        echo "</ul>";
    }
    
    echo "<br><p><a href='admin/upload_product.php' style='background: #f4d03f; color: #333; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>→ Go to Add Product</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
echo "</body></html>";
?>
