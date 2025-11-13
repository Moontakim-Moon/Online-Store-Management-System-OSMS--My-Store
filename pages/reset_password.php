<?php
require_once '../includes/functions.php';
session_start();

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;

// Validate token
if ($token) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "232-15-473@Labony");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT pr.*, u.username, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.expires_at > NOW()");
        $stmt->execute([$token]);
        $reset_data = $stmt->fetch();
        
        if ($reset_data) {
            $valid_token = true;
        } else {
            $error = 'Invalid or expired reset token. Please request a new password reset.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again later.';
    }
} else {
    $error = 'No reset token provided.';
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Please enter a new password.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Update user password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $reset_data['user_id']]);
            
            // Delete used reset token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'Your password has been successfully reset. You can now log in with your new password.';
            $valid_token = false; // Hide form after successful reset
        } catch (PDOException $e) {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #F4D03F;
            --primary-dark: #F1C40F;
            --secondary: #8E44AD;
            --accent: #E67E22;
            --bg-primary: #FFFBF0;
            --bg-card: rgba(255, 255, 255, 0.95);
            --text-primary: #2C3E50;
            --text-secondary: #7F8C8D;
            --shadow-soft: 0 10px 40px rgba(244, 208, 63, 0.15);
            --shadow-hover: 0 20px 60px rgba(244, 208, 63, 0.25);
            --gradient-bg: linear-gradient(135deg, #FFFBF0 0%, #F7DC6F 100%);
            --gradient-card: linear-gradient(145deg, rgba(255,255,255,0.95) 0%, rgba(247,220,111,0.1) 100%);
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
            --font-accent: 'Poppins', sans-serif;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            margin: 0;
            padding: 0;
            font-family: var(--font-body);
            background: var(--gradient-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(244, 208, 63, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(142, 68, 173, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(230, 126, 34, 0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        .auth-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            animation: slideInScale 0.6s ease-out;
        }
        
        .auth-card {
            background: var(--gradient-card);
            backdrop-filter: blur(30px);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            border: 1px solid rgba(244, 208, 63, 0.2);
            position: relative;
        }
        
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        }

        .auth-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(244, 208, 63, 0.05), transparent);
            animation: rotate 30s linear infinite;
            pointer-events: none;
            z-index: -1;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .auth-header {
            padding: 50px 40px 30px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .auth-header h2 {
            font-family: var(--font-heading);
            font-size: clamp(2.2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-header h2 i {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .auth-subtitle {
            font-family: var(--font-accent);
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 500;
        }
        
        .auth-form {
            padding: 20px 40px 40px;
            position: relative;
            z-index: 1;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-accent);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .form-group label i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        
        .form-group input {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid rgba(244, 208, 63, 0.3);
            border-radius: 16px;
            font-family: var(--font-body);
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-primary);
            transition: var(--transition);
            backdrop-filter: blur(10px);
            position: relative;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(244, 208, 63, 0.15);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.95);
        }

        .form-group input::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .password-strength {
            margin-top: 10px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .strength-indicator {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin: 8px 0;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #e74c3c; width: 25%; }
        .strength-fair { background: #f39c12; width: 50%; }
        .strength-good { background: #f1c40f; width: 75%; }
        .strength-strong { background: #27ae60; width: 100%; }
        
        .auth-btn {
            width: 100%;
            padding: 20px 30px;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-family: var(--font-accent);
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 60px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-soft);
            margin: 30px 0 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .auth-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: var(--transition);
        }
        
        .auth-btn:hover::before {
            left: 100%;
        }
        
        .auth-btn:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .auth-btn:active {
            transform: translateY(-2px);
        }
        
        .auth-footer {
            padding: 30px 40px 40px;
            text-align: center;
            border-top: 1px solid rgba(244, 208, 63, 0.2);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .auth-footer p {
            margin: 10px 0;
            font-family: var(--font-body);
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .auth-link {
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
            border-radius: 8px;
            font-family: var(--font-accent);
        }
        
        .auth-link:hover {
            color: var(--primary);
            background: rgba(244, 208, 63, 0.1);
            transform: translateY(-1px);
        }
        
        .alert {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            max-width: 450px;
            width: 90%;
            padding: 20px 25px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: var(--font-body);
            font-weight: 500;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(20px);
            animation: slideInScale 0.4s ease-out;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border: 2px solid rgba(231, 76, 60, 0.3);
        }
        
        .alert-success {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
            border: 2px solid rgba(39, 174, 96, 0.3);
        }

        .alert i {
            font-size: 1.2rem;
        }

        .user-info {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            color: #2980b9;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .user-info i {
            margin-right: 8px;
        }

        .success-message {
            text-align: center;
            padding: 40px 20px;
        }

        .success-message i {
            font-size: 4rem;
            color: #27ae60;
            margin-bottom: 20px;
        }

        .success-message h3 {
            font-family: var(--font-heading);
            font-size: 2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .success-message p {
            color: var(--text-secondary);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .auth-header {
                padding: 40px 30px 25px;
            }

            .auth-form {
                padding: 15px 30px 35px;
            }

            .auth-footer {
                padding: 25px 30px 35px;
            }
            
            .auth-header h2 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                max-width: 100%;
            }

            .auth-header {
                padding: 35px 20px 20px;
            }

            .auth-form {
                padding: 15px 20px 30px;
            }

            .auth-footer {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <div class="auth-card">
            <div class="auth-header">
                <h2><i class="fas fa-lock"></i> Reset Password</h2>
                <p class="auth-subtitle">Create a new secure password</p>
            </div>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h3>Password Reset Successful!</h3>
                    <p>Your password has been successfully updated. You can now sign in with your new password.</p>
                    <a href="login.php" class="auth-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In Now
                    </a>
                </div>
            <?php elseif ($valid_token): ?>
                <form method="post" action="reset_password.php?token=<?= htmlspecialchars($token) ?>" class="auth-form">
                    <div class="user-info">
                        <i class="fas fa-user"></i>
                        Resetting password for: <strong><?= htmlspecialchars($reset_data['username']) ?></strong> (<?= htmlspecialchars($reset_data['email']) ?>)
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            New Password
                        </label>
                        <input type="password" name="password" id="password" required
                               placeholder="Enter your new password" minlength="6">
                        <div class="password-strength">
                            <div class="strength-indicator">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <span id="strengthText">Password strength will appear here</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirm Password
                        </label>
                        <input type="password" name="confirm_password" id="confirm_password" required
                               placeholder="Confirm your new password" minlength="6">
                    </div>
                    
                    <button type="submit" class="auth-btn">
                        <i class="fas fa-save"></i>
                        Update Password
                    </button>
                </form>
            <?php else: ?>
                <div class="auth-form">
                    <div class="success-message">
                        <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                        <h3>Invalid Reset Link</h3>
                        <p>This password reset link is invalid or has expired. Please request a new password reset.</p>
                        <a href="forgot_password.php" class="auth-btn">
                            <i class="fas fa-redo"></i>
                            Request New Reset
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Remember your password? 
                    <a href="login.php" class="auth-link">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        if (passwordInput && strengthBar && strengthText) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = calculatePasswordStrength(password);
                
                // Remove all strength classes
                strengthBar.className = 'strength-bar';
                
                if (password.length === 0) {
                    strengthText.textContent = 'Password strength will appear here';
                    return;
                }
                
                if (strength.score <= 1) {
                    strengthBar.classList.add('strength-weak');
                    strengthText.textContent = 'Weak password';
                } else if (strength.score === 2) {
                    strengthBar.classList.add('strength-fair');
                    strengthText.textContent = 'Fair password';
                } else if (strength.score === 3) {
                    strengthBar.classList.add('strength-good');
                    strengthText.textContent = 'Good password';
                } else {
                    strengthBar.classList.add('strength-strong');
                    strengthText.textContent = 'Strong password';
                }
            });
        }

        function calculatePasswordStrength(password) {
            let score = 0;
            
            // Length check
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            // Character variety checks
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            return { score: Math.min(score, 4) };
        }

        // Password confirmation validation
        const confirmInput = document.getElementById('confirm_password');
        if (passwordInput && confirmInput) {
            confirmInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
            
            passwordInput.addEventListener('input', function() {
                if (confirmInput.value && confirmInput.value !== this.value) {
                    confirmInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmInput.setCustomValidity('');
                }
            });
        }
    </script>
</body>
</html>
