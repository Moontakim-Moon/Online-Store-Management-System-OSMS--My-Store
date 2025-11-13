<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../includes/otp_handler.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = currentUserId();
$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    header('Location: products.php');
    exit;
}

// Fetch order details
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    echo "Order not found or you do not have permission to view this order.";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';
    
    if ($payment_method === 'Cash on Delivery') {
        // For COD, directly update order
        $stmt = $pdo->prepare("UPDATE orders SET payment_method = ?, status = 'pending' WHERE id = ?");
        $stmt->execute([$payment_method, $orderId]);
        
        $_SESSION['success_message'] = "Order placed successfully! You will pay on delivery.";
        header('Location: orders.php');
        exit;
    } else {
        // For other payment methods, require OTP verification
        // Get user's email
        $stmt = $pdo->prepare("SELECT u.email FROM users u JOIN orders o ON u.id = o.user_id WHERE o.id = ?");
        $stmt->execute([$orderId]);
        $user = $stmt->fetch();
        
        if (isset($_POST['verify_otp'])) {
            // OTP verification
            $otp = $_POST['otp'] ?? '';
            if (OTPHandler::verifyOrderOTP($orderId, $otp)) {
                // Update order status
                $stmt = $pdo->prepare("UPDATE orders SET payment_method = ?, status = 'paid' WHERE id = ?");
                $stmt->execute([$payment_method, $orderId]);
                
                // Send success email
                OTPHandler::sendOrderSuccessEmail($user['email'], $orderId, $order['total'], $payment_method);
                
                $_SESSION['success_message'] = "Payment verified successfully!";
                header('Location: orders.php');
                exit;
            } else {
                $errors[] = "Invalid OTP. Please try again.";
            }
        } else {
            // Send OTP
            if (OTPHandler::sendOrderOTP($orderId, $user['email'], $order['total'])) {
                $_SESSION['payment_verification'] = true;
                $_SESSION['payment_method'] = $payment_method;
            } else {
                $errors[] = "Failed to send OTP. Please try again.";
            }
        }

    }
}

include '../includes/header.php';

// Display errors if any
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p style='color: red;'>" . htmlspecialchars($error) . "</p>";
    }
}

// Show OTP verification form if needed
if (isset($_SESSION['payment_verification'])) {
    ?>
    <div class="payment-verification">
        <h2>Payment Verification Required</h2>
        <p>An OTP has been sent to your email address. Please enter it below to confirm your payment.</p>
        
        <form method="post" action="process_payment.php?order_id=<?= $orderId ?>">
            <input type="hidden" name="payment_method" value="<?= htmlspecialchars($_SESSION['payment_method']) ?>">
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required pattern="[0-9]{6}" maxlength="6">
            </div>
            <button type="submit" name="verify_otp">Verify Payment</button>
        </form>
    </div>
    <?php
} else if (!isset($_POST['payment_method'])) {
    // Show initial payment form
    ?>
    <div class="payment-form">
        <h2>Choose Payment Method</h2>
        <form method="post" action="process_payment.php?order_id=<?= $orderId ?>">
            <div class="form-group">
                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Credit Card">Credit Card</option>
                </select>
            </div>
            <button type="submit">Continue to Payment</button>
        </form>
    </div>
    <?php
}
        <?php
        include '../includes/footer.php';
        exit;
    } else {
        // For Cash on Delivery, redirect to checkout page with success message
        header('Location: checkout.php?order_id=' . $orderId);
        exit;
    }
}

include '../includes/header.php';
?>

<h2>Proceed to Payment</h2>

<?php if (!empty($errors)): ?>
    <div style="color: red; margin-bottom: 15px;">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="process_payment.php?order_id=<?= htmlspecialchars($orderId) ?>">
    <label>
        <input type="radio" name="payment_method" value="bKash" required>
        bKash
    </label><br>
    <label>
        <input type="radio" name="payment_method" value="Nagad" required>
        Nagad
    </label><br>
    <label>
        <input type="radio" name="payment_method" value="Rocket" required>
        Rocket
    </label><br>
    <label>
        <input type="radio" name="payment_method" value="Cash on Delivery" required>
        Cash on Delivery
    </label><br><br>
    <button type="submit" style="width: 100%; padding: 15px; font-size: 1.2rem; background-color: #d4af37; color: white; border: none; border-radius: 8px; cursor: pointer;">
        Proceed to Payment
    </button>
</form>

<?php include '../includes/footer.php'; ?>
