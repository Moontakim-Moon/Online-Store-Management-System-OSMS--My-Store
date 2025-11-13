<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// User credentials
$test_username = 'time';
$test_email = 'timelesslyh@gmail.com';
$test_password = 'timelesslyh@gmail.com';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: test_user_dashboard.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';
    
    // Try to find user by username or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$test_username, $test_email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($test_password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        
        // Force non-admin mode for testing
        $_SESSION['is_admin'] = 0;
        
        // Redirect to test dashboard
        header('Location: test_user_dashboard.php');
        exit;
    } else {
        $error = "Invalid username/email or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test User Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .login-form { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        button { 
            background: #4CAF50; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px;
        }
        .error { 
            color: red; 
            margin-bottom: 15px;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
        }
        .debug-info {
            margin-top: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>Test User Login</h1>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="login-form">
        <form method="post">
            <div class="form-group">
                <label>Username or Email:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($test_username) ?>" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" value="<?= htmlspecialchars($test_password) ?>" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    
    <div class="debug-info">
        <h3>Test Credentials:</h3>
        <p>Username: <?= htmlspecialchars($test_username) ?></p>
        <p>Email: <?= htmlspecialchars($test_email) ?></p>
        <p>Password: [hidden]</p>
        
        <h3>Session Status:</h3>
        <pre><?= print_r($_SESSION, true) ?></pre>
    </div>
</body>
</html>
