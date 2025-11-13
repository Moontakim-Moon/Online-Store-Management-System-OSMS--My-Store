<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Not logged in. <a href='../direct_login.php'>Login here</a>");
}

echo "<h1>Debug Dashboard</h1>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test database connection
require_once '../includes/db.php';
try {
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch();
    echo "<p>Connected to database: " . htmlspecialchars($db['db']) . "</p>";
    
    // Get user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<h3>User Data:</h3>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Test if dashboard.php exists
        echo "<h3>Dashboard Test:</h3>";
        if (file_exists('dashboard.php')) {
            echo "<p>dashboard.php exists.</p>";
            // Include dashboard with output buffering to catch any errors
            ob_start();
            include 'dashboard.php';
            $output = ob_get_clean();
            if (empty($output)) {
                echo "<p style='color: red;'>dashboard.php is including but not showing any output. Check for errors in the included files.</p>";
            } else {
                echo $output;
            }
        } else {
            echo "<p style='color: red;'>dashboard.php not found in pages directory.</p>";
        }
    } else {
        echo "<p style='color: red;'>User not found in database.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Show PHP info
if (isset($_GET['phpinfo'])) {
    phpinfo();
    exit;
}
?>

<h3>Debug Options:</h3>
<ul>
    <li><a href="?phpinfo=1">Show PHP Info</a></li>
    <li><a href="../direct_login.php?logout=1">Logout</a></li>
    <li><a href="dashboard.php">Try Original Dashboard</a></li>
</ul>
