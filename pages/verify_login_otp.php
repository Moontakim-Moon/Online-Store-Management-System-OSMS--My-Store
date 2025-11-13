<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler_fixed.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    $email = $_SESSION['pending_login']['email'] ?? '';

    if (empty($otp)) {
        $error = 'Please enter the OTP.';
    } else {
        if (OTPHandlerFixed::verifyLoginOTP($email, $otp)) {
            // Successful OTP verification
            $userId = $_SESSION['pending_login']['user_id'];
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $_SESSION['pending_login']['username'];
            $_SESSION['is_admin'] = $_SESSION['pending_login']['is_admin'];
            unset($_SESSION['pending_login']); // Clear pending login data

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid OTP. Please try again.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="auth-container">
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="auth-card">
        <div class="auth-header">
            <h2>Verify OTP</h2>
            <p>Please enter the OTP sent to your email.</p>
        </div>

        <form method="post" action="verify_login_otp.php" class="auth-form">
            <div class="form-group">
                <label for="otp">OTP</label>
                <input type="text" name="otp" id="otp" required placeholder="Enter your OTP">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Verify OTP</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
