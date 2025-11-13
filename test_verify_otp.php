<?php
require 'includes/db.php';
require 'includes/otp_handler.php';

$email = 'newuser@example.com';
$otp = '926929'; // The OTP we want to verify

$result = OTPHandler::verifyRegistrationOTP($email, $otp);
echo 'OTP verification result: ' . ($result ? 'SUCCESS' : 'FAILED') . '.';
?>
