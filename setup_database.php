<?php
$host = "localhost";
$username = "root";
$password = "232-15-473@Labony";

try {
    // Connect without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Select the database
    $pdo->exec("USE store");
    
    // Import the SQL file
    $sql = file_get_contents('complete_database_final.sql');
    $pdo->exec($sql);
    
    echo "<h1>Database Setup Complete</h1>";
    echo "<p>Database 'store' has been created and tables have been imported.</p>";
    echo "<p><a href='test_db.php'>Check Database Connection</a></p>";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
