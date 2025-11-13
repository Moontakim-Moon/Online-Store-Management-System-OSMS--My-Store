<?php
require_once 'includes/db.php';

echo "<h2>Database Table Fix</h2>";

try {
    // Check if order_items table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<p>Creating order_items table...</p>";
        
        $createOrderItems = "
        CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        
        $pdo->exec($createOrderItems);
        echo "<p style='color: green;'>✅ order_items table created successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ order_items table already exists.</p>";
    }
    
    // Check if orders table has order_number column
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'order_number'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "<p>Adding order_number column to orders table...</p>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) UNIQUE AFTER id");
        echo "<p style='color: green;'>✅ order_number column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ order_number column already exists.</p>";
    }
    
    // Check if orders table has payment_method column
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "<p>Adding payment_method column to orders table...</p>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash on Delivery' AFTER status");
        echo "<p style='color: green;'>✅ payment_method column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ payment_method column already exists.</p>";
    }
    
    // Check if orders table has subtotal column
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'subtotal'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "<p>Adding subtotal column to orders table...</p>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00 AFTER total");
        echo "<p style='color: green;'>✅ subtotal column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ subtotal column already exists.</p>";
    }
    
    echo "<h3 style='color: green;'>Database structure updated successfully!</h3>";
    echo "<p><a href='pages/checkout.php?step=cart_review'>Test Checkout Process</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
