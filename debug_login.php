<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/functions.php';
require_once 'includes/db.php';

echo "<pre>";

echo "=== SESSION DATA ===\n";
print_r($_SESSION);

echo "\n=== POST DATA ===\n";
print_r($_POST);

// Test database connection
try {
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch();
    echo "\n=== DATABASE CONNECTION ===\n";
    echo "Connected to database: " . $db['db'] . "\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'labonysur'");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "\n=== USER FOUND ===\n";
        print_r($user);
        
        // Test login
        if (loginUser('labonysur', 'password')) {
            echo "\n=== LOGIN SUCCESSFUL ===\n";
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            echo "Session variables set. Redirecting to dashboard...";
            header("Location: pages/dashboard.php");
            exit();
        } else {
            echo "\n=== LOGIN FAILED ===\n";
            echo "Check the password or user credentials.";
        }
    } else {
        echo "\n=== USER NOT FOUND ===\n";
        echo "User 'labonysur' not found in the database.";
    }
    
} catch (PDOException $e) {
    echo "\n=== DATABASE ERROR ===\n";
    echo $e->getMessage();
}

?>

<h2>Test Login</h2>
<form method="post" action="debug_login.php">
    <input type="text" name="username" value="labonysur"><br>
    <input type="password" name="password" value="password"><br>
    <button type="submit">Test Login</button>
</form>
