<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h2>Adding Sample Products with Images</h2>";

// Create products directory if it doesn't exist
$imageDir = "assets/images/products/";
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
    echo "<p>✓ Created directory: $imageDir</p>";
}

// Sample products with placeholder images
$sampleProducts = [
    [
        'name' => 'High Heel Sandals',
        'description' => 'Elegant high heel sandals perfect for formal occasions',
        'price' => 89.99,
        'category_id' => 1, // Women
        'subcategory_id' => 2, // Shoes
        'image' => 'assets/images/products/heels.jpg'
    ],
    [
        'name' => 'Summer Dress',
        'description' => 'Light and comfortable summer dress for casual wear',
        'price' => 59.99,
        'category_id' => 1, // Women
        'subcategory_id' => 1, // Dress
        'image' => 'assets/images/products/dress.jpg'
    ],
    [
        'name' => 'Designer Handbag',
        'description' => 'Premium leather handbag with modern design',
        'price' => 149.99,
        'category_id' => 1, // Women
        'subcategory_id' => 4, // Bags
        'image' => 'assets/images/products/handbag.jpg'
    ],
    [
        'name' => 'Men\'s Formal Shirt',
        'description' => 'Classic formal shirt for business and special occasions',
        'price' => 45.99,
        'category_id' => 2, // Men
        'subcategory_id' => 5, // Dress
        'image' => 'assets/images/products/mens_shirt.jpg'
    ],
    [
        'name' => 'Men\'s Leather Shoes',
        'description' => 'Premium leather dress shoes for professional look',
        'price' => 129.99,
        'category_id' => 2, // Men
        'subcategory_id' => 6, // Shoes
        'image' => 'assets/images/products/mens_shoes.jpg'
    ],
    [
        'name' => 'Men\'s Wallet',
        'description' => 'Genuine leather wallet with multiple card slots',
        'price' => 39.99,
        'category_id' => 2, // Men
        'subcategory_id' => 8, // Accessories
        'image' => 'assets/images/products/wallet.jpg'
    ]
];

// Create placeholder images (colored rectangles with text)
$placeholderImages = [
    'heels.jpg' => ['#FF69B4', 'High Heels'],
    'dress.jpg' => ['#FFB6C1', 'Summer Dress'],
    'handbag.jpg' => ['#DDA0DD', 'Handbag'],
    'mens_shirt.jpg' => ['#87CEEB', 'Formal Shirt'],
    'mens_shoes.jpg' => ['#8B4513', 'Leather Shoes'],
    'wallet.jpg' => ['#2F4F4F', 'Wallet']
];

foreach ($placeholderImages as $filename => $config) {
    $imagePath = $imageDir . $filename;
    if (!file_exists($imagePath)) {
        // Create a simple colored placeholder image
        $image = imagecreate(300, 300);
        $color = imagecolorallocate($image, 
            hexdec(substr($config[0], 1, 2)), 
            hexdec(substr($config[0], 3, 2)), 
            hexdec(substr($config[0], 5, 2))
        );
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $color);
        
        // Add text to image
        $text = $config[1];
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = (300 - $textWidth) / 2;
        $y = (300 - $textHeight) / 2;
        imagestring($image, $fontSize, $x, $y, $text, $textColor);
        
        imagejpeg($image, $imagePath);
        imagedestroy($image);
        echo "<p>✓ Created placeholder image: $filename</p>";
    }
}

// Clear existing products
try {
    $pdo->exec("DELETE FROM products");
    echo "<p>✓ Cleared existing products</p>";
} catch (Exception $e) {
    echo "<p>Error clearing products: " . $e->getMessage() . "</p>";
}

// Insert sample products
$insertCount = 0;
foreach ($sampleProducts as $product) {
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, subcategory_id, image, stock_quantity, created_at) VALUES (?, ?, ?, ?, ?, ?, 50, NOW())");
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['category_id'],
            $product['subcategory_id'],
            $product['image']
        ]);
        $insertCount++;
        echo "<p>✓ Added product: " . htmlspecialchars($product['name']) . "</p>";
    } catch (Exception $e) {
        echo "<p>✗ Error adding product " . htmlspecialchars($product['name']) . ": " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Summary</h3>";
echo "<p>Successfully added $insertCount products with images.</p>";

// Test cart functionality
echo "<h3>Testing Cart Functions</h3>";
echo "<p><a href='test_product_images.php'>View Product Image Test</a></p>";
echo "<p><a href='pages/products.php'>View Products Page</a></p>";
echo "<p><a href='pages/cart.php'>View Cart Page</a></p>";

// Show current products
echo "<h3>Current Products in Database</h3>";
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name, s.name as subcategory_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN categories s ON p.subcategory_id = s.id");
    $products = $stmt->fetchAll();
    
    if (!empty($products)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Subcategory</th><th>Image</th><th>Preview</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . $product['id'] . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>$" . number_format($product['price'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($product['category_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($product['subcategory_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($product['image']) . "</td>";
            echo "<td>";
            if ($product['image'] && file_exists($product['image'])) {
                echo "<img src='" . htmlspecialchars($product['image']) . "' width='50' height='50' style='object-fit: cover;'>";
            } else {
                echo "No image";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No products found.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error fetching products: " . $e->getMessage() . "</p>";
}
?>
