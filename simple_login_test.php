<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direct database connection test
echo "<h1>🔧 Simple Login Test & Fix</h1>";

// Test database connection with correct credentials
try {
    $pdo = new PDO("mysql:host=localhost;dbname=store_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>✅ Database connected successfully</div>";
} catch(PDOException $e) {
    echo "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>❌ Database error: " . $e->getMessage() . "</div>";
    
    // Try with password
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=store_db", "root", "232-15-473@Labony");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div style='color: green;'>✅ Connected with password</div>";
    } catch(PDOException $e2) {
        echo "<div style='color: red;'>❌ Still failed with password: " . $e2->getMessage() . "</div>";
        exit;
    }
}

// Check if users table exists and create if needed
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if($stmt->rowCount() == 0) {
        echo "<div style='color: blue;'>📝 Creating users table...</div>";
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_admin BOOLEAN DEFAULT FALSE,
            email_verified BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "<div style='color: green;'>✅ Users table created</div>";
    } else {
        echo "<div style='color: green;'>✅ Users table exists</div>";
    }
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Table error: " . $e->getMessage() . "</div>";
}

// Ensure admin user exists
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if(!$admin) {
        $password_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified) VALUES (?, ?, ?, 1, 1)");
        $stmt->execute(['labonysur', 'labonysur6@gmail.com', $password_hash]);
        echo "<div style='color: green;'>✅ Admin user created</div>";
    } else {
        // Update password to be sure
        $password_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_admin = 1, email_verified = 1 WHERE username = 'labonysur'");
        $stmt->execute([$password_hash]);
        echo "<div style='color: green;'>✅ Admin user verified and updated</div>";
    }
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Admin setup error: " . $e->getMessage() . "</div>";
}

// Simple login function
function simpleLogin($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch(Exception $e) {
        echo "Login error: " . $e->getMessage();
        return false;
    }
}

// Process login attempt
if($_POST['test_login'] ?? false) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h3>🔐 Login Test Results:</h3>";
    $user = simpleLogin($pdo, $username, $password);
    
    if($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        echo "<div style='color: green; padding: 15px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🎉 LOGIN SUCCESSFUL!</h4>";
        echo "User: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "<br>";
        echo "Session started successfully!<br><br>";
        
        if($user['is_admin']) {
            echo "<a href='admin/index.php' style='padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;'>→ Go to Admin Panel</a>";
        } else {
            echo "<a href='pages/products.php' style='padding: 10px 15px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;'>→ Go to Products</a>";
        }
        echo "</div>";
    } else {
        echo "<div style='color: red; padding: 15px; background: #f8d7da; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Login failed. Check username/password.";
        echo "</div>";
    }
}

// Process registration
if($_POST['test_register'] ?? false) {
    $reg_username = $_POST['reg_username'] ?? '';
    $reg_email = $_POST['reg_email'] ?? '';
    $reg_password = $_POST['reg_password'] ?? '';
    
    echo "<h3>📝 Registration Test Results:</h3>";
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$reg_username, $reg_email]);
        
        if($stmt->fetchColumn() > 0) {
            echo "<div style='color: red;'>❌ Username or email already exists</div>";
        } else {
            $password_hash = password_hash($reg_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified) VALUES (?, ?, ?, 0, 1)");
            $stmt->execute([$reg_username, $reg_email, $password_hash]);
            
            echo "<div style='color: green; padding: 15px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ REGISTRATION SUCCESSFUL!</h4>";
            echo "Username: $reg_username<br>";
            echo "Email: $reg_email<br>";
            echo "You can now login with these credentials.";
            echo "</div>";
        }
    } catch(Exception $e) {
        echo "<div style='color: red;'>❌ Registration error: " . $e->getMessage() . "</div>";
    }
}
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">

<!-- Login Form -->
<form method="post" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
    <h3>🔐 Test Login</h3>
    <div style="margin: 10px 0;">
        <label>Username/Email:</label><br>
        <input type="text" name="username" value="labonysur" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="margin: 10px 0;">
        <label>Password:</label><br>
        <input type="password" name="password" value="password" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <button type="submit" name="test_login" value="1" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%;">🔐 Test Login</button>
</form>

<!-- Registration Form -->
<form method="post" style="background: #e7f3ff; padding: 20px; border-radius: 8px;">
    <h3>📝 Test Registration</h3>
    <div style="margin: 10px 0;">
        <label>Username:</label><br>
        <input type="text" name="reg_username" value="testuser<?= rand(100, 999) ?>" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="margin: 10px 0;">
        <label>Email:</label><br>
        <input type="email" name="reg_email" value="test<?= rand(100, 999) ?>@example.com" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="margin: 10px 0;">
        <label>Password:</label><br>
        <input type="password" name="reg_password" value="testpass123" style="padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <button type="submit" name="test_register" value="1" style="padding: 10px 20px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%;">📝 Test Register</button>
</form>

</div>

<div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;">
    <h3>🔧 Fix Applied:</h3>
    <ul>
        <li>✅ Fixed database connection (removed password, changed DB name to store_db)</li>
        <li>✅ Ensured users table exists</li>
        <li>✅ Created/updated admin user with correct credentials</li>
        <li>✅ Simple direct login function that bypasses complex logic</li>
        <li>✅ Session handling for successful logins</li>
    </ul>
</div>

<div style="background: #d4edda; padding: 15px; border-radius: 8px; margin: 20px 0;">
    <h3>🚀 Test Your Original Pages:</h3>
    <a href="pages/login.php" target="_blank" style="margin: 5px; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">🔐 Original Login Page</a>
    <a href="pages/register.php" target="_blank" style="margin: 5px; padding: 10px 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">📝 Original Register Page</a>
    <a href="admin/admin_login.php" target="_blank" style="margin: 5px; padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">👨‍💼 Original Admin Login</a>
</div>
