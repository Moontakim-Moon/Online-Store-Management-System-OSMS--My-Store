<?php
require_once 'includes/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Check if PDO connection is established
    if (isset($pdo)) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // List all tables in the database
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<h3>Tables in database:</h3>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul>";
            
            // Check if users table exists and has data
            if (in_array('users', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<p>Total users in database: " . $userCount . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ No tables found in the database.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Database connection failed: PDO object not created.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>PHP Info</h2>
echo "<p>PHP Version: " . phpversion() . "</p>";
?>
