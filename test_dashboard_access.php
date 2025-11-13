<?php
// Test dashboard access and session handling
session_start();
require_once 'includes/functions.php';

echo "<h2>Dashboard Access Test</h2>";

// Check session status
echo "<h3>Session Information:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "User ID in session: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Username in session: " . ($_SESSION['username'] ?? 'Not set') . "<br>";
echo "Is Admin: " . ($_SESSION['is_admin'] ?? 'Not set') . "<br>";

// Test authentication functions
echo "<h3>Authentication Functions:</h3>";
echo "isLoggedIn(): " . (isLoggedIn() ? 'true' : 'false') . "<br>";
echo "currentUserId(): " . (currentUserId() ?? 'null') . "<br>";

// Test database connection
echo "<h3>Database Connection:</h3>";
try {
    require_once 'includes/db.php';
    echo "Database connection: SUCCESS<br>";
    
    // Check if user exists
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        echo "User data found: " . ($user ? 'YES' : 'NO') . "<br>";
        if ($user) {
            echo "Username: " . $user['username'] . "<br>";
            echo "Email: " . $user['email'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Test dashboard file access
echo "<h3>Dashboard File Access:</h3>";
$dashboardPath = 'pages/user_dashboard.php';
echo "Dashboard file exists: " . (file_exists($dashboardPath) ? 'YES' : 'NO') . "<br>";
echo "Dashboard file readable: " . (is_readable($dashboardPath) ? 'YES' : 'NO') . "<br>";

// Test CSS file access
$cssPath = 'assets/css/user_dashboard.css';
echo "CSS file exists: " . (file_exists($cssPath) ? 'YES' : 'NO') . "<br>";
echo "CSS file readable: " . (is_readable($cssPath) ? 'YES' : 'NO') . "<br>";

echo "<h3>Direct Dashboard Access Test:</h3>";
echo '<a href="pages/user_dashboard.php" target="_blank">Test Dashboard Link</a><br>';
echo '<a href="pages/login.php" target="_blank">Login Page</a><br>';
?>
