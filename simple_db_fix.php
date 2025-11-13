<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Simple Database Fix</h1>";

// Try connecting without password first (common XAMPP default)
try {
    echo "Trying connection without password...<br>";
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL<br>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS store");
    echo "✅ Database 'store' created/exists<br>";
    
    // Connect to store database
    $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to store database<br>";
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT FALSE,
        email_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Users table ready<br>";
    
    // Delete existing admin and create fresh one
    $pdo->exec("DELETE FROM users WHERE username = 'labonysur'");
    
    $password_hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified) VALUES (?, ?, ?, 1, 1)");
    $stmt->execute(['labonysur', 'labonysur6@gmail.com', $password_hash]);
    
    echo "✅ Admin user created successfully<br>";
    
    // Test the admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify('password', $admin['password'])) {
        echo "<div style='background: #d4edda; padding: 15px; border: 2px solid #28a745; margin: 10px 0;'>";
        echo "<h2>✅ SUCCESS!</h2>";
        echo "<strong>Username:</strong> labonysur<br>";
        echo "<strong>Password:</strong> password<br>";
        echo "<strong>Admin:</strong> " . ($admin['is_admin'] ? 'YES' : 'NO') . "<br>";
        echo "<strong>Email Verified:</strong> " . ($admin['email_verified'] ? 'YES' : 'NO') . "<br>";
        echo "</div>";
        
        // Update db.php with correct credentials
        $db_content = '<?php
$host = "localhost";
$dbname = "store";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>';
        
        file_put_contents('../includes/db.php', $db_content);
        echo "✅ Updated db.php with correct credentials<br>";
        
    } else {
        echo "❌ Admin user verification failed<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    
    // Try with password
    try {
        echo "<br>Trying with password...<br>";
        $pdo = new PDO("mysql:host=localhost", "root", "232-15-473@Labony");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connected with password<br>";
        
        // Update db.php with password
        $db_content = '<?php
$host = "localhost";
$dbname = "store";
$username = "root";
$password = "232-15-473@Labony";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>';
        
        file_put_contents('../includes/db.php', $db_content);
        echo "✅ Updated db.php with password<br>";
        
    } catch(PDOException $e2) {
        echo "❌ Both connection attempts failed<br>";
        echo "Error: " . $e2->getMessage() . "<br>";
    }
}

echo "<hr>";
echo '<div style="text-align: center; margin: 20px;">';
echo '<a href="pages/login.php" style="background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;">🚀 TRY LOGIN NOW</a>';
echo '</div>';
?>
