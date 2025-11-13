<?php
require 'includes/db.php';
require 'includes/otp_handler_fixed.php';

$email = 'newuser@example.com';
$otp = '926929';

echo "=== Testing Fixed OTP Handler ===\n";
echo "Email: $email\n";
echo "OTP to verify: $otp\n";

// Check what's in the database
$stmt = $pdo->prepare("SELECT email_otp, email_otp_expires, email_verified FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Database OTP: '" . $user['email_otp'] . "'\n";
    echo "OTP Expires: " . $user['email_otp_expires'] . "\n";
    
    // Test the verification
    $result = OTPHandler::verifyRegistrationOTP($email, $otp);
    echo "Verification Result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    
    // Check if user is now verified
    $stmt = $pdo->prepare("SELECT email_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $verified = $stmt->fetchColumn();
    echo "Email Verified: " . ($verified ? "YES" : "NO") . "\n";
    
} else {
    echo "User not found!\n";
}
?>
