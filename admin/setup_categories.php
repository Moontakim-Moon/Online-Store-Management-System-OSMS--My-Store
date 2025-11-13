<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die('Access denied. Admin only.');
}

// Categories and their subcategories
$categories = [
    'Women' => ['Dress', 'Shoes', 'Accessories', 'Bags'],
    'Men' => ['Dress', 'Shoes', 'Bags', 'Accessories']
];

// Create categories table if it doesn't exist
$createTableSQL = "
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    // Create categories table
    $pdo->exec($createTableSQL);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    foreach ($categories as $category => $subcategories) {
        // Insert main category if it doesn't exist
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, parent_id) VALUES (?, NULL)");
        $stmt->execute([$category]);
        
        // Get the category ID
        $parentId = $pdo->lastInsertId();
        
        // If category already exists, get its ID
        if ($parentId == 0) {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND parent_id IS NULL LIMIT 1");
            $stmt->execute([$category]);
            $parentId = $stmt->fetchColumn();
        }
        
        // Insert subcategories
        foreach ($subcategories as $subcategory) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, parent_id) VALUES (?, ?)");
            $stmt->execute([$subcategory, $parentId]);
        }
    }
    
    $pdo->commit();
    $message = "Categories and subcategories added successfully!";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Setup Categories</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; margin: 20px 0; }
        .error { color: red; margin: 20px 0; }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #4CAF50; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-top: 20px;
        }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Setup Categories</h1>
        
        <?php if (isset($message)): ?>
            <div class="<?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <h3>Categories to be added:</h3>
        <ul>
            <?php foreach ($categories as $category => $subcategories): ?>
                <li><?= htmlspecialchars($category) ?>
                    <ul>
                        <?php foreach ($subcategories as $subcategory): ?>
                            <li><?= htmlspecialchars($subcategory) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div>
            <a href="categories.php" class="btn">View Categories</a>
            <a href="index.php" class="btn">Admin Dashboard</a>
        </div>
    </div>
</body>
</html>
