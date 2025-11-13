<?php
require_once 'includes/db.php';

try {
    // Create order_items table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add missing columns to orders table if they don't exist
    $columns_to_add = [
        "ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) UNIQUE AFTER id",
        "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash on Delivery'",
        "ALTER TABLE orders ADD COLUMN subtotal DECIMAL(10,2) DEFAULT 0.00"
    ];
    
    foreach ($columns_to_add as $sql) {
        try {
            $pdo->exec($sql);
        } catch (Exception $e) {
            // Column might already exist, continue
        }
    }
    
    echo "Database setup completed successfully!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
