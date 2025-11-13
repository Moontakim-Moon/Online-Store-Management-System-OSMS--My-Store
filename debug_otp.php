<?php
require 'includes/db.php';
require 'includes/otp_handler.php';

$email = 'newuser@example.com';
$otp = '926929';

echo "=== OTP Debug Information ===\n";
echo "Email: $email\n";
echo "OTP to verify: $otp\n";

// Check what's in the database
$stmt = $pdo->prepare("SELECT email_otp, email_otp_expires, email_verified FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Database OTP: '" . $user['email_otp'] . "'\n";
    echo "OTP Length: " . strlen($user['email_otp']) . "\n";
    echo "OTP Expires: " . $user['email_otp_expires'] . "\n";
    echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Email Verified: " . $user['email_verified'] . "\n";
    
    // Check if OTP is expired
    $expiry = new DateTime($user['email_otp_expires']);
    $now = new DateTime();
    $isExpired = $now > $expiry;
    echo "Is Expired: " . ($isExpired ? "YES" : "NO") . "\n";
    
    // Check exact comparison
    echo "Exact Match: " . ($user['email_otp'] === $otp ? "YES" : "NO") . "\n";
    echo "Trimmed Match: " . (trim($user['email_otp']) === trim($otp) ? "YES" : "NO") . "\n";
    
    // Test the verification
    $result = OTPHandler::verifyRegistrationOTP($email, $otp);
    echo "Verification Result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    
} else {
    echo "User not found!\n";
}
?>
