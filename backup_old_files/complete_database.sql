-- Database initialization
CREATE DATABASE IF NOT EXISTS ecommerce
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ecommerce;

-- Character set configuration
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;
------------------------------
EP1 (Engineering Fundamentals):
- Normalized database design (3NF)
- Complex data relationships and constraints
- Transaction management
- Stored procedures and triggers
- Optimized query design

EP2 (Problem Analysis):
- Multi-stakeholder system
- Complex business rules
- Data integrity requirements
- Performance considerations
- Security implementation

EP4 (Research Methods):
- Data analytics views
- Sales pattern analysis
- Customer behavior tracking
- Performance metrics
- Trend analysis

SYSTEM COMPONENTS
----------------
1. User Management:
   - Customer accounts
   - Admin privileges
   - Email verification
   - Activity tracking

2. Product System:
   - Hierarchical categories
   - Stock management
   - Rating system
   - Price history

3. Order Processing:
   - Cart management
   - OTP verification
   - Status tracking
   - Email notifications

4. Analytics:
   - Sales reporting
   - Product performance
   - Customer engagement
   - Stock analytics

TECHNICAL SPECIFICATIONS
----------------------
- Database Engine: MySQL 8.0+
- Character Set: UTF-8
- Collation: utf8mb4_unicode_ci
- Storage Engine: InnoDB

Problem Definition:
-----------------
An e-commerce system with complex order tracking, user verification, and product review features.

Stakeholders:
1. Customers (Buyers)
2. Administrators
3. System Managers
4. Product Managers

System Complexity:
1. Multi-level Authentication (Email OTP)
2. Hierarchical Category Management
3. Order Processing with Status Tracking
4. Product Rating and Review System
5. Sales Analytics and Reporting
6. Admin Activity Monitoring

Engineering Principles Applied:
1. Data Normalization (3NF)
2. Referential Integrity
3. Transaction Management
4. Business Logic Implementation
5. Data Security and Validation

-- Core tables creation
-- Tables are organized in order of dependencies
INSERT INTO product_tags (product_id, tag_id)
SELECT p.id, t.id
FROM products p
CROSS JOIN tags t
WHERE p.id <= 3 AND t.name IN ('New Arrival', 'Featured');

-- Create view for user roles and status
CREATE OR REPLACE VIEW user_details AS
SELECT username, email, 
       CASE WHEN is_admin = 1 THEN 'Admin' ELSE 'Customer' END as role, 
       CASE WHEN email_verified = 1 THEN 'Verified' ELSE 'Pending' END as status
FROM users;

-- Create view for product statistics
CREATE OR REPLACE VIEW product_details AS
SELECT p.name, p.price, c.name as category, 
       COALESCE(ps.total_sales, 0) as total_sales,
       COALESCE(ps.rating, 0) as avg_rating
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN product_stats ps ON p.id = ps.product_id
WHERE p.status = 'active';
LIMIT 10;

-- Show new arrivals with their categories
SELECT p.name, p.price, c.name as category, p.created_at
FROM products p
JOIN categories c ON p.category_id = c.id
WHERE p.is_new_arrival = TRUE AND p.status = 'active'
ORDER BY p.created_at DESC;

3. CATEGORY MANAGEMENT
--------------------
-- Show complete category hierarchy
SELECT 
    CASE 
        WHEN c2.name IS NULL THEN c1.name
        ELSE CONCAT(c1.name, ' > ', c2.name)
    END as category_path,
    COUNT(p.id) as product_count
FROM categories c1
LEFT JOIN categories c2 ON c2.parent_id = c1.id
LEFT JOIN products p ON p.category_id = COALESCE(c2.id, c1.id)
GROUP BY c1.id, c1.name, c2.id, c2.name;

4. ORDER MANAGEMENT
-----------------
-- Show recent orders with customer details
SELECT 
    o.id as order_id,
    u.username,
    o.total,
    o.status,
    o.payment_method,
    o.created_at,
    COALESCE(ot.is_verified, 0) as otp_verified
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN order_otp ot ON o.id = ot.order_id
ORDER BY o.created_at DESC
LIMIT 10;

