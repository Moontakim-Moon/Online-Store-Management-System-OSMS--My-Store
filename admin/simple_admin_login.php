<?php
session_start();
require_once "../includes/functions.php";

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION["user_id"]) && $_SESSION["is_admin"]) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    // Simple admin check - bypass OTP system
    if ($username === "labonysur" && $password === "password") {
        // Get user from database to get proper ID
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Set admin session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["is_admin"] = true;
            $_SESSION["email"] = $user["email"];
            
            // Redirect to admin dashboard
            header("Location: index.php");
            exit();
        } else {
            $error = "Admin user not found in database.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - My Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container" style="max-width: 400px; margin: 100px auto; padding: 2rem;">
        <h2><i class="fas fa-user-shield"></i> Admin Login</h2>
        
        <?php if ($error): ?>
            <div class="error-message" style="background: #fee; color: #c33; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fcc;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="simple_admin_login.php" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required 
                       value="<?= htmlspecialchars($_POST['username'] ?? 'labonysur') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required 
                       value="<?= htmlspecialchars($_POST['password'] ?? 'password') ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Admin Login
            </button>
        </form>
        
        <div style="margin-top: 1rem; text-align: center;">
            <a href="../pages/login.php" style="color: var(--primary-color);">
                <i class="fas fa-arrow-left"></i> Back to Regular Login
            </a>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: var(--card-bg); border-radius: var(--border-radius); border: 1px solid var(--border-color);">
            <h4><i class="fas fa-info-circle"></i> Admin Login Info:</h4>
            <p><strong>Username:</strong> labonysur</p>
            <p><strong>Password:</strong> password</p>
            <p><strong>Email:</strong> labonysur6@gmail.com</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 1rem;">
                <i class="fas fa-exclamation-triangle"></i> 
                This bypasses the OTP system for direct admin access.
            </p>
        </div>
    </div>
</body>
</html>
