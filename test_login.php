<?php
// Quick login test to debug the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login System Debug Test</h2>";

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "232-15-473@Labony");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful<br>";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test if admin user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute(['labonysur']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✅ Admin user found: " . $admin['username'] . "<br>";
    echo "Email verified: " . ($admin['email_verified'] ? 'Yes' : 'No') . "<br>";
    echo "Is admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ Admin user not found<br>";
    // Create admin user
    $password_hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified) VALUES (?, ?, ?, 1, 1)");
    if ($stmt->execute(['labonysur', 'labonysur6@gmail.com', $password_hash])) {
        echo "✅ Admin user created<br>";
    } else {
        echo "❌ Failed to create admin user<br>";
    }
}

// Test login function
if (isset($_POST['test_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h3>Testing Login:</h3>";
    echo "Username: " . htmlspecialchars($username) . "<br>";
    
    // Direct password verification
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User found<br>";
        if (password_verify($password, $user['password'])) {
            echo "✅ Password correct<br>";
            if ($user['email_verified']) {
                echo "✅ Email verified<br>";
                echo "<strong>LOGIN SUCCESS!</strong><br>";
                echo "User ID: " . $user['id'] . "<br>";
                echo "Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "<br>";
            } else {
                echo "❌ Email not verified<br>";
            }
        } else {
            echo "❌ Password incorrect<br>";
        }
    } else {
        echo "❌ User not found<br>";
    }
}
?>

<form method="post">
    <h3>Test Login:</h3>
    <input type="text" name="username" placeholder="Username" value="labonysur" required><br><br>
    <input type="password" name="password" placeholder="Password" value="password" required><br><br>
    <button type="submit" name="test_login">Test Login</button>
</form>

<hr>
<a href="pages/login.php">Go to actual login page</a>
