-- ========================================
-- Test Review Data for AI Review Analyzer
-- The Seventh Com E-Commerce Platform
-- ========================================

-- NOTE: Adjust product_id and user_id based on your actual database
-- This script assumes product_id = 1 exists and user_id 1-15 exist

-- ========================================
-- SCENARIO 1: Laptop with Mixed Reviews
-- Product ID: 1 (Change if needed)
-- ========================================

-- Excellent Reviews (5 stars)
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(1, 1, 5, 'Excellent laptop! Amazing speed and performance. The display is crystal clear and colors are vibrant.', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 2, 5, 'Love this product! Fast, reliable, and great build quality. Highly recommend for professionals.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 3, 5, 'Best purchase ever! The performance is outstanding and it handles multitasking like a charm.', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 4, 5, 'Fantastic laptop! Beautiful design and the keyboard is very comfortable to type on.', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 5, 5, 'Perfect for work and gaming. Fast processor and excellent graphics. Worth every penny!', 1, DATE_SUB(NOW(), INTERVAL 12 DAY));

-- Good Reviews (4 stars)
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(1, 6, 4, 'Very good laptop overall. Performance is great but the price is a bit high.', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 7, 4, 'Good quality and fast speed. The display is sharp. Battery life could be better though.', 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 8, 4, 'Great laptop for the price. Fast and reliable. Only minor complaint is it gets warm during heavy use.', 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(1, 9, 4, 'Impressive performance and sleek design. Would give 5 stars if battery lasted longer.', 1, DATE_SUB(NOW(), INTERVAL 11 DAY));

-- Average Reviews (3 stars)
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(1, 10, 3, 'Decent product. Performance is okay but battery drains faster than expected.', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 11, 3, 'Average laptop. Nothing special. Battery life is disappointing for the price.', 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(1, 12, 3, 'Good display and speed but has heating issues during gaming. Battery could be better.', 1, DATE_SUB(NOW(), INTERVAL 13 DAY));

-- Below Average Reviews (2 stars)
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(1, 13, 2, 'Battery drains too quickly. Also noticed heating problem when running multiple applications.', 1, DATE_SUB(NOW(), INTERVAL 14 DAY)),
(1, 14, 2, 'Disappointed with battery life. Gets hot during use. Expected better quality for this price.', 1, DATE_SUB(NOW(), INTERVAL 15 DAY));

-- Poor Review (1 star)
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(1, 15, 1, 'Very disappointed. Poor battery performance and severe overheating issue. Would not recommend.', 1, DATE_SUB(NOW(), INTERVAL 16 DAY));


-- ========================================
-- SCENARIO 2: Highly Rated Product
-- Product ID: 2 (Change if needed)
-- ========================================

INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(2, 1, 5, 'Absolutely amazing! Best headphones I have ever owned. Sound quality is superb.', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 2, 5, 'Excellent sound quality and very comfortable. Perfect for long listening sessions.', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 3, 5, 'Outstanding audio quality! Bass is deep and clear. Highly recommend!', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 4, 5, 'Premium quality headphones. Comfortable, great sound, and beautiful design.', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 5, 4, 'Very good headphones. Sound is excellent but a bit pricey.', 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(2, 6, 4, 'Great audio quality and comfortable fit. Worth the investment.', 1, DATE_SUB(NOW(), INTERVAL 11 DAY));


-- ========================================
-- SCENARIO 3: Budget Product with Good Value
-- Product ID: 3 (Change if needed)
-- ========================================

INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(3, 1, 5, 'Amazing value for money! Great quality at this price point.', 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 2, 4, 'Good budget option. Performance is decent for the price.', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 3, 4, 'Excellent value! Not premium but works perfectly for basic needs.', 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 4, 5, 'Best budget product! Exceeded my expectations. Highly recommend!', 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(3, 5, 3, 'Okay for the price. Does the job but nothing fancy.', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(3, 6, 4, 'Great price and good quality. Perfect for students on a budget.', 1, DATE_SUB(NOW(), INTERVAL 12 DAY));


-- ========================================
-- SCENARIO 4: Product with Quality Issues
-- Product ID: 4 (Change if needed)
-- ========================================

INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at) VALUES
(4, 1, 2, 'Poor quality. Stopped working after a week. Very disappointed.', 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 2, 1, 'Terrible product. Defective unit. Would not recommend to anyone.', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 3, 3, 'Average quality. Had some issues but customer service helped resolve them.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 4, 2, 'Not worth the money. Build quality is cheap and fragile.', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(4, 5, 4, 'Decent product but had to return the first one due to defect. Replacement works fine.', 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(4, 6, 2, 'Disappointing. Poor build quality and stopped working after two weeks.', 1, DATE_SUB(NOW(), INTERVAL 11 DAY));


-- ========================================
-- VERIFICATION QUERY
-- Run this to check if reviews were added
-- ========================================

-- SELECT 
--     p.id,
--     p.name,
--     COUNT(r.id) as total_reviews,
--     ROUND(AVG(r.rating), 1) as avg_rating,
--     SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star,
--     SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star,
--     SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star,
--     SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star,
--     SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star
-- FROM products p
-- LEFT JOIN reviews r ON p.id = r.product_id AND r.approved = 1
-- WHERE p.id IN (1, 2, 3, 4)
-- GROUP BY p.id
-- ORDER BY p.id;


-- ========================================
-- NOTES:
-- ========================================
-- 1. Update product_id values (1, 2, 3, 4) to match your actual product IDs
-- 2. Update user_id values (1-15) to match your actual user IDs
-- 3. All reviews are set to approved = 1 so they show immediately
-- 4. Reviews have different dates to simulate realistic timeline
-- 5. Comments include keywords that AI will extract:
--    - Positive: excellent, amazing, great, fast, quality, comfortable
--    - Negative: battery, heating, poor, disappointing, defective
--    - Features: display, performance, speed, sound, design
-- ========================================
