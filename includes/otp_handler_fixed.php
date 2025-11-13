<?php
require_once 'db.php';
require_once 'config.php';
require_once 'email_sender.php';

class OTPHandlerFixed {
    public static function generateOTP($length = 6) {
        return str_pad(random_int(0, 999999), $length, '0', STR_PAD_LEFT);
    }
    
    public static function sendRegistrationOTP($email, $username) {
        require_once 'email_templates.php';
        
        $otp = self::generateOTP();
        $expires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        
        // Store OTP with proper formatting
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET email_otp = TRIM(?), email_otp_expires = ? WHERE email = ?");
        $stmt->execute([$otp, $expires, $email]);
        
        $subject = "Verify Your Email - Registration OTP";
        $body = EmailTemplates::getRegistrationOTPTemplate($username, $otp);
        
        return EmailSender::sendEmail($email, $subject, $body);
    }
    
    public static function sendLoginOTP($email, $username) {
        require_once 'email_templates.php';
        
        $otp = self::generateOTP();
        $expires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        
        // Store OTP with proper formatting
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET email_otp = TRIM(?), email_otp_expires = ? WHERE email = ?");
        $stmt->execute([$otp, $expires, $email]);
        
        $subject = "Login Verification - OTP";
        $body = EmailTemplates::getLoginOTPTemplate($username, $otp);
        
        return EmailSender::sendEmail($email, $subject, $body);
    }
    
    public static function verifyRegistrationOTP($email, $otp) {
        global $pdo;
        
        // Clean input OTP
        $otp = trim($otp);
        
        $stmt = $pdo->prepare("SELECT email_otp, email_otp_expires, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            error_log("OTP verification failed: user not found for email: $email");
            return false;
        }
        
        // Check if OTP is expired
        $expiry = new DateTime($user['email_otp_expires']);
        $now = new DateTime();
        
        if ($now > $expiry) {
            error_log("OTP verification failed: OTP expired for email: $email");
            return false;
        }
        
        // Check if OTP matches (case-insensitive)
        if (strcasecmp($user['email_otp'], $otp) === 0) {
            // Mark email as verified and clear OTP
            $updateStmt = $pdo->prepare("UPDATE users SET email_verified = 1, email_otp = NULL, email_otp_expires = NULL WHERE email = ?");
            $updateStmt->execute([$email]);
            error_log("OTP verification successful for email: $email");
            return true;
        }
        
        error_log("OTP verification failed: mismatch for email: $email");
        return false;
    }
    
    public static function verifyLoginOTP($email, $otp) {
        global $pdo;
        
        // Clean input OTP
        $otp = trim($otp);
        
        $stmt = $pdo->prepare("SELECT email_otp, email_otp_expires FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        // Check if OTP is expired
        $expiry = new DateTime($user['email_otp_expires']);
        $now = new DateTime();
        
        if ($now > $expiry) {
            return false;
        }
        
        // Check if OTP matches (case-insensitive)
        if (strcasecmp($user['email_otp'], $otp) === 0) {
            // Clear login OTP after successful verification
            $updateStmt = $pdo->prepare("UPDATE users SET email_otp = NULL, email_otp_expires = NULL WHERE email = ?");
            $updateStmt->execute([$email]);
            return true;
        }
        
        return false;
    }
    
    public static function verifyOrderOTP($order_id, $otp) {
        global $pdo;
        
        // Clean input OTP
        $otp = trim($otp);
        
        $stmt = $pdo->prepare("SELECT * FROM order_otp WHERE order_id = ? AND otp = ? AND expires_at > NOW() AND is_verified = FALSE");
        $stmt->execute([$order_id, $otp]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE order_otp SET is_verified = TRUE WHERE order_id = ? AND otp = ?");
            $stmt->execute([$order_id, $otp]);
            return true;
        }
        return false;
    }
}
?>
<content>
