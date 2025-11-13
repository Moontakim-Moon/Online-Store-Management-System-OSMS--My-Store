<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$type = $_GET['type'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($type) {
        case 'registration':
            if (isset($_SESSION['temp_email']) && isset($_SESSION['temp_username'])) {
                if (OTPHandler::sendRegistrationOTP($_SESSION['temp_email'], $_SESSION['temp_username'])) {
                    $success = "New OTP sent successfully! Please check your email.";
                } else {
                    $error = "Failed to send new OTP. Please try again.";
                }
            } else {
                $error = "Session expired. Please register again.";
            }
            break;

        case 'payment':
            if (isset($_SESSION['order_id'])) {
                $stmt = $pdo->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
                $stmt->execute([$_SESSION['order_id']]);
                $order = $stmt->fetch();

                if ($order && OTPHandler::sendOrderOTP($order['id'], $order['email'], $order['total'])) {
                    $success = "New OTP sent successfully! Please check your email.";
                } else {
                    $error = "Failed to send new OTP. Please try again.";
                }
            } else {
                $error = "Order session expired. Please try again.";
            }
            break;

        default:
            $error = "Invalid OTP type.";
    }
}

include '../includes/header.php';
?>

<div class="resend-otp-container" style="max-width: 500px; margin: 30px auto; padding: 20px;">
    <?php if ($error): ?>
        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="post" action="resend_otp.php?type=<?= htmlspecialchars($type) ?>" class="resend-form">
            <h2>Resend OTP</h2>
            <p>Click the button below to get a new OTP.</p>
            <button type="submit" name="resend">Resend OTP</button>
        </form>
    <?php endif; ?>

    <?php if ($type === 'registration'): ?>
        <p><a href="verify_email.php">Back to Verification</a></p>
    <?php elseif ($type === 'payment'): ?>
        <p><a href="process_payment.php?order_id=<?= $_SESSION['order_id'] ?? '' ?>">Back to Payment</a></p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
