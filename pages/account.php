<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();
$error = '';
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user_data = $stmt->fetch();
            
            if (password_verify($current_password, $user_data['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $userId]);
                $success = 'Password changed successfully!';
            } else {
                $error = 'Current password is incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update password. Please try again.';
        }
    }
}

global $pdo;
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

<style>
    .account-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .account-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(244, 208, 63, 0.15);
        border: 1px solid rgba(244, 208, 63, 0.2);
    }
    
    .account-section h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: #2C3E50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .account-section h3 i {
        color: #F4D03F;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid rgba(244, 208, 63, 0.1);
        font-family: 'Inter', sans-serif;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-item i {
        color: #F4D03F;
        width: 20px;
        text-align: center;
    }
    
    .info-label {
        font-weight: 600;
        color: #2C3E50;
        min-width: 100px;
    }
    
    .info-value {
        color: #7F8C8D;
        font-weight: 500;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: #2C3E50;
        margin-bottom: 10px;
        font-size: 1rem;
    }
    
    .form-group label i {
        color: #F4D03F;
        width: 20px;
        text-align: center;
    }
    
    .form-group input {
        width: 100%;
        padding: 15px 18px;
        border: 2px solid rgba(244, 208, 63, 0.3);
        border-radius: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.8);
        color: #2C3E50;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #F4D03F;
        box-shadow: 0 0 0 4px rgba(244, 208, 63, 0.15);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #F4D03F, #F1C40F);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 12px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(244, 208, 63, 0.3);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #8E44AD, #9B59B6);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        margin-top: 15px;
    }
    
    .btn-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(142, 68, 173, 0.3);
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-family: 'Inter', sans-serif;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-error {
        background: rgba(231, 76, 60, 0.1);
        color: #c0392b;
        border: 1px solid rgba(231, 76, 60, 0.3);
    }
    
    .alert-success {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        border: 1px solid rgba(39, 174, 96, 0.3);
    }
    
    .password-form {
        display: none;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(244, 208, 63, 0.2);
    }
    
    .password-form.active {
        display: block;
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
    
    .password-strength {
        margin-top: 8px;
        font-size: 0.85rem;
        color: #7F8C8D;
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="account-container">
    <div class="account-section">
        <h3><i class="fas fa-user"></i> Account Information</h3>
        
        <div class="info-item">
            <i class="fas fa-user"></i>
            <span class="info-label">Username:</span>
            <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
        </div>
        
        <div class="info-item">
            <i class="fas fa-envelope"></i>
            <span class="info-label">Email:</span>
            <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
        </div>
    </div>
    
    <div class="account-section">
        <h3><i class="fas fa-shield-alt"></i> Security Settings</h3>
        
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
        
        <p style="color: #7F8C8D; margin-bottom: 20px;">
            <i class="fas fa-info-circle" style="color: #3498db; margin-right: 8px;"></i>
            Keep your account secure by using a strong password and changing it regularly.
        </p>
        
        <button type="button" class="btn-secondary" onclick="togglePasswordForm()">
            <i class="fas fa-key"></i>
            Change Password
        </button>
        
        <a href="forgot_password.php" class="btn-secondary" style="margin-left: 15px;">
            <i class="fas fa-envelope"></i>
            Reset via Email
        </a>
        
        <form method="post" class="password-form" id="passwordForm">
            <div class="form-group">
                <label for="current_password">
                    <i class="fas fa-lock"></i>
                    Current Password
                </label>
                <input type="password" name="current_password" id="current_password" required
                       placeholder="Enter your current password">
            </div>
            
            <div class="form-group">
                <label for="new_password">
                    <i class="fas fa-key"></i>
                    New Password
                </label>
                <input type="password" name="new_password" id="new_password" required
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
                    <i class="fas fa-check"></i>
                    Confirm New Password
                </label>
                <input type="password" name="confirm_password" id="confirm_password" required
                       placeholder="Confirm your new password" minlength="6">
            </div>
            
            <button type="submit" name="change_password" class="btn-primary">
                <i class="fas fa-save"></i>
                Update Password
            </button>
        </form>
    </div>
</div>

<script>
function togglePasswordForm() {
    const form = document.getElementById('passwordForm');
    form.classList.toggle('active');
}

// Password strength checker
const passwordInput = document.getElementById('new_password');
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

<?php include '../includes/footer.php'; ?>
