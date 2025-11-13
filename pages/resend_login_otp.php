<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler.php';
session_start();

$error = '';
$success = '';

// Check if user has temporary session data
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['temp_email'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['temp_email'];
    $username = $_SESSION['temp_username'];
    
    if (OTPHandler::sendLoginOTP($email, $username)) {
        $success = 'A new verification code has been sent to your email.';
    } else {
        $error = 'Failed to send OTP. Please try again.';
    }
}

include '../includes/header.php';
?>

<div class="resend-otp-container">
    <h2>Resend Login Verification Code</h2>
    
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="resend-info">
        <p>We'll send a new verification code to:</p>
        <p class="user-email"><?= htmlspecialchars($_SESSION['temp_email'] ?? '') ?></p>
    </div>
    
    <form method="post" action="resend_login_otp.php" class="resend-form">
        <button type="submit" class="btn btn-primary">Send New Code</button>
    </form>
    
    <div class="resend-actions">
        <a href="login.php" class="back-link">Back to Login</a>
        <a href="login.php?reset=1" class="reset-link">Start Over</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
