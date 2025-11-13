<?php
require 'includes/db.php';
require 'includes/otp_handler.php';

$username = 'newuser';
$email = 'newuser@example.com';
$password = 'password';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert the user into the database
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, email_verified) VALUES (?, ?, ?, false)");
$stmt->execute([$username, $email, $hashed_password]);

// Send OTP for email verification
if (OTPHandler::sendRegistrationOTP($email, $username)) {
    echo "Test user created and OTP sent successfully.";
} else {
    echo "Failed to send OTP.";
}
?>
