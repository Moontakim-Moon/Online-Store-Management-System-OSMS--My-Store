<?php
class EmailTemplates {
    public static function getRegistrationOTPTemplate($username, $otp) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #333; text-align: center;'>Email Verification</h2>
                <p>Hello {$username},</p>
                <p>Welcome to My Store! Please use the following OTP to verify your email address:</p>
                <div style='background: #fff; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;'>
                    <strong>{$otp}</strong>
                </div>
                <p>This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
                <p>If you didn't request this verification, please ignore this email.</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    This is an automated message, please do not reply.
                </p>
            </div>
        </div>";
    }

    public static function getLoginOTPTemplate($username, $otp) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #333; text-align: center;'>Login Verification</h2>
                <p>Hello {$username},</p>
                <p>We received a login request for your account. Please use the following OTP to complete your login:</p>
                <div style='background: #fff; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;'>
                    <strong>{$otp}</strong>
                </div>
                <p>This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
                <p>If you didn't request this login, please change your password immediately and contact our support team.</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    This is an automated message, please do not reply.
                </p>
            </div>
        </div>";
    }

    public static function getOrderOTPTemplate($order_id, $total, $otp) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #333; text-align: center;'>Payment Verification</h2>
                <p>Thank you for your order!</p>
                <div style='background: #fff; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <p><strong>Order ID:</strong> #{$order_id}</p>
                    <p><strong>Total Amount:</strong> ${$total}</p>
                </div>
                <p>Please use the following OTP to verify your payment:</p>
                <div style='background: #fff; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;'>
                    <strong>{$otp}</strong>
                </div>
                <p>This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.</p>
                <p>If you didn't make this purchase, please contact our support team immediately.</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    This is an automated message, please do not reply.
                </p>
            </div>
        </div>";
    }

    public static function getOrderConfirmationTemplate($order_id, $total, $payment_method) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #333; text-align: center;'>Order Confirmation</h2>
                <div style='background: #fff; padding: 20px; margin: 20px 0; border-radius: 5px;'>
                    <h3 style='color: #28a745; margin-top: 0;'>Order Successfully Confirmed!</h3>
                    <p><strong>Order ID:</strong> #{$order_id}</p>
                    <p><strong>Total Amount:</strong> ${$total}</p>
                    <p><strong>Payment Method:</strong> {$payment_method}</p>
                </div>
                <p>Thank you for shopping with us! We'll process your order right away.</p>
                <p>You can track your order status in your account dashboard.</p>
                <hr style='border: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px; text-align: center;'>
                    If you have any questions, please contact our support team.
                </p>
            </div>
        </div>";
    }
}
