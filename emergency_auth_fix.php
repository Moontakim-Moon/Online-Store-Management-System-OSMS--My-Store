<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🚨 Emergency Authentication Diagnosis & Fix</h1>";

// Step 1: Check if database exists and is accessible
echo "<h2>Step 1: Database Connection Test</h2>";
// Try different database connection methods
$db_configs = [
    ["mysql:host=localhost;dbname=store", "root", "232-15-473@Labony"],
    ["mysql:host=localhost;dbname=store", "root", ""],
    ["mysql:host=127.0.0.1;dbname=store", "root", "232-15-473@Labony"],
    ["mysql:host=localhost", "root", "232-15-473@Labony"] // Without database name
];

$pdo = null;
foreach ($db_configs as $i => $config) {
    try {
        echo "Trying connection " . ($i + 1) . "...<br>";
        $pdo = new PDO($config[0], $config[1], $config[2]);
        $pdo = new PDO("mysql:host=localhost", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS store_db");
        echo "<div style='color: blue;'>📝 Created database 'store_db'</div>";
        
        // Reconnect to new database
        $pdo = new PDO("mysql:host=localhost;dbname=store_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div style='color: green;'>✅ Connected to new database</div>";
    } catch(PDOException $e2) {
        echo "<div style='color: red;'>❌ Could not create database: " . $e2->getMessage() . "</div>";
        exit;
    }
}

// Step 2: Check if users table exists
echo "<h2>Step 2: Users Table Check</h2>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    echo "<div style='color: green;'>✅ Users table exists</div>";
    
    // Show table structure
    echo "<h4>Table Structure:</h4>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Users table missing: " . $e->getMessage() . "</div>";
    
    // Create users table
    echo "<div style='color: blue;'>📝 Creating users table...</div>";
    try {
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
    } catch(PDOException $e2) {
        echo "<div style='color: red;'>❌ Could not create users table: " . $e2->getMessage() . "</div>";
        exit;
    }
}

// Step 3: Check/Create admin user
echo "<h2>Step 3: Admin User Setup</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if($admin) {
        echo "<div style='color: green;'>✅ Admin user exists</div>";
        
        // Update admin password to ensure it's correct
        $password_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_admin = 1, email_verified = 1 WHERE username = 'labonysur'");
        $stmt->execute([$password_hash]);
        echo "<div style='color: blue;'>🔄 Admin password updated</div>";
        
    } else {
        echo "<div style='color: red;'>❌ Admin user missing</div>";
        
        // Create admin user
        $password_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified, created_at) VALUES (?, ?, ?, 1, 1, NOW())");
        $stmt->execute(['labonysur', 'labonysur6@gmail.com', $password_hash]);
        echo "<div style='color: green;'>✅ Admin user created</div>";
    }
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Admin user error: " . $e->getMessage() . "</div>";
}

// Step 4: Test direct login function
echo "<h2>Step 4: Direct Login Function Test</h2>";

function testLoginUser($pdo, $usernameOrEmail, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<div style='color: blue;'>📝 User found: " . $user['username'] . " (ID: " . $user['id'] . ")</div>";
            echo "<div style='color: blue;'>📝 Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "</div>";
            echo "<div style='color: blue;'>📝 Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "</div>";
            
            if (password_verify($password, $user['password'])) {
                echo "<div style='color: green;'>✅ Password verification successful</div>";
                return $user;
            } else {
                echo "<div style='color: red;'>❌ Password verification failed</div>";
                echo "<div style='color: blue;'>📝 Stored hash: " . substr($user['password'], 0, 20) . "...</div>";
            }
        } else {
            echo "<div style='color: red;'>❌ User not found</div>";
        }
        return false;
    } catch(Exception $e) {
        echo "<div style='color: red;'>❌ Login function error: " . $e->getMessage() . "</div>";
        return false;
    }
}

// Test admin login
echo "<h3>Testing Admin Login:</h3>";
$admin_result = testLoginUser($pdo, 'labonysur', 'password');

// Step 5: Create a simple working login form
echo "<h2>Step 5: Emergency Login Form</h2>";

if($_POST['emergency_login'] ?? false) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h3>Login Attempt Results:</h3>";
    $login_result = testLoginUser($pdo, $username, $password);
    
    if($login_result) {
        session_start();
        $_SESSION['user_id'] = $login_result['id'];
        $_SESSION['username'] = $login_result['username'];
        $_SESSION['is_admin'] = $login_result['is_admin'];
        
        echo "<div style='color: green; padding: 15px; background: #d4edda; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>🎉 LOGIN SUCCESSFUL!</h4>";
        echo "User ID: " . $login_result['id'] . "<br>";
        echo "Username: " . $login_result['username'] . "<br>";
        echo "Admin: " . ($login_result['is_admin'] ? 'Yes' : 'No') . "<br>";
        
        if($login_result['is_admin']) {
            echo "<a href='admin/index.php' style='padding: 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin: 5px;'>Go to Admin Panel</a>";
        } else {
            echo "<a href='pages/products.php' style='padding: 10px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; margin: 5px;'>Go to Products</a>";
        }
        echo "</div>";
    }
}

?>

<form method="post" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3>🚨 Emergency Login Test</h3>
    <div style="margin: 10px 0;">
        <label>Username/Email:</label><br>
        <input type="text" name="username" value="labonysur" style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="margin: 10px 0;">
        <label>Password:</label><br>
        <input type="password" name="password" value="password" style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <button type="submit" name="emergency_login" value="1" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">🔐 Emergency Login</button>
</form>

<?php
// Step 6: Create test user for registration
echo "<h2>Step 6: Test User Registration</h2>";

if($_POST['emergency_register'] ?? false) {
    $reg_username = $_POST['reg_username'] ?? '';
    $reg_email = $_POST['reg_email'] ?? '';
    $reg_password = $_POST['reg_password'] ?? '';
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$reg_username, $reg_email]);
        
        if($stmt->fetchColumn() > 0) {
            echo "<div style='color: red;'>❌ Username or email already exists</div>";
        } else {
            // Create user
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

<form method="post" style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3>📝 Emergency Registration Test</h3>
    <div style="margin: 10px 0;">
        <label>Username:</label><br>
        <input type="text" name="reg_username" value="testuser<?= rand(100, 999) ?>" style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="margin: 10px 0;">
        <label>Email:</label><br>
        <input type="email" name="reg_email" value="test<?= rand(100, 999) ?>@example.com" style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="margin: 10px 0;">
        <label>Password:</label><br>
        <input type="password" name="reg_password" value="testpass123" style="padding: 8px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <button type="submit" name="emergency_register" value="1" style="padding: 10px 20px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">📝 Emergency Register</button>
</form>

<?php
echo "<h2>🔧 Quick Fix Actions</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h4>If login still doesn't work:</h4>";
echo "<ol>";
echo "<li>Use the emergency login form above</li>";
echo "<li>Check if your original login pages have session_start()</li>";
echo "<li>Verify database connection in includes/db.php</li>";
echo "<li>Make sure functions.php is properly included</li>";
echo "</ol>";
echo "</div>";

echo "<h3>Test Your Fixed System:</h3>";
echo "<a href='pages/login.php' target='_blank' style='margin: 5px; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>🔐 Test Login Page</a>";
echo "<a href='pages/register.php' target='_blank' style='margin: 5px; padding: 10px 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>📝 Test Register Page</a>";
echo "<a href='admin/admin_login.php' target='_blank' style='margin: 5px; padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>👨‍💼 Test Admin Login</a>";
?>