-- Calculate daily/monthly sales
SELECT 
    DATE_FORMAT(created_at, '%Y-%m-%d') as date,
    COUNT(*) as total_orders,
    SUM(total) as total_revenue,
    AVG(total) as average_order_value
FROM orders
WHERE status = 'confirmed'
GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
ORDER BY date DESC;

5. CART ANALYSIS
--------------
-- Show active carts with products
SELECT 
    u.username,
    p.name as product_name,
    ci.quantity,
    p.price,
    (ci.quantity * p.price) as subtotal
FROM cart_items ci
JOIN users u ON ci.user_id = u.id
JOIN products p ON ci.product_id = p.id
ORDER BY u.username;

6. PRODUCT REVIEWS
----------------
-- Show product ratings and reviews
SELECT 
    p.name as product_name,
    u.username as reviewer,
    pr.rating,
    pr.review,
    pr.created_at
FROM product_reviews pr
JOIN products p ON pr.product_id = p.id
JOIN users u ON pr.user_id = u.id
ORDER BY pr.created_at DESC;

7. ADMIN ACTIVITY MONITORING
-------------------------
-- Track admin actions
SELECT 
    u.username as admin_name,
    al.action_type,
    al.entity_type,
    al.created_at,
    al.ip_address
FROM admin_activity_log al
JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC;

8. STOCK MANAGEMENT
-----------------
-- Show low stock products
SELECT 
    p.name,
    p.stock,
    c.name as category,
    p.price
FROM products p
JOIN categories c ON p.category_id = c.id
WHERE p.stock < 10 AND p.status = 'active'
ORDER BY p.stock ASC;

9. SALES ANALYSIS
---------------
-- Product performance analysis
SELECT 
    p.name,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity_sold,
    SUM(oi.quantity * oi.price) as total_revenue
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE o.status = 'confirmed'
GROUP BY p.id, p.name
ORDER BY total_revenue DESC;

10. OTP VERIFICATION TRACKING
--------------------------
-- Monitor OTP status and attempts
SELECT 
    ot.order_id,
    u.email,
    ot.verification_attempts,
    ot.is_verified,
    ot.created_at,
    ot.expires_at
FROM order_otp ot
JOIN orders o ON ot.order_id = o.id
JOIN users u ON o.user_id = u.id
WHERE ot.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY ot.created_at DESC;

-- Note: Replace NOW() with CURRENT_TIMESTAMP in MySQL versions that don't support NOW()
*/Queries for Demonstration:
----------------------------------------
1. Show all tables in database:
   SHOW TABLES;

2. View table structure:
   DESCRIBE table_name;
   Example: DESCRIBE products;

3. Check database version:
   SELECT VERSION();
*/

-- Create and use the database
DROP DATABASE IF EXISTS store;
CREATE DATABASE store;
USE store;

/*
USER MANAGEMENT QUERIES
----------------------------------------
1. Show all users:
   SELECT id, username, email, is_admin, email_verified FROM users;

2. Find admin users:
   SELECT * FROM users WHERE is_admin = TRUE;

3. Check unverified users:
   SELECT * FROM users WHERE email_verified = FALSE;

4. Search users by email or username:
   SELECT * FROM users WHERE email LIKE '%@gmail.com' OR username LIKE 'search_term%';
*/

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_otp VARCHAR(10) DEFAULT NULL,
    email_otp_expires DATETIME DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/*
CATEGORY MANAGEMENT QUERIES
----------------------------------------
1. Show all main categories (no parent):
   SELECT * FROM categories WHERE parent_id IS NULL;

2. Show category hierarchy:
   SELECT c1.name as main_category, c2.name as subcategory 
   FROM categories c1 
   LEFT JOIN categories c2 ON c2.parent_id = c1.id 
   WHERE c1.parent_id IS NULL;

3. Count products in each category:
   SELECT c.name, COUNT(p.id) as product_count 
   FROM categories c 
   LEFT JOIN products p ON c.id = p.category_id 
   GROUP BY c.id, c.name;
*/

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL,
    UNIQUE KEY unique_name_parent (name, parent_id),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
);

