<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/functions.php';
require_once '../includes/otp_handler.php';
require_once '../includes/logger.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['temp_email'] ?? '';
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($email)) {
        $error = "Invalid session. Please try registering again.";
    } elseif (empty($otp)) {
        $error = "Please enter the OTP.";
    } else {
        if (OTPHandler::verifyRegistrationOTP($email, $otp)) {
            $success = "Email verified successfully! You can now login.";
            unset($_SESSION['temp_email']);
            unset($_SESSION['temp_username']);
            unset($_SESSION['success_message']);
        } else {
            $error = "Invalid OTP or OTP expired. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<h2>Verify Email</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <div class="verification-success">
        <p class="success-message"><?= $success ?></p>
        <a href="login.php" class="btn-primary">Login Now</a>
    </div>
<?php else: ?>
    <div class="verification-container">
        <div class="otp-box">
            <p class="otp-instruction">Please enter the verification code sent to your email address</p>
            <form method="post" action="verify_email.php" class="otp-form">
                <div class="otp-input-group">
                    <label for="otp">Verification Code:</label>
                    <input type="text" name="otp" id="otp" required maxlength="6" placeholder="Enter 6-digit code">
                </div>
                <button type="submit" class="btn-verify">Verify Email</button>
            </form>
            <div class="resend-otp">
                <p>Didn't receive the code?</p>
                <a href="resend_otp.php" class="resend-link">Resend Code</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
