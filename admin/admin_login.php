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
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message" style="color: red; margin-bottom: 1rem; padding: 0.5rem; background: #ffe6e6; border: 1px solid #ff9999; border-radius: 4px;">
                Invalid username or password. Please try again.
            </div>
        <?php endif; ?>
        
        <form method="post" action="admin_login_process.php" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required value="labonysur">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required value="password">
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
        </div>
    </div>
</body>
</html>