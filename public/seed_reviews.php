<?php
require_once __DIR__ . '/../config/functions.php';
global $pdo;

// 1. Create Dummy Users
$dummyUsers = [
    ['name' => 'Alice Reviewer', 'email' => 'alice@example.com'],
    ['name' => 'Bob Tester', 'email' => 'bob@example.com'],
    ['name' => 'Charlie Shopper', 'email' => 'charlie@example.com'],
    ['name' => 'Diana Tech', 'email' => 'diana@example.com'],
    ['name' => 'Eve Gamer', 'email' => 'eve@example.com']
];

$userIds = [];

foreach ($dummyUsers as $u) {
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$u['email']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $userIds[] = $existing['id'];
    } else {
        // Create
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, 'dummyhash', 'user')");
        $stmt->execute([$u['name'], $u['email']]);
        $userIds[] = $pdo->lastInsertId();
    }
}

echo "User IDs: " . implode(', ', $userIds) . "\n";

// 2. Get Products
$stmt = $pdo->query("SELECT id, name FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Comments Data
$comments = [
    5 => [
        ['title' => 'Amazing!', 'comment' => 'Absolutely love this product. Works perfectly.'],
        ['title' => 'Best purchase', 'comment' => 'Worth every penny. Highly recommended!'],
        ['title' => 'Great quality', 'comment' => 'The build quality is fantastic. exceeded expectations.'],
        ['title' => 'Perfect', 'comment' => 'Just what I needed. Fast shipping too.'],
        ['title' => 'Wow', 'comment' => 'Blown away by the performance.']
    ],
    4 => [
        ['title' => 'Very good', 'comment' => 'Solid product, does the job well.'],
        ['title' => 'Good value', 'comment' => 'Good for the price, but could be slightly better.'],
        ['title' => 'Nice', 'comment' => 'Happy with it overall. No major complaints.'],
        ['title' => 'Pretty good', 'comment' => 'Works as advertised. Good packaging.'],
        ['title' => 'Satisfied', 'comment' => 'I like it, but shipping took a while.']
    ],
    3 => [
        ['title' => 'Okay', 'comment' => 'It is decent, but not great.'],
        ['title' => 'Average', 'comment' => 'You get what you pay for. It is fine.'],
        ['title' => 'Mixed feelings', 'comment' => 'Some features are good, others lacking.'],
        ['title' => 'Decent', 'comment' => 'Not bad, but I have seen better.']
    ]
];

// 4. Insert Reviews
$count = 0;
foreach ($products as $p) {
    // Pick 3-5 random users
    $numReviews = rand(3, 5);
    $selectedUsers = array_rand(array_flip($userIds), $numReviews);
    if (!is_array($selectedUsers)) $selectedUsers = [$selectedUsers];
    
    foreach ($selectedUsers as $uid) {
        // Check if review exists
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$uid, $p['id']]);
        if ($stmt->fetch()) continue; // Skip if already reviewed
        
        // Pick rating (weighted towards 4 and 5)
        $rand = rand(1, 100);
        if ($rand > 80) $rating = 3;
        elseif ($rand > 40) $rating = 4;
        else $rating = 5;
        
        $reviewData = $comments[$rating][array_rand($comments[$rating])];
        
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, title, comment, approved, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([
            $uid,
            $p['id'],
            $rating,
            $reviewData['title'],
            $reviewData['comment']
        ]);
        $count++;
    }
}

echo "Added $count new reviews!\n";
