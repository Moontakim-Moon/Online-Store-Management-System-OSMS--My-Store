-- Update Men's Product Images
UPDATE products p
JOIN categories c ON p.category_id = c.id
JOIN categories parent ON c.parent_id = parent.id
SET p.image = CASE p.name
    -- Men's Shirts
    WHEN 'Classic White Shirt' THEN 'https://images.unsplash.com/photo-1603252109303-2751441dd157?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Casual Denim Shirt' THEN 'https://images.unsplash.com/photo-1588359348347-9bc6cbbb689e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Men's Pants
    WHEN 'Black Formal Trousers' THEN 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Khaki Chinos' THEN 'https://images.unsplash.com/photo-1605518216938-7c31b7b14ad0?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Men's Suits
    WHEN 'Classic Black Suit' THEN 'https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Navy Blue Blazer' THEN 'https://images.unsplash.com/photo-1592878940526-0214b0f374f6?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Men's Shoes
    WHEN 'Brown Leather Oxfords' THEN 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Black Formal Shoes' THEN 'https://images.unsplash.com/photo-1605733160314-4fc7dac4bb16?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Men's Accessories
    WHEN 'Leather Belt' THEN 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Silk Tie' THEN 'https://images.unsplash.com/photo-1589756823695-278bc923f962?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Classic Watch' THEN 'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
END
WHERE parent.name = 'Men';

-- Update Women's Product Images
UPDATE products p
JOIN categories c ON p.category_id = c.id
JOIN categories parent ON c.parent_id = parent.id
SET p.image = CASE p.name
    -- Women's Dresses
    WHEN 'Floral Summer Dress' THEN 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Evening Gown' THEN 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Women's Tops
    WHEN 'Casual Blouse' THEN 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Designer T-Shirt' THEN 'https://images.unsplash.com/photo-1583744946564-b52ac1c389c8?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Women's Pants
    WHEN 'Slim Fit Jeans' THEN 'https://images.unsplash.com/photo-1584370848010-d7fe6bc767ec?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Formal Trousers' THEN 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Women's Skirts
    WHEN 'A-Line Skirt' THEN 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Pleated Midi Skirt' THEN 'https://images.unsplash.com/photo-1573879541250-58ae8b322b40?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    
    -- Women's Accessories
    WHEN 'Designer Handbag' THEN 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
    WHEN 'Pearl Necklace' THEN 'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&h=600'
END
WHERE parent.name = 'Women';
