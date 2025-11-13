<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Simple dashboard content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .content { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Your Dashboard</h1>
            <p>User ID: <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></p>
            <p>Username: <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></p>
            <p><a href="logout.php">Logout</a></p>
        </div>
        
        <div class="content">
            <h2>Dashboard Content</h2>
            <p>This is a test dashboard. If you can see this, the basic dashboard functionality is working.</p>
            
            <h3>Debug Info:</h3>
            <pre>Session Data: <?php print_r($_SESSION); ?></pre>
            
            <p><a href="dashboard.php">Try Original Dashboard</a></p>
        </div>
    </div>
</body>
</html>
