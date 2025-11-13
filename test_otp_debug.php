<?php
require_once 'includes/db.php';
require_once 'includes/otp_handler.php';

// Test OTP functionality
$email = "test@example.com";
$test_otp = "123456";

// First, let's create a test user
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, email_otp, email_otp_expires) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
    $stmt->execute(['testuser', $email, password_hash('testpass', PASSWORD_DEFAULT), $test_otp]);
    echo "Test user created successfully.<br>";
    
    // Now test OTP verification
    $result = OTPHandler::verifyRegistrationOTP($email, $test_otp);
    echo "OTP verification result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
    
    // Clean up
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    echo "Test user cleaned up.<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
