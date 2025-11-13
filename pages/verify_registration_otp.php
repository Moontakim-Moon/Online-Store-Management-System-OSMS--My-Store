<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler_fixed.php';
require_once '../includes/db.php';
session_start();

$error = '';
$success = '';

// Check if user is coming from registration
if (!isset($_SESSION['pending_registration'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['pending_registration']['email'];
$username = $_SESSION['pending_registration']['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($otp)) {
        $error = 'Please enter the OTP code.';
    } else {
        // Verify OTP
        if (OTPHandlerFixed::verifyRegistrationOTP($email, $otp)) {
            // OTP verified successfully, mark email as verified
            $updateStmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE email = ?");
            $updateStmt->execute([$email]);
            
            // Clear pending registration
            unset($_SESSION['pending_registration']);
            
            $success = 'Registration successful! You can now log in.';
        } else {
            $error = 'Invalid or expired OTP code. Please try again.';
        }
    }
}

// Resend OTP functionality
if (isset($_POST['resend_otp'])) {
    $user = getUserByEmail($email);
    if ($user) {
        OTPHandler::sendRegistrationOTP($email, $username);
        $success = 'A new OTP has been sent to your email.';
    }
}

include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2><i class="fas fa-shield-alt"></i> Verify Your Registration</h2>
            <p class="auth-subtitle">Enter the OTP code sent to your email</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="post" action="verify_registration_otp.php" class="auth-form">
            <div class="form-group">
                <label for="otp">
                    <i class="fas fa-key"></i>
                    OTP Code
                </label>
                <input type="text" name="otp" id="otp" required 
                       placeholder="Enter 6-digit OTP"
                       maxlength="6"
                       pattern="\d{6}">
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-check"></i>
                Verify OTP
            </button>
        </form>

        <form method="post" action="verify_registration_otp.php" style="margin-top: 20px;">
            <button type="submit" name="resend_otp" class="btn btn-secondary btn-full">
                <i class="fas fa-redo"></i>
                Resend OTP
            </button>
        </form>

        <div class="auth-footer">
            <p>Having trouble? 
                <a href="register.php" class="auth-link">
                    <i class="fas fa-arrow-left"></i> Back to Registration
                </a>
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
