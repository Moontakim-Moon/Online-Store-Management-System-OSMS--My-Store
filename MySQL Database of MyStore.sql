

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS store;
CREATE DATABASE store
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE store;

-- Set session character set and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- TABLE CREATION
-- =====================================================

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_otp VARCHAR(10) DEFAULT NULL,
    email_otp_expires DATETIME DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(50) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    country VARCHAR(50) DEFAULT 'Bangladesh',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name_parent (name, parent_id),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(100) UNIQUE,
    weight DECIMAL(8,2) DEFAULT NULL,
    dimensions VARCHAR(100) DEFAULT NULL,
    image VARCHAR(255),
    gallery TEXT, -- JSON array of image URLs
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product attributes table (for size, color, etc.)
CREATE TABLE product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(100) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    billing_address TEXT,
    customer_notes TEXT,
    admin_notes TEXT,
    tracking_number VARCHAR(100),
    shipped_at DATETIME NULL,
    delivered_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL, -- Store product name at time of order
    product_sku VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    product_attributes JSON, -- Store selected attributes (size, color, etc.)
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart items table
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    attributes JSON, -- Store selected attributes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Product images table
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tags table
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product tags table
CREATE TABLE product_tags (
    product_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Product reviews table
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT, -- Link to order for verified purchases
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(255),
    review_text TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- Review helpfulness table
CREATE TABLE review_helpfulness (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    is_helpful BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_review (user_id, review_id)
);

-- Coupons table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('fixed', 'percentage') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    starts_at DATETIME,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupon usage table
CREATE TABLE coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- OTP tracking table for orders
CREATE TABLE order_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Login OTP table
CREATE TABLE login_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Email templates table
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(50) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables TEXT, -- JSON array of available variables
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin activity log
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- User indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_is_admin ON users(is_admin);

-- Product indexes
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_stock ON products(stock);
CREATE INDEX idx_products_sku ON products(sku);

-- Order indexes
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_date ON orders(created_at);
CREATE INDEX idx_orders_number ON orders(order_number);

-- Cart indexes
CREATE INDEX idx_cart_user ON cart_items(user_id);
CREATE INDEX idx_cart_product ON cart_items(product_id);

-- Review indexes
CREATE INDEX idx_reviews_product ON product_reviews(product_id);
CREATE INDEX idx_reviews_user ON product_reviews(user_id);
CREATE INDEX idx_reviews_rating ON product_reviews(rating);

-- Category indexes
CREATE INDEX idx_categories_parent ON categories(parent_id);
CREATE INDEX idx_categories_active ON categories(is_active);

-- =====================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- Insert main categories
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES 
('Men', NULL, 'Men\'s fashion and accessories', TRUE, 1),
('Women', NULL, 'Women\'s fashion and accessories', TRUE, 2),
('Electronics', NULL, 'Electronic devices and gadgets', TRUE, 3),
('Home & Garden', NULL, 'Home decor and garden items', TRUE, 4);

-- Insert Men subcategories
SET @men_id = (SELECT id FROM categories WHERE name = 'Men' AND parent_id IS NULL);
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES
('Clothing', @men_id, 'Men\'s clothing items', TRUE, 1),
('Shoes', @men_id, 'Men\'s footwear', TRUE, 2),
('Accessories', @men_id, 'Men\'s accessories', TRUE, 3);

-- Insert Women subcategories
SET @women_id = (SELECT id FROM categories WHERE name = 'Women' AND parent_id IS NULL);
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES
('Clothing', @women_id, 'Women\'s clothing items', TRUE, 1),
('Bags', @women_id, 'Women\'s handbags and purses', TRUE, 2),
('Hair Accessories', @women_id, 'Hair clips, bands, and accessories', TRUE, 3),
('Jewelry', @women_id, 'Women\'s jewelry and accessories', TRUE, 4);

-- Insert sample products for Men's Clothing
SET @men_clothing_id = (SELECT id FROM categories WHERE name = 'Clothing' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, sale_price, stock, sku, image, status, featured) VALUES
(@men_clothing_id, 'Classic Black Suit', 'Elegant black suit perfect for formal occasions. Made from premium wool blend fabric with excellent tailoring. Includes jacket and trousers.', 'Premium wool blend black suit for formal occasions', 299.99, 249.99, 15, 'MEN-SUIT-001', 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=500', 'active', TRUE),
(@men_clothing_id, 'Navy Blue Blazer', 'Sophisticated navy blue blazer suitable for business and casual wear. Comfortable fit with modern styling.', 'Versatile navy blue blazer for business and casual wear', 159.99, NULL, 20, 'MEN-BLAZER-001', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500', 'active', FALSE),
(@men_clothing_id, 'White Dress Shirt', 'Crisp white dress shirt made from 100% cotton. Perfect for office wear and formal events.', 'Classic white cotton dress shirt', 54.99, 44.99, 30, 'MEN-SHIRT-001', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500', 'active', FALSE),
(@men_clothing_id, 'Casual Jeans', 'Comfortable slim-fit jeans made from premium denim. Perfect for everyday wear.', 'Premium slim-fit denim jeans', 79.99, NULL, 25, 'MEN-JEANS-001', 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500', 'active', FALSE);

-- Insert sample products for Men's Shoes
SET @men_shoes_id = (SELECT id FROM categories WHERE name = 'Shoes' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@men_shoes_id, 'Oxford Dress Shoes', 'Classic leather Oxford shoes perfect for formal occasions. Handcrafted with genuine leather and comfortable sole.', 'Classic leather Oxford dress shoes', 129.99, 18, 'MEN-SHOES-001', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500', 'active'),
(@men_shoes_id, 'Casual Sneakers', 'Comfortable casual sneakers for everyday wear. Breathable material with excellent cushioning.', 'Comfortable casual sneakers', 89.99, 22, 'MEN-SNEAKERS-001', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=500', 'active');

-- Insert sample products for Men's Accessories
SET @men_accessories_id = (SELECT id FROM categories WHERE name = 'Accessories' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@men_accessories_id, 'Leather Wallet', 'Premium leather wallet with multiple card slots and bill compartment. RFID blocking technology included.', 'Premium leather wallet with RFID protection', 49.99, 35, 'MEN-WALLET-001', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500', 'active'),
(@men_accessories_id, 'Silk Tie', 'Elegant silk tie perfect for business and formal occasions. Available in classic patterns.', 'Premium silk tie for formal wear', 29.99, 40, 'MEN-TIE-001', 'https://images.unsplash.com/photo-1521369909029-2afed882baee?w=500', 'active');

-- Insert sample products for Women's Clothing
SET @women_clothing_id = (SELECT id FROM categories WHERE name = 'Clothing' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, sale_price, stock, sku, image, status, featured) VALUES
(@women_clothing_id, 'Floral Summer Dress', 'Beautiful floral summer dress perfect for casual outings. Lightweight and comfortable fabric with elegant design.', 'Elegant floral summer dress', 69.99, 55.99, 20, 'WOMEN-DRESS-001', 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500', 'active', TRUE),
(@women_clothing_id, 'Evening Gown', 'Sophisticated evening gown for special occasions. Elegant design with premium fabric and perfect fit.', 'Sophisticated evening gown', 199.99, NULL, 8, 'WOMEN-GOWN-001', 'https://images.unsplash.com/photo-1566479179817-c0b2b3b7b5b5?w=500', 'active', FALSE);

-- Insert sample products for Women's Bags
SET @women_bags_id = (SELECT id FROM categories WHERE name = 'Bags' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@women_bags_id, 'Leather Handbag', 'Stylish leather handbag perfect for daily use. Multiple compartments with secure zippers and comfortable handles.', 'Stylish leather handbag', 89.99, 15, 'WOMEN-BAG-001', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500', 'active'),
(@women_bags_id, 'Designer Clutch', 'Elegant designer clutch perfect for evening events. Compact design with premium materials.', 'Elegant designer clutch', 45.99, 12, 'WOMEN-CLUTCH-001', 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500', 'active');

-- Insert sample products for Women's Hair Accessories
SET @women_hair_id = (SELECT id FROM categories WHERE name = 'Hair Accessories' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@women_hair_id, 'Pearl Hair Clip', 'Elegant pearl hair clip perfect for special occasions. High-quality materials with secure grip.', 'Elegant pearl hair clip', 15.99, 50, 'WOMEN-CLIP-001', 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=500', 'active'),
(@women_hair_id, 'Silk Hair Band', 'Luxurious silk hair band for comfortable and stylish hair management. Gentle on hair with elegant design.', 'Luxurious silk hair band', 12.99, 60, 'WOMEN-BAND-001', 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=500', 'active');

-- =====================================================
-- CREATE ADMIN USER
-- =====================================================

-- Insert admin user with verified email
INSERT INTO users (username, email, password, is_admin, email_verified, full_name, created_at) VALUES 
('labonysur', 'labonysur6@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, TRUE, 'Admin User', NOW());

-- Insert a sample regular user for testing
INSERT INTO users (username, email, password, is_admin, email_verified, full_name, created_at) VALUES 
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, TRUE, 'Test User', NOW());

-- =====================================================
-- SAMPLE ORDERS AND REVIEWS
-- =====================================================

-- Insert sample order
INSERT INTO orders (user_id, order_number, total, subtotal, status, payment_method, shipping_address, created_at) VALUES 
(2, 'ORD-2024-001', 159.98, 159.98, 'delivered', 'Cash on Delivery', '123 Test Street, Dhaka, Bangladesh', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Insert order items
INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES 
(1, 1, 'Classic Black Suit', 1, 249.99, 249.99),
(1, 3, 'White Dress Shirt', 2, 44.99, 89.98);

-- Insert sample reviews
INSERT INTO product_reviews (product_id, user_id, order_id, rating, title, review_text, is_verified, created_at) VALUES 
(1, 2, 1, 5, 'Excellent Quality!', 'Amazing suit with perfect fit. Highly recommended for formal occasions.', TRUE, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 2, 1, 4, 'Good shirt', 'Nice quality shirt, comfortable to wear.', TRUE, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- =====================================================
-- EMAIL TEMPLATES
-- =====================================================

INSERT INTO email_templates (template_name, subject, body, variables) VALUES 
('registration_otp', 'Verify Your Email - My Store', 
'<h2>Welcome to My Store!</h2><p>Hello {username},</p><p>Thank you for registering with us. Your OTP for email verification is:</p><h3 style="color: #007cba; text-align: center; font-size: 24px; letter-spacing: 3px;">{otp}</h3><p>This OTP will expire in 15 minutes.</p><p>If you did not request this, please ignore this email.</p><p>Best regards,<br>My Store Team</p>', 
'["username", "otp"]'),

('order_confirmation', 'Order Confirmation - My Store', 
'<h2>Order Confirmation Required</h2><p>Hello,</p><p>Thank you for your order! To confirm your order #{order_id}, please use the following OTP:</p><h3 style="color: #007cba; text-align: center; font-size: 24px; letter-spacing: 3px;">{otp}</h3><p><strong>Order Details:</strong></p><ul><li>Order Number: #{order_id}</li><li>Total Amount: ${total}</li><li>Payment Method: {payment_method}</li></ul><p>This OTP will expire in 15 minutes.</p><p>Best regards,<br>My Store Team</p>', 
'["order_id", "total", "payment_method", "otp"]'),

('order_success', 'Order Confirmed - My Store', 
'<h2>Order Successfully Confirmed!</h2><p>Hello,</p><p>Your order has been successfully confirmed and is now being processed.</p><p><strong>Order Details:</strong></p><ul><li>Order Number: #{order_id}</li><li>Total Amount: ${total}</li><li>Payment Method: {payment_method}</li><li>Status: Confirmed</li></ul><p>You will receive another email once your order is shipped.</p><p>Thank you for shopping with us!</p><p>Best regards,<br>My Store Team</p>', 
'["order_id", "total", "payment_method"]');

-- =====================================================
-- SYSTEM SETTINGS
-- =====================================================

INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES 
('site_name', 'My Store', 'string', 'Website name'),
('site_description', 'Your one-stop shop for fashion and accessories', 'string', 'Website description'),
('currency', 'BDT', 'string', 'Default currency'),
('tax_rate', '0.00', 'number', 'Tax rate percentage'),
('shipping_cost', '50.00', 'number', 'Default shipping cost'),
('free_shipping_threshold', '1000.00', 'number', 'Minimum order for free shipping'),
('order_otp_required', 'true', 'boolean', 'Require OTP for order confirmation'),
('email_verification_required', 'true', 'boolean', 'Require email verification for registration'),
('admin_email', 'labonysur6@gmail.com', 'string', 'Admin email address'),
('smtp_enabled', 'true', 'boolean', 'Enable SMTP email sending');

-- =====================================================
-- SAMPLE COUPONS
-- =====================================================

INSERT INTO coupons (code, type, value, minimum_amount, usage_limit, is_active, starts_at, expires_at) VALUES 
('WELCOME10', 'percentage', 10.00, 100.00, 100, TRUE, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY)),
('SAVE50', 'fixed', 50.00, 500.00, 50, TRUE, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY)),
('NEWUSER', 'percentage', 15.00, 200.00, NULL, TRUE, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY));

-- =====================================================
-- ENABLE FOREIGN KEY CHECKS
-- =====================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- FINAL VERIFICATION QUERIES
-- =====================================================

-- Show table creation summary
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    DATA_LENGTH,
    INDEX_LENGTH
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'store' 
ORDER BY TABLE_NAME;

-- Show admin user
SELECT id, username, email, is_admin, email_verified, created_at 
FROM users 
WHERE is_admin = TRUE;

-- Show product count by category
SELECT 
    c.name as category_name,
    COUNT(p.id) as product_count
FROM categories c
LEFT JOIN products p ON c.id = p.category_id
WHERE c.parent_id IS NOT NULL
GROUP BY c.id, c.name
ORDER BY c.name;

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================

SELECT 'Database setup completed successfully!' as message,
       'Admin Username: labonysur' as admin_username,
       'Admin Password: password' as admin_password,
       'Admin Email: labonysur6@gmail.com' as admin_email;
  USE store;     
       -- Insert admin user (if not exists) and update password and is_admin flag
INSERT INTO users (username, email, password, is_admin)
SELECT 'labonysur', 'labonysur6@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'labonysur');

UPDATE users
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    is_admin = TRUE
WHERE username = 'labonysur';

-- =====================================================
-- EXACT CATEGORY SETUP FOR WOMEN & MEN ONLY
-- =====================================================
-- This script creates the precise category structure you requested:
-- Women: Dress, Shoes, Accessories, Bags
-- Men: Dress, Shoes, Bags, Accessories

USE store;

-- First, let's clear existing categories to avoid conflicts
DELETE FROM categories WHERE name IN ('Women', 'Men') OR parent_id IN (
    SELECT id FROM categories WHERE name IN ('Women', 'Men')
);

-- Insert Women as main category
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES 
('Women', NULL, 'Women\'s fashion and accessories', TRUE, 1);

-- Insert Men as main category
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES 
('Men', NULL, 'Men\'s fashion and accessories', TRUE, 2);

-- Get the IDs of Women and Men categories
SET @women_id = (SELECT id FROM categories WHERE name = 'Women' AND parent_id IS NULL);
SET @men_id = (SELECT id FROM categories WHERE name = 'Men' AND parent_id IS NULL);

-- Insert Women subcategories
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES
('Dress', @women_id, 'Women\'s dresses and gowns', TRUE, 1),
('Shoes', @women_id, 'Women\'s footwear', TRUE, 2),
('Accessories', @women_id, 'Women\'s fashion accessories', TRUE, 3),
('Bags', @women_id, 'Women\'s handbags and purses', TRUE, 4);

-- Insert Men subcategories
INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES
('Dress', @men_id, 'Men\'s formal and casual wear', TRUE, 1),
('Shoes', @men_id, 'Men\'s footwear', TRUE, 2),
('Bags', @men_id, 'Men\'s bags and wallets', TRUE, 3),
('Accessories', @men_id, 'Men\'s fashion accessories', TRUE, 4);

-- Verify the setup
SELECT 
    c1.name as main_category,
    c2.name as subcategory
FROM categories c1
JOIN categories c2 ON c1.id = c2.parent_id
WHERE c1.name IN ('Women', 'Men')
ORDER BY c1.name, c2.sort_order;

-- Sample products for testing
-- Women Dresses
SET @women_dress_id = (SELECT id FROM categories WHERE name = 'Dress' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status, featured) VALUES
(@women_dress_id, 'Elegant Evening Gown', 'Stunning evening gown perfect for special occasions', 'Elegant evening gown for women', 199.99, 15, 'WOMEN-DRESS-001', 'https://images.unsplash.com/photo-1566479179817-c0b2b3b7b5b5?w=500', 'active', TRUE),
(@women_dress_id, 'Floral Summer Dress', 'Light and breezy summer dress with floral patterns', 'Floral summer dress for women', 79.99, 25, 'WOMEN-DRESS-002', 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=500', 'active', FALSE);

-- Women Shoes
SET @women_shoes_id = (SELECT id FROM categories WHERE name = 'Shoes' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@women_shoes_id, 'High Heel Sandals', 'Elegant high heel sandals for formal events', 'High heel sandals for women', 89.99, 20, 'WOMEN-SHOES-001', 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500', 'active');

-- Women Accessories
SET @women_accessories_id = (SELECT id FROM categories WHERE name = 'Accessories' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@women_accessories_id, 'Pearl Necklace', 'Classic pearl necklace for elegant styling', 'Pearl necklace for women', 49.99, 30, 'WOMEN-ACC-001', 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500', 'active');

-- Women Bags
SET @women_bags_id = (SELECT id FROM categories WHERE name = 'Bags' AND parent_id = @women_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@women_bags_id, 'Designer Handbag', 'Stylish designer handbag for daily use', 'Designer handbag for women', 129.99, 15, 'WOMEN-BAG-001', 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500', 'active');

-- Men Dress
SET @men_dress_id = (SELECT id FROM categories WHERE name = 'Dress' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status, featured) VALUES
(@men_dress_id, 'Classic Black Suit', 'Premium black suit for formal occasions', 'Classic black suit for men', 299.99, 10, 'MEN-DRESS-001', 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=500', 'active', TRUE),
(@men_dress_id, 'Casual Shirt', 'Comfortable casual shirt for everyday wear', 'Casual shirt for men', 59.99, 30, 'MEN-SHIRT-001', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500', 'active', FALSE);

-- Men Shoes
SET @men_shoes_id = (SELECT id FROM categories WHERE name = 'Shoes' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@men_shoes_id, 'Oxford Dress Shoes', 'Classic leather Oxford shoes for formal wear', 'Oxford dress shoes for men', 129.99, 20, 'MEN-SHOES-001', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500', 'active');

-- Men Bags
SET @men_bags_id = (SELECT id FROM categories WHERE name = 'Bags' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@men_bags_id, 'Leather Wallet', 'Premium leather wallet with multiple compartments', 'Leather wallet for men', 49.99, 25, 'MEN-BAG-001', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500', 'active');

-- Men Accessories
SET @men_accessories_id = (SELECT id FROM categories WHERE name = 'Accessories' AND parent_id = @men_id);
INSERT INTO products (category_id, name, description, short_description, price, stock, sku, image, status) VALUES
(@men_accessories_id, 'Silk Tie', 'Elegant silk tie for formal occasions', 'Silk tie for men', 29.99, 35, 'MEN-TIE-001', 'https://images.unsplash.com/photo-1521369909029-2afed882baee?w=500', 'active');

-- Final verification
SELECT 
    CONCAT(c1.name, ' > ', c2.name) as full_category_path
FROM categories c1
JOIN categories c2 ON c1.id = c2.parent_id
WHERE c1.name IN ('Women', 'Men')
ORDER BY c1.name, c2.sort_order;

-- Display all categories for confirmation
SELECT 
    c1.name as main_category,
    GROUP_CONCAT(c2.name ORDER BY c2.sort_order) as subcategories
FROM categories c1
JOIN categories c2 ON c1.id = c2.parent_id
WHERE c1.name IN ('Women', 'Men')
GROUP BY c1.name
ORDER BY c1.name;

SELECT 'Category setup completed successfully!' as status,
       'You can now add products to Women and Men categories with their specific subcategories!' as message;

SELECT username, email, email_otp, email_otp_expires, email_verified 
FROM users 
WHERE email = 'test@example.com'; -- Replace with the email of the user you want to check
SELECT username, email, email_otp, email_otp_expires, email_verified 
FROM users 
WHERE email = 'labonysur473@gmail.com';

USE store;

SELECT username, email, email_otp, email_otp_expires, email_verified FROM users WHERE email = 'test@example.com';
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at),
    INDEX idx_user_id (user_id)
);