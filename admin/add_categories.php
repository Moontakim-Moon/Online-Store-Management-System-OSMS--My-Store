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

// Add categories and subcategories
try {
    $pdo->beginTransaction();
    
    // Clear existing categories (optional - uncomment if you want to reset)
    // $pdo->exec("DELETE FROM categories WHERE 1");
    
    foreach ($categories as $category => $subcategories) {
        // Insert main category
        $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, NULL) ON DUPLICATE KEY UPDATE name=name");
        $stmt->execute([$category]);
        
        // Get the inserted category ID
        $parentId = $pdo->lastInsertId();
        
        // If category already exists, get its ID
        if ($parentId == 0) {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND parent_id IS NULL");
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
    echo "Categories and subcategories added successfully!";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Categories</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Categories Management</h1>
        <p>Categories and subcategories have been processed.</p>
        <a href="categories.php" class="btn">Back to Categories</a>
    </div>
</body>
</html>