/*
PRODUCT MANAGEMENT QUERIES
----------------------------------------
1. Show all active products with categories:
   SELECT p.*, c.name as category_name 
   FROM products p 
   JOIN categories c ON p.category_id = c.id 
   WHERE p.status = 'active';

2. Find best sellers:
   SELECT p.*, ps.total_sales, ps.rating 
   FROM products p 
   JOIN product_stats ps ON p.id = ps.product_id 
   ORDER BY ps.total_sales DESC LIMIT 10;

3. Show new arrivals:
   SELECT * FROM products 
   WHERE is_new_arrival = TRUE AND status = 'active' 
   ORDER BY created_at DESC LIMIT 10;

4. Find products with low stock:
   SELECT * FROM products WHERE stock < 10 AND status = 'active';

5. Show products with discounts:
   SELECT *, ((price - discount_price)/price * 100) as discount_percentage 
   FROM products 
   WHERE discount_price IS NOT NULL AND status = 'active';
*/

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    is_new_arrival BOOLEAN DEFAULT TRUE,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(50) UNIQUE,
    weight DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

/*
ORDER MANAGEMENT QUERIES
----------------------------------------
1. Show recent orders with customer details:
   SELECT o.*, u.username, u.email 
   FROM orders o 
   JOIN users u ON o.user_id = u.id 
   ORDER BY o.created_at DESC LIMIT 10;

2. View order details with products:
   SELECT o.id as order_id, p.name, oi.quantity, oi.price, (oi.quantity * oi.price) as subtotal 
   FROM orders o 
   JOIN order_items oi ON o.id = oi.order_id 
   JOIN products p ON oi.product_id = p.id 
   WHERE o.id = [order_id];

3. Show pending orders:
   SELECT * FROM orders WHERE status = 'pending';

4. Calculate daily sales:
   SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue 
   FROM orders 
   GROUP BY DATE(created_at) 
   ORDER BY date DESC;
*/

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart items table
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product images table
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product statistics table
CREATE TABLE IF NOT EXISTS product_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    total_sales INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    last_sale_date TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Product reviews table
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Product tags table
CREATE TABLE IF NOT EXISTS product_tags (
    product_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

/*
OTP AND VERIFICATION QUERIES
----------------------------------------
1. Check pending OTP verifications:
   SELECT o.*, u.email 
   FROM order_otp o 
   JOIN orders ord ON o.order_id = ord.id 
   JOIN users u ON ord.user_id = u.id 
   WHERE o.is_verified = FALSE AND o.expires_at > NOW();

2. View OTP attempts:
   SELECT * FROM order_otp 
   WHERE verification_attempts >= 3 
   ORDER BY last_attempt_at DESC;

3. Check OTP settings:
   SELECT * FROM otp_settings;

4. Find expired OTPs:
   SELECT * FROM order_otp 
   WHERE expires_at < NOW() AND is_verified = FALSE;
*/

-- OTP tracking table for orders
CREATE TABLE IF NOT EXISTS order_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_attempts INT DEFAULT 0,
    last_attempt_at TIMESTAMP NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

/*
STORED PROCEDURES AND TRIGGERS
============================
These implement complex business logic and automation requirements (EP1)
*/

DELIMITER //

-- Stored Procedure: Process Order
CREATE PROCEDURE ProcessOrder(
    IN p_user_id INT,
    IN p_total DECIMAL(10,2),
    IN p_payment_method VARCHAR(50)
)
BEGIN
    DECLARE new_order_id INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Order processing failed';
    END;

    START TRANSACTION;
    
    -- Create order
    INSERT INTO orders (user_id, total, payment_method, status)
    VALUES (p_user_id, p_total, p_payment_method, 'pending');
    
    SET new_order_id = LAST_INSERT_ID();
    
    -- Move items from cart to order_items
    INSERT INTO order_items (order_id, product_id, quantity, price)
    SELECT new_order_id, product_id, quantity, 
           (SELECT price FROM products WHERE id = cart_items.product_id)
    FROM cart_items
    WHERE user_id = p_user_id;
    
    -- Update product stock
    UPDATE products p
    JOIN order_items oi ON p.id = oi.product_id
    SET p.stock = p.stock - oi.quantity
    WHERE oi.order_id = new_order_id;
    
    -- Clear cart
    DELETE FROM cart_items WHERE user_id = p_user_id;
    
    COMMIT;
END //

-- Stored Procedure: Update Product Rating
CREATE PROCEDURE UpdateProductRating(IN p_product_id INT)
BEGIN
    UPDATE product_stats ps
    SET rating = (
        SELECT AVG(rating)
        FROM product_reviews
        WHERE product_id = p_product_id
    )
    WHERE ps.product_id = p_product_id;
END //

-- Trigger: After Review Insert
CREATE TRIGGER after_review_insert
AFTER INSERT ON product_reviews
FOR EACH ROW
BEGIN
    CALL UpdateProductRating(NEW.product_id);
END //

-- Trigger: Update Product Stats
CREATE TRIGGER after_order_confirm
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' AND OLD.status = 'pending' THEN
        UPDATE product_stats ps
        JOIN order_items oi ON ps.product_id = oi.product_id
        SET ps.total_sales = ps.total_sales + oi.quantity
        WHERE oi.order_id = NEW.id;
    END IF;
END //

DELIMITER ;

-- OTP Settings table
CREATE TABLE IF NOT EXISTS otp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL, -- 'registration', 'order', 'password_reset'
    expiry_minutes INT DEFAULT 15,
    max_attempts INT DEFAULT 3,
    lockout_minutes INT DEFAULT 30,
    otp_length INT DEFAULT 6,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

/*
ADMIN ACTIVITY AND ANALYTICS QUERIES
----------------------------------------
1. View recent admin actions:
   SELECT u.username, al.action_type, al.entity_type, al.created_at 
   FROM admin_activity_log al 
   JOIN users u ON al.user_id = u.id 
   ORDER BY al.created_at DESC LIMIT 20;

2. Check product modifications:
   SELECT * FROM admin_activity_log 
   WHERE entity_type = 'product' 
   ORDER BY created_at DESC;

3. View admin performance:
   SELECT u.username, COUNT(*) as total_actions 
   FROM admin_activity_log al 
   JOIN users u ON al.user_id = u.id 
   GROUP BY u.id, u.username;
*/

-- Admin activity log
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL, -- 'product_add', 'product_edit', 'product_delete', etc.
    entity_type VARCHAR(50) NOT NULL, -- 'product', 'category', 'order', etc.
    entity_id INT NOT NULL,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Email templates table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(50) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert main categories
INSERT INTO categories (name, parent_id) VALUES 
('Men', NULL),
('Women', NULL);

-- Insert Men subcategories
INSERT INTO categories (name, parent_id) VALUES 
('Dress', (SELECT id FROM categories WHERE name = 'Men')),
('Shoes', (SELECT id FROM categories WHERE name = 'Men')),
('Accessories', (SELECT id FROM categories WHERE name = 'Men'));

-- Insert Women subcategories
INSERT INTO categories (name, parent_id) VALUES 
('Dress', (SELECT id FROM categories WHERE name = 'Women')),
('Bags', (SELECT id FROM categories WHERE name = 'Women')),
('Claw Clip', (SELECT id FROM categories WHERE name = 'Women'));

-- Insert sample products for Men's Dress category
INSERT INTO products (name, category_id, description, image, price, stock) VALUES
('Classic Navy Suit', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Dress' AND p.name = 'Men'), 'Elegant navy blue suit perfect for formal occasions', '/assets/images/products/mens-suit-1.jpg', 299.99, 10),
('White Business Shirt', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Dress' AND p.name = 'Men'), 'Crisp white cotton business shirt', '/assets/images/products/mens-shirt-1.jpg', 59.99, 20),
('Black Formal Trousers', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Dress' AND p.name = 'Men'), 'Classic black formal trousers', '/assets/images/products/mens-trousers-1.jpg', 89.99, 15);

-- Insert sample products for Men's Shoes category
INSERT INTO products (name, category_id, description, image, price, stock) VALUES
('Brown Leather Oxford', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Shoes' AND p.name = 'Men'), 'Classic brown leather oxford shoes', '/assets/images/products/mens-shoes-1.jpg', 129.99, 8),
('Black Derby Shoes', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Shoes' AND p.name = 'Men'), 'Elegant black derby shoes', '/assets/images/products/mens-shoes-2.jpg', 119.99, 10);

-- Insert sample products for Women's Dress category
INSERT INTO products (name, category_id, description, image, price, stock) VALUES
('Floral Summer Dress', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Dress' AND p.name = 'Women'), 'Beautiful floral print summer dress', '/assets/images/products/womens-dress-1.jpg', 79.99, 12),
('Evening Gown', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Dress' AND p.name = 'Women'), 'Elegant black evening gown', '/assets/images/products/womens-dress-2.jpg', 199.99, 5);

-- Insert sample products for Women's Bags category
INSERT INTO products (name, category_id, description, image, price, stock) VALUES
('Leather Tote Bag', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Bags' AND p.name = 'Women'), 'Spacious leather tote bag', '/assets/images/products/womens-bag-1.jpg', 149.99, 10),
('Evening Clutch', (SELECT c.id FROM categories c JOIN categories p ON c.parent_id = p.id WHERE c.name = 'Bags' AND p.name = 'Women'), 'Elegant evening clutch bag', '/assets/images/products/womens-bag-2.jpg', 89.99, 8);

-- Create indexes for performance optimization
CREATE INDEX idx_product_status ON products(status);
CREATE INDEX idx_product_category ON products(category_id);
CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_order_date ON orders(created_at);
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_cart_user ON cart_items(user_id);
CREATE INDEX idx_review_product ON product_reviews(product_id);
CREATE INDEX idx_order_user ON orders(user_id);

-- Insert admin user (if not exists) and update password and is_admin flag
INSERT INTO users (username, email, password, is_admin, email_verified)
SELECT 'labonysur', 'labonysur6@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'labonysur');

UPDATE users
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    is_admin = TRUE
WHERE username = 'labonysur';

-- Insert email templates
INSERT INTO email_templates (template_name, subject, body) VALUES
('registration_otp', 'Verify Your Email - Registration OTP', 'Hello {username},<br><br>Your OTP for email verification is: <strong>{otp}</strong><br>This OTP will expire in 15 minutes.<br><br>Thank you!'),
('order_confirmation', 'Order Confirmation OTP - Order #{order_id}', 'Hello,<br><br>Your OTP to confirm order #{order_id} (Total: ${total}) is: <strong>{otp}</strong><br>This OTP will expire in 15 minutes.<br><br>Thank you for your order!'),
('order_success', 'Order Confirmed - Order #{order_id}', 'Hello,<br><br>Your order #{order_id} has been successfully confirmed!<br><br>Order Details:<br>- Total: ${total}<br>- Payment Method: {payment_method}<br><br>Thank you for shopping with us!');

-- Add sample tags
INSERT INTO tags (name) VALUES 
('New Arrival'),
('Sale'),
('Featured'),
('Best Seller');

-- Add some product tags
INSERT INTO product_tags (product_id, tag_id)
SELECT p.id, t.id
FROM products p
CROSS JOIN tags t
WHERE p.id <= 3 AND t.name IN ('New Arrival', 'Featured');
