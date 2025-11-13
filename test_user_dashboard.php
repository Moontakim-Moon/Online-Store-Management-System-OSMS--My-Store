<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please <a href='pages/login.php'>login</a> first.");
}

// Force user mode (not admin)
$_SESSION['is_admin'] = 0;

// Include database connection
require_once 'includes/db.php';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
        .user-info { background: #e9f7fe; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>User Dashboard Test</h1>
    
    <div class="user-info">
        <h2>Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Member since: <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
        <p><a href="pages/logout.php">Logout</a></p>
    </div>
    
    <h2>Debug Information</h2>
    
    <h3>Session Data:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h3>User Data from Database:</h3>
    <pre><?php print_r($user); ?></pre>
    
    <h3>Test Links:</h3>
    <ul>
        <li><a href="pages/dashboard.php">Original Dashboard</a></li>
        <li><a href="pages/products.php">Products Page</a></li>
        <li><a href="pages/cart.php">View Cart</a></li>
    </ul>
    
    <h3>PHP Info:</h3>
    <p>PHP Version: <?= phpversion() ?></p>
    <p>Memory Limit: <?= ini_get('memory_limit') ?></p>
    <p>Max Execution Time: <?= ini_get('max_execution_time') ?>s</p>
</body>
</html>
