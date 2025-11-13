<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Not logged in. <a href='login.php'>Login here</a>");
}

require_once '../includes/db.php';

// Get basic user info
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Error: User not found");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Dashboard</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #4a76a8;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .error {
            color: red;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ffcccc;
            background: #fff0f0;
            border-radius: 4px;
        }
        .success {
            color: green;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccffcc;
            background: #f0fff0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Simple Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($user['username']) ?>!</p>
            <p><a href="logout.php" style="color: white;">Logout</a></p>
        </div>
        
        <div class="content">
            <h2>User Information</h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Member since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
            
            <h2>Debug Information</h2>
            <h3>Session Data:</h3>
            <pre><?php print_r($_SESSION); ?></pre>
            
            <h3>PHP Version:</h3>
            <p><?= phpversion() ?></p>
            
            <h3>Included Files:</h3>
            <pre><?php print_r(get_included_files()); ?></pre>
        </div>
    </div>
</body>
</html>
