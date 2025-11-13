<?php
// Fix admin password
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "232-15-473@Labony");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected<br>";
} catch(PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Generate new password hash for 'password'
$new_password_hash = password_hash('password', PASSWORD_DEFAULT);
echo "New password hash: " . $new_password_hash . "<br>";

// Update admin user
$stmt = $pdo->prepare("UPDATE users SET password = ?, is_admin = 1, email_verified = 1 WHERE username = 'labonysur'");
if ($stmt->execute([$new_password_hash])) {
    echo "✅ Admin password updated successfully<br>";
} else {
    echo "❌ Failed to update admin password<br>";
}

// Verify the update
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "<h3>Admin User Details:</h3>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "<br>";
    echo "Email Verified: " . ($admin['email_verified'] ? 'Yes' : 'No') . "<br>";
    
    // Test password verification
    if (password_verify('password', $admin['password'])) {
        echo "✅ Password verification works<br>";
    } else {
        echo "❌ Password verification failed<br>";
    }
} else {
    echo "❌ Admin user not found<br>";
}

echo "<hr>";
echo '<a href="debug_admin_login.php">Test login again</a> | ';
echo '<a href="pages/login.php">Go to main login</a>';
?>
