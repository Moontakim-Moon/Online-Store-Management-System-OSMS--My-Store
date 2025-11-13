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
