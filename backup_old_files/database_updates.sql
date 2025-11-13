-- Database Schema Updates for OTP System
-- Run these SQL commands to update your database

-- Add OTP columns to users table
-- Remove duplicate column additions to avoid errors
-- ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
-- ALTER TABLE users MODIFY COLUMN email_otp VARCHAR(10) DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN email_otp_expires DATETIME DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL;

-- Create OTP tracking table for orders
CREATE TABLE order_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Create email templates table
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(50) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default email templates
INSERT INTO email_templates (template_name, subject, body) VALUES
('registration_otp', 'Verify Your Email - Registration OTP', 'Hello {username},<br><br>Your OTP for email verification is: <strong>{otp}</strong><br>This OTP will expire in 15 minutes.<br><br>Thank you!'),
('order_confirmation', 'Order Confirmation OTP - Order #{order_id}', 'Hello,<br><br>Your OTP to confirm order #{order_id} (Total: ${total}) is: <strong>{otp}</strong><br>This OTP will expire in 15 minutes.<br><br>Thank you for your order!'),
('order_success', 'Order Confirmed - Order #{order_id}', 'Hello,<br><br>Your order #{order_id} has been successfully confirmed!<br><br>Order Details:<br>- Total: ${total}<br>- Payment Method: {payment_method}<br><br>Thank you for shopping with us!');
