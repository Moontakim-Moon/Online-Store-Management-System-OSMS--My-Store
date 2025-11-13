<?php
require_once 'config.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send order confirmation email to customer
 */
function sendOrderConfirmationEmail($userId, $orderId, $orderNumber, $total, $items) {
    global $pdo;
    
    try {
        // Get user details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            error_log("User not found for order confirmation email: $userId");
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME ?? '';
        $mail->Password   = SMTP_PASSWORD ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT ?? 587;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL ?? 'noreply@store.com', SITE_NAME ?? 'Online Store');
        $mail->addAddress($user['email'], $user['username']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation - $orderNumber";
        
        // Generate email body
        $emailBody = generateOrderConfirmationEmailBody($user, $orderNumber, $orderId, $total, $items);
        $mail->Body = $emailBody;
        
        // Plain text version
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $emailBody));
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Order confirmation email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate HTML email body for order confirmation
 */
function generateOrderConfirmationEmailBody($user, $orderNumber, $orderId, $total, $items) {
    $siteName = SITE_NAME ?? 'Online Store';
    $currentDate = date('F j, Y');
    
    $itemsHtml = '';
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $itemsHtml .= "
            <tr style='border-bottom: 1px solid #eee;'>
                <td style='padding: 12px; text-align: left;'>" . htmlspecialchars($item['name']) . "</td>
                <td style='padding: 12px; text-align: center;'>" . $item['quantity'] . "</td>
                <td style='padding: 12px; text-align: right;'>$" . number_format($item['price'], 2) . "</td>
                <td style='padding: 12px; text-align: right; font-weight: bold;'>$" . number_format($itemTotal, 2) . "</td>
            </tr>
        ";
    }
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Order Confirmation</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
        <div style='max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1);'>
            <!-- Header -->
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center;'>
                <h1 style='margin: 0; font-size: 28px;'>$siteName</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Order Confirmation</p>
            </div>
            
            <!-- Content -->
            <div style='padding: 30px;'>
                <h2 style='color: #27ae60; margin-bottom: 20px;'>✅ Thank You for Your Order!</h2>
                
                <p>Dear " . htmlspecialchars($user['username']) . ",</p>
                
                <p>We're excited to confirm that we've received your order. Here are the details:</p>
                
                <!-- Order Info -->
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #495057;'>Order Information</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Order Number:</td>
                            <td style='padding: 8px 0;'>" . htmlspecialchars($orderNumber) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Order Date:</td>
                            <td style='padding: 8px 0;'>$currentDate</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Total Amount:</td>
                            <td style='padding: 8px 0; font-size: 18px; font-weight: bold; color: #27ae60;'>$" . number_format($total, 2) . "</td>
                        </tr>
                    </table>
                </div>
                
                <!-- Order Items -->
                <h3 style='color: #495057; margin-bottom: 15px;'>Items Ordered</h3>
                <table style='width: 100%; border-collapse: collapse; background: white; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;'>
                    <thead>
                        <tr style='background: #e9ecef;'>
                            <th style='padding: 12px; text-align: left; font-weight: bold;'>Product</th>
                            <th style='padding: 12px; text-align: center; font-weight: bold;'>Qty</th>
                            <th style='padding: 12px; text-align: right; font-weight: bold;'>Price</th>
                            <th style='padding: 12px; text-align: right; font-weight: bold;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        $itemsHtml
                        <tr style='background: #f8f9fa; font-weight: bold;'>
                            <td colspan='3' style='padding: 15px; text-align: right; font-size: 16px;'>Total Amount:</td>
                            <td style='padding: 15px; text-align: right; font-size: 18px; color: #27ae60;'>$" . number_format($total, 2) . "</td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Next Steps -->
                <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #2196f3;'>
                    <h3 style='margin-top: 0; color: #1976d2;'>What's Next?</h3>
                    <ul style='margin: 10px 0; padding-left: 20px;'>
                        <li>We'll process your order within 1-2 business days</li>
                        <li>You'll receive a shipping confirmation with tracking details</li>
                        <li>Expected delivery: 3-5 business days</li>
                    </ul>
                </div>
                
                <p>If you have any questions about your order, please don't hesitate to contact us.</p>
                
                <p style='margin-top: 30px;'>
                    Best regards,<br>
                    <strong>The $siteName Team</strong>
                </p>
            </div>
            
            <!-- Footer -->
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                <p style='margin: 0; font-size: 14px; color: #6c757d;'>
                    This is an automated message. Please do not reply to this email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Send order status update email
 */
function sendOrderStatusUpdateEmail($orderId, $newStatus) {
    global $pdo;
    
    try {
        // Get order and user details
        $stmt = $pdo->prepare("
            SELECT o.*, u.email, u.username, o.order_number 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME ?? '';
        $mail->Password   = SMTP_PASSWORD ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT ?? 587;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL ?? 'noreply@store.com', SITE_NAME ?? 'Online Store');
        $mail->addAddress($order['email'], $order['username']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Order Status Update - " . $order['order_number'];
        
        $statusMessages = [
            'pending' => 'Your order has been received and is being processed.',
            'processing' => 'Your order is currently being prepared for shipment.',
            'shipped' => 'Great news! Your order has been shipped and is on its way.',
            'delivered' => 'Your order has been successfully delivered.',
            'cancelled' => 'Your order has been cancelled.'
        ];
        
        $statusMessage = $statusMessages[$newStatus] ?? 'Your order status has been updated.';
        
        $mail->Body = generateStatusUpdateEmailBody($order, $newStatus, $statusMessage);
        $mail->AltBody = strip_tags($statusMessage);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Order status update email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate status update email body
 */
function generateStatusUpdateEmailBody($order, $newStatus, $statusMessage) {
    $siteName = SITE_NAME ?? 'Online Store';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Order Status Update</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #2c3e50;'>Order Status Update</h2>
            
            <p>Dear " . htmlspecialchars($order['username']) . ",</p>
            
            <p>Your order <strong>" . htmlspecialchars($order['order_number']) . "</strong> status has been updated to: <strong>" . ucfirst($newStatus) . "</strong></p>
            
            <p>$statusMessage</p>
            
            <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <strong>Order Details:</strong><br>
                Order Number: " . htmlspecialchars($order['order_number']) . "<br>
                Total: $" . number_format($order['total'], 2) . "<br>
                Status: " . ucfirst($newStatus) . "
            </div>
            
            <p>Thank you for shopping with us!</p>
            
            <p>Best regards,<br>The $siteName Team</p>
        </div>
    </body>
    </html>
    ";
}
?>
