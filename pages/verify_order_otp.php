<?php
require_once '../includes/functions.php';
require_once '../includes/otp_handler_fixed.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_SESSION['order_id'] ?? 0;
    $email = $_SESSION['order_email'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    if (empty($order_id) || empty($email)) {
        $error = "Invalid session. Please try again.";
    } elseif (empty($otp)) {
        $error = "Please enter the OTP.";
    } else {
        if (OTPHandlerFixed::verifyOrderOTP($order_id, $otp)) {
            $success = "Order confirmed successfully! Payment will be processed.";
            unset($_SESSION['order_id'], $_SESSION['order_email']);
        } else {
            $error = "Invalid OTP or OTP expired. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<h2>Order Confirmation</h2>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
    <p><a href="orders.php">View Orders</a></p>
<?php else: ?>
    <form method="post" action="verify_order_otp.php">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" id="otp" required>
        <button type="submit">Confirm Order</button>
    </form>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
