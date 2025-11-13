<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler.php';
require_once '../includes/otp_handler_fixed.php'; // Added to include the OTPHandlerFixed class
session_start();

// Debug logging
function debug_log($message) {
    error_log(date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, 3, '../logs/debug.log');
}

debug_log('Login page accessed');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log('POST request received: ' . json_encode($_POST));
    
    // Verify username/password
    $usernameOrEmail = trim($_POST['usernameOrEmail'] ?? '');
    $password = $_POST['password'] ?? '';
        
    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'Please enter both username/email and password.';
        debug_log('Empty username or password');
    } else {
        debug_log('Attempting login for: ' . $usernameOrEmail);
        $user = loginUser($usernameOrEmail, $password);

        if ($user) {
            debug_log('Login successful for user: ' . $user['username'] . ' (ID: ' . $user['id'] . ')');
            // Check if email is verified
            if (!$user['email_verified']) {
                $error = 'Please verify your email first. <a href="verify_email.php">Click here to verify</a>';
                debug_log('Email not verified for user: ' . $user['username']);
            } else {
                debug_log('Email verified, proceeding with login');
                // Check if user is admin
                if ($user['is_admin']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = (bool)$user['is_admin'];
                    debug_log('Admin user logged in, redirecting to dashboard');
                    header('Location: /store/admin/index.php');
                    exit();
                } else {
                    // Directly log in the user
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = (bool)$user['is_admin'];
                    debug_log('Regular user logged in, redirecting to user_dashboard');
                    header('Location: /store/pages/user_dashboard.php');
                    exit();
                }
            }
        }else {
            $error = 'Invalid username/email or password.';
            debug_log('Invalid credentials for: ' . $usernameOrEmail);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .alert i {
            font-size: 1.2rem;
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
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="auth-card">
            <div class="auth-header">
                <h2><i class="fas fa-sign-in-alt"></i> Welcome Back</h2>
                <p class="auth-subtitle">Sign in to continue your journey</p>
            </div>

            <form method="post" action="login.php" class="auth-form">
                <div class="form-group">
                    <label for="usernameOrEmail">
                        <i class="fas fa-user"></i>
                        Username or Email
                    </label>
                    <input type="text" name="usernameOrEmail" id="usernameOrEmail" required 
                           placeholder="Enter your username or email"
                           value="<?= htmlspecialchars($_POST['usernameOrEmail'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" name="password" id="password" required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>New to our store? 
                    <a href="register.php" class="auth-link">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </p>
                <p>Forgot your password? 
                    <a href="forgot_password.php" class="auth-link">
                        <i class="fas fa-key"></i> Reset Password
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
