<?php
require_once 'config.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once 'logger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSender
{
    public static function sendEmail($to, $subject, $body)
    {
        Logger::log("Attempting to send email to: $to");
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0; // Disable debug output for production
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            // Optional settings for development/testing
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            //Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            Logger::log("SMTP Configuration:");
            Logger::log("Host: " . SMTP_HOST);
            Logger::log("Port: " . SMTP_PORT);
            Logger::log("Username: " . SMTP_USERNAME);
            Logger::log("From Email: " . SMTP_FROM_EMAIL);
            
            $mail->send();
            Logger::log("Email sent successfully to: $to", "SUCCESS");
            return true;
        } catch (Exception $e) {
            Logger::log("Email sending failed. Error: " . $mail->ErrorInfo, "ERROR");
            Logger::log("Full exception: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
}
