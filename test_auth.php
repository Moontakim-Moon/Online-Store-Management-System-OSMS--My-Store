<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database connection
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Test database connection
try {
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch();
    echo "<p>Connected to database: " . htmlspecialchars($db['db']) . "</p>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p>Users table exists.</p>";
        
        // Check if test user exists
        $testUser = 'labonysur';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$testUser]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p>Test user '$testUser' found.</p>";
            
            // Test login
            $loggedInUser = loginUser($testUser, 'password');
            if ($loggedInUser) {
                echo "<p style='color: green;'>✅ Login successful!</p>";
                
                // Set session variables
                $_SESSION['user_id'] = $loggedInUser['id'];
                $_SESSION['username'] = $loggedInUser['username'];
                $_SESSION['is_admin'] = (bool)$loggedInUser['is_admin'];
                
                echo "<p>Session variables set. Try accessing the dashboard now:</p>";
                echo "<a href='/store/pages/dashboard.php' class='btn btn-primary'>Go to Dashboard</a>";
            } else {
                echo "<p style='color: red;'>❌ Login failed. Check the password.</p>";
            }
        } else {
            echo "<p style='color: orange;'>Test user '$testUser' not found in database.</p>";
        }
    } else {
        echo "<p style='color: red;'>Users table does not exist.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test session
if (isset($_SESSION['user_id'])) {
    echo "<p>Session user_id: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Session username: " . $_SESSION['username'] . "</p>";
} else {
    echo "<p>No active session.</p>";
}
?>

<h2>Test Login Form</h2>
<form method="post" action="test_auth.php">
    <div>
        <label>Username or Email:</label>
        <input type="text" name="usernameOrEmail" value="labonysur" required>
    </div>
    <div>
        <label>Password:</label>
        <input type="password" name="password" value="password" required>
    </div>
    <button type="submit">Test Login</button>
</form>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .btn { display: inline-block; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
    .btn-primary { background: #007bff; }
    p { margin: 10px 0; padding: 5px; }
</style>
