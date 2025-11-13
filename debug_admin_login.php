<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Login Debug</h2>";

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "232-15-473@Labony");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful<br>";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Check admin user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute(['labonysur']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✅ Admin user found<br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . " (Value: " . $admin['is_admin'] . ")<br>";
    echo "Email Verified: " . ($admin['email_verified'] ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ Admin user not found<br>";
}

// Test login process
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h3>Login Test Results:</h3>";
    
    // Get user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        echo "✅ Login successful<br>";
        
        // Set session variables exactly like login.php
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        
        echo "Session Variables Set:<br>";
        echo "- user_id: " . $_SESSION['user_id'] . "<br>";
        echo "- username: " . $_SESSION['username'] . "<br>";
        echo "- is_admin: " . ($_SESSION['is_admin'] ? 'true' : 'false') . "<br>";
        
        // Test admin check
        if (!isset($_SESSION["user_id"]) || !$_SESSION["is_admin"]) {
            echo "❌ Admin check failed<br>";
            echo "- user_id isset: " . (isset($_SESSION["user_id"]) ? 'Yes' : 'No') . "<br>";
            echo "- is_admin: " . ($_SESSION["is_admin"] ? 'Yes' : 'No') . "<br>";
        } else {
            echo "✅ Admin check passed<br>";
            echo '<a href="admin/index.php" style="background: green; color: white; padding: 10px; text-decoration: none;">Go to Admin Panel</a><br>';
        }
        
    } else {
        echo "❌ Login failed<br>";
    }
}

// Show current session
echo "<h3>Current Session:</h3>";
if (empty($_SESSION)) {
    echo "No session data<br>";
} else {
    foreach ($_SESSION as $key => $value) {
        echo $key . ": " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "<br>";
    }
}
?>

<form method="post">
    <h3>Test Admin Login:</h3>
    <input type="text" name="username" placeholder="Username" value="labonysur" required><br><br>
    <input type="password" name="password" placeholder="Password" value="password" required><br><br>
    <button type="submit" name="login">Test Login</button>
</form>

<hr>
<a href="pages/login.php">Go to main login</a> | 
<a href="admin/index.php">Try admin panel directly</a>
