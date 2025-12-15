-- ========================================
-- STEP 1: Check existing users
-- Run this first to see what user IDs exist
-- ========================================

SELECT id, name, email FROM users ORDER BY id LIMIT 20;

-- ========================================
-- STEP 2: If you have NO users, create them first
-- (Only run if you have no users)
-- ========================================

-- Uncomment and run if needed:
-- INSERT INTO users (name, email, password, role, created_at) VALUES
-- ('John Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'customer', NOW()),
-- ('Jane Smith', 'jane@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'customer', NOW()),
-- ('Mike Johnson', 'mike@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'customer', NOW()),
-- ('Sarah Williams', 'sarah@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'customer', NOW()),
-- ('Tom Brown', 'tom@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'customer', NOW()),
-- ('Emily Davis', 'emily@example.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'customer', NOW());

-- ========================================
-- STEP 3: Insert reviews using EXISTING user IDs
-- Replace the numbers in parentheses with actual user IDs from Step 1
-- ========================================

-- OPTION A: If your first user ID is 1, use this:
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(1, 1, 5, 'Excellent laptop! Amazing speed and performance. Display is crystal clear.', 1, NOW()),
(1, 1, 5, 'Love this product! Fast, reliable, and great quality.', 1, NOW()),
(1, 1, 4, 'Very good laptop. Performance is great but price is high.', 1, NOW()),
(1, 1, 4, 'Good quality and fast speed. Battery could be better.', 1, NOW()),
(1, 1, 3, 'Decent product. Battery drains faster than expected.', 1, NOW()),
(1, 1, 2, 'Battery drains too quickly. Heating problem.', 1, NOW());

-- OPTION B: If you have multiple users, replace user_id with actual IDs
-- Example: If your user IDs are 5, 7, 9, 11, 13, 15
-- INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
-- (1, 5, 5, 'Excellent laptop! Amazing speed and performance. Display is crystal clear.', 1, NOW()),
-- (1, 7, 5, 'Love this product! Fast, reliable, and great quality.', 1, NOW()),
-- (1, 9, 4, 'Very good laptop. Performance is great but price is high.', 1, NOW()),
-- (1, 11, 4, 'Good quality and fast speed. Battery could be better.', 1, NOW()),
-- (1, 13, 3, 'Decent product. Battery drains faster than expected.', 1, NOW()),
-- (1, 15, 2, 'Battery drains too quickly. Heating problem.', 1, NOW());

-- ========================================
-- STEP 4: Verify reviews were added
-- ========================================

SELECT 
    r.id,
    r.product_id,
    r.user_id,
    u.name as user_name,
    r.rating,
    r.comment,
    r.created_at
FROM reviews r
LEFT JOIN users u ON r.user_id = u.id
WHERE r.product_id = 1
ORDER BY r.created_at DESC;
