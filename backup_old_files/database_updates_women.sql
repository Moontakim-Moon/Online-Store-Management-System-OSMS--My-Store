-- Get Women category ID first
SET @women_id = (SELECT id FROM categories WHERE name = 'Women');

-- Add Women's subcategories
INSERT INTO categories (name, parent_id) VALUES 
('Dresses', @women_id),
('Tops', @women_id),
('Pants', @women_id),
('Skirts', @women_id),
('Accessories', @women_id);

-- Get subcategory IDs
SET @dresses_id = (SELECT id FROM categories WHERE name = 'Dresses' AND parent_id = @women_id);
SET @tops_id = (SELECT id FROM categories WHERE name = 'Tops' AND parent_id = @women_id);
SET @pants_id = (SELECT id FROM categories WHERE name = 'Pants' AND parent_id = @women_id);
SET @skirts_id = (SELECT id FROM categories WHERE name = 'Skirts' AND parent_id = @women_id);
SET @accessories_id = (SELECT id FROM categories WHERE name = 'Accessories' AND parent_id = @women_id);

-- Insert sample women's products
INSERT INTO products (name, description, category_id, image, price, stock, created_at) VALUES
-- Dresses
('Floral Summer Dress', 'Beautiful floral print summer dress perfect for any occasion', 
 @dresses_id, '/assets/images/products/floral_dress.jpg', 79.99, 15, NOW()),
('Evening Gown', 'Elegant black evening gown with subtle embellishments', 
 @dresses_id, '/assets/images/products/evening_gown.jpg', 199.99, 10, NOW()),

-- Tops
('Casual Blouse', 'Comfortable and stylish casual blouse', 
 @tops_id, '/assets/images/products/casual_blouse.jpg', 29.99, 25, NOW()),
('Designer T-Shirt', 'Premium quality designer t-shirt', 
 @tops_id, '/assets/images/products/designer_tshirt.jpg', 39.99, 20, NOW()),

-- Pants
('Slim Fit Jeans', 'Classic slim fit jeans with perfect stretch', 
 @pants_id, '/assets/images/products/slim_jeans.jpg', 59.99, 30, NOW()),
('Formal Trousers', 'Professional formal trousers for office wear', 
 @pants_id, '/assets/images/products/formal_trousers.jpg', 69.99, 20, NOW()),

-- Skirts
('A-Line Skirt', 'Classic A-line skirt perfect for any season', 
 @skirts_id, '/assets/images/products/aline_skirt.jpg', 49.99, 15, NOW()),
('Pleated Midi Skirt', 'Elegant pleated midi skirt', 
 @skirts_id, '/assets/images/products/pleated_skirt.jpg', 54.99, 12, NOW()),

-- Accessories
('Designer Handbag', 'Premium leather designer handbag', 
 @accessories_id, '/assets/images/products/designer_bag.jpg', 129.99, 8, NOW()),
('Pearl Necklace', 'Elegant pearl necklace with silver chain', 
 @accessories_id, '/assets/images/products/pearl_necklace.jpg', 79.99, 10, NOW());
