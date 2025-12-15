# 🚀 Setup Test Data for AI Review Analyzer

## Step-by-Step Guide

---

## 📋 Prerequisites

Before importing test data, make sure:
- ✅ XAMPP is running (Apache + MySQL)
- ✅ You have products in your database
- ✅ You have users in your database
- ✅ Reviews table exists

---

## 🔧 Step 1: Check Your Database

### Open phpMyAdmin:
```
http://localhost/phpmyadmin
```

### Check Products:
```sql
SELECT id, name FROM products LIMIT 5;
```
**Note the product IDs** (e.g., 1, 2, 3, 4, 5)

### Check Users:
```sql
SELECT id, name FROM users LIMIT 15;
```
**Make sure you have at least 15 users**

---

## 📝 Step 2: Customize the SQL File

1. **Open:** `test_reviews_data.sql`

2. **Update Product IDs:**
   - Find all `product_id` values in the file
   - Replace with your actual product IDs
   - Example: If your laptop product is ID 5, change all `(1,` to `(5,`

3. **Update User IDs (if needed):**
   - If your user IDs are different, update them
   - Make sure the user IDs exist in your database

---

## 💾 Step 3: Import Test Data

### Method 1: Using phpMyAdmin (Recommended)

1. **Go to phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Select your database** (e.g., `ecommerce`)

3. **Click "SQL" tab** at the top

4. **Copy the contents** of `test_reviews_data.sql`

5. **Paste into the SQL box**

6. **Click "Go"** to execute

7. **Check for success message**

### Method 2: Using Command Line

```bash
# Navigate to your project folder
cd c:\xampp\htdocs\ecommerce

# Import the SQL file
mysql -u root -p ecommerce < test_reviews_data.sql
```

---

## ✅ Step 4: Verify Data Import

Run this query in phpMyAdmin:

```sql
SELECT 
    p.id,
    p.name,
    COUNT(r.id) as total_reviews,
    ROUND(AVG(r.rating), 1) as avg_rating,
    SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star,
    SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star,
    SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star,
    SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star,
    SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star
FROM products p
LEFT JOIN reviews r ON p.id = r.product_id AND r.approved = 1
GROUP BY p.id
HAVING total_reviews > 0
ORDER BY total_reviews DESC;
```

**Expected Result:**
```
Product 1: 16 reviews, Avg 4.1 (Mixed - laptop with battery issues)
Product 2: 6 reviews, Avg 4.8 (Excellent - headphones)
Product 3: 6 reviews, Avg 4.2 (Good value - budget product)
Product 4: 6 reviews, Avg 2.3 (Poor - quality issues)
```

---

## 🧪 Step 5: Test the AI Analyzer

### Test 1: Standalone Analyzer
```
http://localhost/ecommerce/public/review-analyzer.php
```

1. Select "Product 1" (or whatever your first product is)
2. Click "Analyze Reviews with AI"
3. You should see:
   - ⭐ 4.1/5 • Very Good
   - 💬 Summary about performance, display, but battery issues
   - 📊 Rating distribution
   - 👍 Positive: performance, speed, display
   - 👎 Negative: battery, heating

### Test 2: Product Page Widget
```
http://localhost/ecommerce/public/product.php?id=1
```

1. Scroll to "Customer Reviews" section
2. You should see the AI widget with summary
3. Click "View Full AI Analysis" button

### Test 3: Different Products

**Excellent Product (Product 2):**
```
http://localhost/ecommerce/public/review-analyzer.php?id=2
```
Expected: ⭐ 4.8/5 • Excellent

**Budget Product (Product 3):**
```
http://localhost/ecommerce/public/review-analyzer.php?id=3
```
Expected: ⭐ 4.2/5 • Very Good

**Poor Product (Product 4):**
```
http://localhost/ecommerce/public/review-analyzer.php?id=4
```
Expected: ⭐ 2.3/5 • Below Average

---

## 🎯 What Each Test Scenario Shows

### Scenario 1: Laptop (Product 1)
**16 Reviews - Mixed Feedback**

**AI Should Extract:**
- ✅ Positive: excellent, performance, speed, display, quality
- ❌ Negative: battery, heating, expensive
- 📊 Rating: 4.1/5 (Very Good)
- 💬 Summary: "Customers love the performance, speed, and display but some mentioned issues with battery and heating."

