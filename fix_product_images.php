<?php
require_once 'includes/db.php';

echo "<h2>Fixing Product Images in Cart and Checkout</h2>";

// Create products directory if it doesn't exist
$imageDir = "assets/images/products/";
if (!is_dir($imageDir)) {
    if (mkdir($imageDir, 0755, true)) {
        echo "<p>✓ Created directory: $imageDir</p>";
    } else {
        echo "<p>✗ Failed to create directory: $imageDir</p>";
    }
}

// Check if GD extension is available for image creation
if (!extension_loaded('gd')) {
    echo "<p>⚠ GD extension not available. Creating simple text files instead of images.</p>";
    $useGD = false;
} else {
    echo "<p>✓ GD extension available for image creation</p>";
    $useGD = true;
}

// Sample products with images
$sampleProducts = [
    [
        'name' => 'High Heel Sandals',
        'description' => 'Elegant high heel sandals perfect for formal occasions',
        'price' => 89.99,
        'category_id' => 1,
        'subcategory_id' => 2,
        'image' => 'assets/images/products/heels.jpg',
        'color' => '#FF69B4'
    ],
    [
        'name' => 'Summer Dress',
        'description' => 'Light and comfortable summer dress for casual wear',
        'price' => 59.99,
        'category_id' => 1,
        'subcategory_id' => 1,
        'image' => 'assets/images/products/dress.jpg',
        'color' => '#FFB6C1'
    ],
    [
        'name' => 'Designer Handbag',
        'description' => 'Premium leather handbag with modern design',
        'price' => 149.99,
        'category_id' => 1,
        'subcategory_id' => 4,
        'image' => 'assets/images/products/handbag.jpg',
        'color' => '#DDA0DD'
    ]
];

// Create placeholder images
foreach ($sampleProducts as $product) {
    $imagePath = $product['image'];
    $filename = basename($imagePath);
    
    if (!file_exists($imagePath)) {
        if ($useGD) {
            // Create colored placeholder with GD
            $image = imagecreate(300, 300);
            $hex = str_replace('#', '', $product['color']);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            
            $bgColor = imagecolorallocate($image, $r, $g, $b);
            $textColor = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $bgColor);
            
            // Add product name to image
            $text = $product['name'];
            $fontSize = 3;
            $textWidth = imagefontwidth($fontSize) * strlen($text);
            $textHeight = imagefontheight($fontSize);
            $x = (300 - $textWidth) / 2;
            $y = (300 - $textHeight) / 2;
            imagestring($image, $fontSize, $x, $y, $text, $textColor);
            
            if (imagejpeg($image, $imagePath)) {
                echo "<p>✓ Created image: $filename</p>";
            } else {
                echo "<p>✗ Failed to create image: $filename</p>";
            }
            imagedestroy($image);
        } else {
            // Create simple text file as placeholder
            file_put_contents($imagePath . '.txt', $product['name']);
            echo "<p>✓ Created text placeholder: $filename.txt</p>";
        }
    } else {
        echo "<p>- Image already exists: $filename</p>";
    }
}

// Clear existing products and add new ones
try {
    $pdo->exec("DELETE FROM products");
    echo "<p>✓ Cleared existing products</p>";
    
    $insertCount = 0;
    foreach ($sampleProducts as $product) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, subcategory_id, image, stock_quantity, created_at) VALUES (?, ?, ?, ?, ?, ?, 50, NOW())");
        if ($stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['category_id'],
            $product['subcategory_id'],
            $product['image']
        ])) {
            $insertCount++;
            echo "<p>✓ Added product: " . htmlspecialchars($product['name']) . "</p>";
        } else {
            echo "<p>✗ Failed to add product: " . htmlspecialchars($product['name']) . "</p>";
        }
    }
    
    echo "<h3>Summary</h3>";
    echo "<p>Successfully added $insertCount products with images.</p>";
    
    // Display current products
    echo "<h3>Products in Database</h3>";
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Price</th><th>Image Path</th><th>File Exists</th></tr>";
    foreach ($products as $product) {
        $fileExists = file_exists($product['image']) ? '✓' : '✗';
        echo "<tr>";
        echo "<td>" . $product['id'] . "</td>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>$" . number_format($product['price'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($product['image']) . "</td>";
        echo "<td>" . $fileExists . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>Next Steps</h3>";
echo "<ul>";
echo "<li><a href='pages/products.php'>View Products Page</a></li>";
echo "<li><a href='pages/login.php'>Login to test cart functionality</a></li>";
echo "<li><a href='test_product_images.php'>Test Product Images</a></li>";
echo "</ul>";
?>
