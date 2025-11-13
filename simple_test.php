<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Test Page</h1>";

echo "<h2>PHP Version: " . phpversion() . "</h2>";

// Test database connection
echo "<h3>Testing Database Connection...</h3>";
try {
    $host = "localhost";
    $dbname = "store";
    $username = "root";
    $password = "232-15-473@Labony";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // List tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables found: " . count($tables) . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Try to create database if it doesn't exist
    if (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "<p>Attempting to create database...</p>";
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            echo "<p style='color: green;'>✅ Database created successfully!</p>";
            echo "<p><a href='setup_database.php'>Click here to set up the database tables</a></p>";
        } catch (PDOException $e2) {
            echo "<p style='color: red;'>❌ Failed to create database: " . htmlspecialchars($e2->getMessage()) . "</p>";
        }
    }
}
?>