### Scenario 2: Headphones (Product 2)
**6 Reviews - Highly Rated**

**AI Should Extract:**
- ✅ Positive: excellent, amazing, sound, quality, comfortable
- ❌ Negative: expensive (minor)
- 📊 Rating: 4.8/5 (Excellent)
- 💬 Summary: "Customers love the sound, quality, and comfortable fit with very few complaints."

### Scenario 3: Budget Product (Product 3)
**6 Reviews - Good Value**

**AI Should Extract:**
- ✅ Positive: value, budget, price, quality, recommend
- ❌ Negative: basic, nothing fancy
- 📊 Rating: 4.2/5 (Very Good)
- 💬 Summary: "Customers appreciate the great value and quality for the price."

### Scenario 4: Poor Quality (Product 4)
**6 Reviews - Quality Issues**

**AI Should Extract:**
- ✅ Positive: customer service (minor)
- ❌ Negative: poor, defective, cheap, fragile, disappointing
- 📊 Rating: 2.3/5 (Below Average)
- 💬 Summary: "Several customers reported issues with poor quality and defective units."

---

## 🐛 Troubleshooting

### Error: "Duplicate entry"
**Solution:** Reviews already exist. Either:
1. Delete existing reviews first:
   ```sql
   DELETE FROM reviews WHERE product_id IN (1,2,3,4);
   ```
2. Or change product_id values in the SQL file

### Error: "Cannot add foreign key constraint"
**Solution:** User IDs don't exist. Either:
1. Create users first
2. Or update user_id values in SQL file to existing users

### No Reviews Showing
**Check:**
```sql
SELECT * FROM reviews WHERE approved = 1 LIMIT 10;
```
If empty, reviews weren't imported.

### Widget Not Showing
**Check:**
1. Product has reviews (`SELECT COUNT(*) FROM reviews WHERE product_id = 1`)
2. Reviews are approved (`approved = 1`)
3. Clear browser cache
4. Check PHP error logs

---

## 🎨 Expected Visual Output

### On Product Page:
```
┌─────────────────────────────────────────┐
│ 🤖 AI Analysis                          │
│ Customer Review Summary                 │
│                                         │
│        4.1                              │
│      ★★★★☆                              │
│     16 reviews                          │
│                                         │
│ 5 ★ ████████░░░░░░░░░░  5              │
│ 4 ★ ████░░░░░░░░░░░░░░  4              │
│ 3 ★ ███░░░░░░░░░░░░░░░  3              │
│ 2 ★ ██░░░░░░░░░░░░░░░░  2              │
│ 1 ★ █░░░░░░░░░░░░░░░░░  1              │
│                                         │
│ ⭐ 4.1/5 • Very Good                    │
│                                         │
│ 💬 Customers love the performance,      │
│ speed, and display but some mentioned   │
│ issues with battery and heating.        │
│                                         │
│ [View Full AI Analysis →]              │
└─────────────────────────────────────────┘
```

---

## 📊 Success Metrics

After importing, you should have:
- ✅ 34 total reviews across 4 products
- ✅ Mix of ratings (1-5 stars)
- ✅ Realistic comments with keywords
- ✅ Different sentiment levels
- ✅ AI can extract meaningful insights

---

## 🎉 Next Steps

Once data is imported:

1. ✅ **Test all 4 scenarios** (excellent, good, average, poor)
2. ✅ **Check keyword extraction** (positive/negative)
3. ✅ **Verify rating distribution** (bars show correctly)
4. ✅ **Test responsive design** (mobile view)
5. ✅ **Share with team** for feedback

---

## 💡 Tips

### Add More Reviews
```sql
INSERT INTO reviews (product_id, user_id, rating, comment, approved, created_at)
VALUES (1, 16, 5, 'Your custom review here!', 1, NOW());
```

### Delete Test Data
```sql
DELETE FROM reviews WHERE product_id IN (1,2,3,4);
```

### Reset Auto-increment
```sql
ALTER TABLE reviews AUTO_INCREMENT = 1;
```

---

## 📞 Need Help?

If you encounter issues:

1. Check `test_reviews_data.sql` file
2. Verify product and user IDs exist
3. Look at PHP error logs
4. Check browser console
5. Review `TEST_AI_REVIEW_ANALYZER.md` guide

---

**Ready to see AI in action! 🚀**

Import the data and watch the magic happen! ✨
