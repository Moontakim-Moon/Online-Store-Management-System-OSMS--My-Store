<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "232-15-473@Labony");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_token (token),
        INDEX idx_expires (expires_at),
        INDEX idx_user_id (user_id)
    )";
    
    $pdo->exec($sql);
    echo "✅ Password reset table created successfully!\n";
    
    // Clean up any expired tokens
    $cleanup = "DELETE FROM password_resets WHERE expires_at < NOW()";
    $pdo->exec($cleanup);
    echo "✅ Cleaned up expired tokens\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
