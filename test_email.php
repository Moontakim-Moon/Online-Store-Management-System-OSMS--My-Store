<?php
require_once 'includes/email_sender.php';

$email = 'labonysur473@gmail.com';
$subject = 'Test Email';
$body = 'This is a test email to verify SMTP settings.';

if (EmailSender::sendEmail($email, $subject, $body)) {
    echo 'Email sent successfully!';
} else {
    echo 'Failed to send email.';
}
?>
