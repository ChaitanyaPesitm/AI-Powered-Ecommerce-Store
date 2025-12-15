-- Add average_rating column to products table if it doesn't exist
-- Run this in phpMyAdmin if you get errors about average_rating column

ALTER TABLE products 
ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER price;

-- Update existing products with their average ratings
UPDATE products p
SET average_rating = (
    SELECT COALESCE(ROUND(AVG(r.rating), 2), 0)
    FROM reviews r
    WHERE r.product_id = p.id AND r.approved = 1
);
