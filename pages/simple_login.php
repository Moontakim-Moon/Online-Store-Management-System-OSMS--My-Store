<?php
require_once '../includes/functions.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['usernameOrEmail'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = loginUser($usernameOrEmail, $password);

    if ($user) {
        // Check if email is verified
        if (!$user['email_verified']) {
            // Auto-verify admin users
            if ($user['is_admin']) {
                // Update email verification for admin
                global $pdo;
                $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
                $stmt->execute([$user['id']]);
                $user['email_verified'] = 1;
            }
        }
        
        if ($user['email_verified']) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect based on user role
            if ($user['is_admin']) {
                header('Location: ../admin/index.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error = 'Please verify your email first. <a href="verify_email.php">Click here to verify</a>';
        }
    } else {
        $error = 'Invalid username/email or password.';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="login-container">
    <h2>Login</h2>

    <?php if ($error): ?>
        <div class="error-message"><?= $error ?></div>
    <?php endif; ?>

    <div class="login-form-container">
        <form method="post" action="simple_login.php" class="login-form">
            <div class="form-group">
                <label for="usernameOrEmail">Username or Email:</label>
                <input type="text" name="usernameOrEmail" id="usernameOrEmail" required 
                       value="<?= htmlspecialchars($_POST['usernameOrEmail'] ?? '') ?>">
            </div>

            <div class="password-container">
                <label for="password">Password:</label>
                <div class="password-input-group">
                    <input type="password" name="password" id="password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="login-links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p>Forgot password? <a href="reset_password.php">Reset here</a></p>
            <p><strong>Admin Login:</strong> <a href="../admin/simple_admin_login.php">Click here</a></p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
