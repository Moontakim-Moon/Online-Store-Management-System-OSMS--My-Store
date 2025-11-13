<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Show PHP info
phpinfo();

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'includes/db.php';
    if (isset($pdo)) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test query
        $stmt = $pdo->query("SELECT DATABASE() as db");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Connected to database: " . htmlspecialchars($result['db']) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
