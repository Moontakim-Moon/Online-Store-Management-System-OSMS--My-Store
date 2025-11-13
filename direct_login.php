<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'includes/db.php';

// Direct login function
function directLogin($username, $password) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (directLogin($username, $password)) {
        header("Location: pages/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Direct Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .login-form { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Direct Login</h1>
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="login-form">
        <form method="post">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="labonysur" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" value="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    
    <div style="margin-top: 20px; color: #666;">
        <h3>Debug Info:</h3>
        <p>Session ID: <?= session_id() ?></p>
        <p>Session Data: <?= print_r($_SESSION, true) ?></p>
        <p>PHP Version: <?= phpversion() ?></p>
    </div>
</body>
</html>
