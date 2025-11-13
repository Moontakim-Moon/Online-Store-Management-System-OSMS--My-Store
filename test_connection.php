<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test database connection
try {
    $host = "localhost";
    $dbname = "store";
    $username = "root";
    $password = "232-15-473@Labony";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Database Connection Test</h1>";
    echo "<p style='color: green;'>✅ Successfully connected to database: $dbname</p>";
    
    // Test user query
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'labonysur'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<h2>User Found:</h2>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Test password
        if (password_verify('password', $user['password'])) {
            echo "<p style='color: green;'>✅ Password verification successful!</p>";
            
            // Test session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            echo "<h2>Session Data:</h2>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
            
            // Test redirect
            echo "<p><a href='/store/pages/dashboard.php'>Go to Dashboard</a></p>";
            
        } else {
            echo "<p style='color: red;'>❌ Password verification failed!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ User 'labonysur' not found in database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h1>Database Connection Error</h1>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
</style>
