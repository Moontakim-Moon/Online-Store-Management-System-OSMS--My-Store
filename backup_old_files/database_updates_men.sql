-- Get Men category ID first
SET @men_id = (SELECT id FROM categories WHERE name = 'Men');

-- Add Men's subcategories
INSERT INTO categories (name, parent_id) VALUES 
('Shirts', @men_id),
('Pants', @men_id),
('Suits', @men_id),
('Shoes', @men_id),
('Accessories', @men_id);

-- Get subcategory IDs
SET @shirts_id = (SELECT id FROM categories WHERE name = 'Shirts' AND parent_id = @men_id);
SET @pants_id = (SELECT id FROM categories WHERE name = 'Pants' AND parent_id = @men_id);
SET @suits_id = (SELECT id FROM categories WHERE name = 'Suits' AND parent_id = @men_id);
SET @shoes_id = (SELECT id FROM categories WHERE name = 'Shoes' AND parent_id = @men_id);
SET @accessories_id = (SELECT id FROM categories WHERE name = 'Accessories' AND parent_id = @men_id);

-- Insert sample men's products
INSERT INTO products (name, description, category_id, image, price, stock, created_at) VALUES
-- Shirts
('Classic White Shirt', 'Premium cotton formal white shirt', 
 @shirts_id, '/assets/images/products/white_shirt.jpg', 49.99, 20, NOW()),
('Casual Denim Shirt', 'Stylish denim shirt for casual wear', 
 @shirts_id, '/assets/images/products/denim_shirt.jpg', 45.99, 15, NOW()),

-- Pants
('Black Formal Trousers', 'Classic black formal trousers for office wear', 
 @pants_id, '/assets/images/products/black_trousers.jpg', 69.99, 25, NOW()),
('Khaki Chinos', 'Comfortable and stylish khaki chinos', 
 @pants_id, '/assets/images/products/khaki_chinos.jpg', 54.99, 20, NOW()),

-- Suits
('Classic Black Suit', 'Premium black suit for formal occasions', 
 @suits_id, '/assets/images/products/black_suit.jpg', 299.99, 10, NOW()),
('Navy Blue Blazer', 'Versatile navy blue blazer', 
 @suits_id, '/assets/images/products/navy_blazer.jpg', 199.99, 15, NOW()),

-- Shoes
('Brown Leather Oxfords', 'Classic brown leather oxford shoes', 
 @shoes_id, '/assets/images/products/oxford_shoes.jpg', 129.99, 12, NOW()),
('Black Formal Shoes', 'Premium black formal shoes', 
 @shoes_id, '/assets/images/products/formal_shoes.jpg', 149.99, 10, NOW()),

-- Accessories
('Leather Belt', 'Premium leather belt with classic buckle', 
 @accessories_id, '/assets/images/products/leather_belt.jpg', 39.99, 30, NOW()),
('Silk Tie', 'Classic silk tie for formal wear', 
 @accessories_id, '/assets/images/products/silk_tie.jpg', 29.99, 25, NOW()),
('Classic Watch', 'Elegant stainless steel watch', 
 @accessories_id, '/assets/images/products/classic_watch.jpg', 199.99, 8, NOW());
