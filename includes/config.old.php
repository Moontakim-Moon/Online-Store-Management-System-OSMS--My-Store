<?php
// Email configuration
// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'labonysur473@gmail.com');
define('SMTP_PASSWORD', 'trni yftt fhxe fskg');
define('SMTP_FROM_EMAIL', 'labonysur473@gmail.com');
define('SMTP_FROM_NAME', 'My Store');ration
// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'labonysur473@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'trni yftt fhxe fskg'); // Your Gmail App Password
define('SMTP_FROM_EMAIL', 'labonysur473@gmail.com');
define('SMTP_FROM_NAME', 'My Store - E-commerce');mail configuration
// Update these settings with your SMTP provider details

// Example for Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'labonysur473@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'trni yftt fhxe fskg'); // Your Gmail App Password (not your Gmail login password)
define('SMTP_FROM_EMAIL', 'labonysur473@gmail.com');
define('SMTP_FROM_NAME', 'My Store');

// OTP Configuration
define('OTP_EXPIRY_MINUTES', 15);
define('OTP_LENGTH', 6);

// Base URL for links
define('BASE_URL', 'http://localhost/store/');

/*
Instructions:
- For Gmail, you need to create an App Password if you have 2FA enabled:
  https://support.google.com/accounts/answer/185833
- Replace 'your-email@gmail.com' and 'your-app-password' with your actual credentials.
- For other email providers, update SMTP_HOST, SMTP_PORT, SMTP_USERNAME, and SMTP_PASSWORD accordingly.
*/
?>
