<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Make sure we can find our include files
$includePath = realpath(__DIR__ . '/../includes');
require_once $includePath . '/config.php';
require_once $includePath . '/email_sender.php';
require_once $includePath . '/otp_handler.php';
require_once $includePath . '/logger.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email System Test</h2>";

// Show current PHP version and loaded extensions
echo "<h3>System Information:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded Extensions: " . implode(', ', get_loaded_extensions()) . "<br><br>";

// Show SMTP Configuration (excluding password)
echo "<h3>SMTP Configuration:</h3>";
echo "Host: " . SMTP_HOST . "<br>";
echo "Port: " . SMTP_PORT . "<br>";
echo "Username: " . SMTP_USERNAME . "<br>";
echo "From Email: " . SMTP_FROM_EMAIL . "<br>";
echo "From Name: " . SMTP_FROM_NAME . "<br><br>";

// Test email sending
$test_email = 'labonysur473@gmail.com'; // Your Gmail address from config.php
$test_username = 'Test User';

echo "<h3>Test Results:</h3>";
echo "Attempting to send test email to: $test_email<br>";

if (OTPHandler::sendRegistrationOTP($test_email, $test_username)) {
    echo "<p style='color: green;'>OTP sent successfully! Check your email and the log file.</p>";
} else {
    echo "<p style='color: red;'>Failed to send OTP. Check the log file at /logs/app.log for details.</p>";
}

// Display recent logs if they exist
$logFile = __DIR__ . '/../logs/app.log';
if (file_exists($logFile)) {
    echo "<h3>Recent Logs:</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($logFile)) . "</pre>";
} else {
    echo "<p>No log file found yet.</p>";
}
?>
