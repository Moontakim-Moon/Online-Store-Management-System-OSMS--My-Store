<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h2>Testing Product Images in Cart</h2>";

// Check products in database
echo "<h3>Products in Database:</h3>";
$stmt = $pdo->query("SELECT id, name, image FROM products LIMIT 10");
$products = $stmt->fetchAll();

if (empty($products)) {
    echo "<p>No products found in database!</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists</th></tr>";
    foreach ($products as $product) {
        $imagePath = $product['image'];
        $fileExists = $imagePath && file_exists($imagePath) ? 'YES' : 'NO';
        echo "<tr>";
        echo "<td>" . $product['id'] . "</td>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . ($imagePath ?: 'NULL') . "</td>";
        echo "<td>" . $fileExists . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check cart items structure
echo "<h3>Cart Items Table Structure:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE cart_items");
    $columns = $stmt->fetchAll();
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Test getCartItems function
echo "<h3>Testing getCartItems Function:</h3>";
if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $cartItems = getCartItems($userId);
    
    if (empty($cartItems)) {
        echo "<p>No cart items found for user ID: $userId</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>Cart ID</th><th>Product Name</th><th>Price</th><th>Quantity</th><th>Image Path</th><th>Image Preview</th></tr>";
        foreach ($cartItems as $item) {
            echo "<tr>";
            echo "<td>" . $item['id'] . "</td>";
            echo "<td>" . htmlspecialchars($item['name']) . "</td>";
            echo "<td>$" . number_format($item['price'], 2) . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>" . ($item['image'] ?: 'NULL') . "</td>";
            echo "<td>";
            if (!empty($item['image']) && file_exists($item['image'])) {
                echo "<img src='" . htmlspecialchars($item['image']) . "' width='50' height='50' style='object-fit: cover;'>";
            } else {
                echo "No image";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>Add ?user_id=X to URL to test cart items for specific user</p>";
}

// Check if assets/images/products directory exists
echo "<h3>Image Directory Check:</h3>";
$imageDir = "assets/images/products/";
if (is_dir($imageDir)) {
    echo "<p>✓ Directory exists: $imageDir</p>";
    $files = scandir($imageDir);
    $imageFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
    });
    
    if (!empty($imageFiles)) {
        echo "<p>Images found: " . count($imageFiles) . "</p>";
        echo "<ul>";
        foreach (array_slice($imageFiles, 0, 10) as $file) {
            echo "<li>$file</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No image files found in directory</p>";
    }
} else {
    echo "<p>✗ Directory does not exist: $imageDir</p>";
}
?>
