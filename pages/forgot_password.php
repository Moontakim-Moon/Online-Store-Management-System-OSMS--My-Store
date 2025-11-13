<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if user exists
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=store", "root", "232-15-473@Labony");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token in database
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
                $stmt->execute([$user['id'], $reset_token, $expires_at, $reset_token, $expires_at]);
                
                // Send reset email
                $reset_link = "http://localhost/store/pages/reset_password.php?token=" . $reset_token;
                $subject = "Password Reset Request - My Store";
                $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #F4D03F, #8E44AD); padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .header h1 { color: white; margin: 0; font-size: 24px; }
                        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; background: linear-gradient(135deg, #F4D03F, #F1C40F); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Password Reset Request</h1>
                        </div>
                        <div class='content'>
                            <p>Hello " . htmlspecialchars($user['username']) . ",</p>
                            <p>We received a request to reset your password for your My Store account.</p>
                            <p>Click the button below to reset your password:</p>
                            <p><a href='" . $reset_link . "' class='button'>Reset Password</a></p>
                            <p>Or copy and paste this link into your browser:</p>
                            <p style='word-break: break-all; background: #fff; padding: 10px; border-radius: 5px;'>" . $reset_link . "</p>
                            <p><strong>This link will expire in 1 hour.</strong></p>
                            <p>If you didn't request this password reset, please ignore this email.</p>
                            <p>Best regards,<br>The My Store Team</p>
                        </div>
                        <div class='footer'>
                            <p>This is an automated message. Please do not reply to this email.</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: My Store <noreply@mystore.com>" . "\r\n";
                
                if (mail($email, $subject, $message, $headers)) {
                    $success = 'Password reset instructions have been sent to your email address.';
                } else {
                    $error = 'Failed to send reset email. Please try again later.';
                }
            } else {
                // Don't reveal if email exists or not for security
                $success = 'If an account with that email exists, password reset instructions have been sent.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Store</title>
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

        .info-text {
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            color: #2980b9;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-text i {
            margin-right: 8px;
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
                <h2><i class="fas fa-key"></i> Forgot Password</h2>
                <p class="auth-subtitle">Reset your account password</p>
            </div>

            <form method="post" action="forgot_password.php" class="auth-form">
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    Enter your email address and we'll send you instructions to reset your password.
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" name="email" id="email" required
                           placeholder="Enter your email address"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-paper-plane"></i>
                    Send Reset Instructions
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Remember your password? 
                    <a href="login.php" class="auth-link">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                </p>
                <p>Don't have an account? 
                    <a href="register.php" class="auth-link">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
