<?php
require_once __DIR__ . '/config/functions.php';

echo "🌱 Generating Stories from Products...\n";

// Fetch latest 5 products
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 5")->fetchAll();

if (empty($products)) {
    echo "⚠️ No products found.\n";
    exit;
}

// Clear existing stories (optional, but good for clean slate)
$pdo->exec("TRUNCATE TABLE stories");

$stmt = $pdo->prepare("INSERT INTO stories (title, image, content_image) VALUES (?, ?, ?)");

foreach ($products as $p) {
    $title = $p['name'];
    // Use product image for both thumbnail and content
    $image = $p['image'] ?: 'https://via.placeholder.com/500'; 
    $content_image = $p['image'] ?: 'https://via.placeholder.com/800';

    $stmt->execute([$title, $image, $content_image]);
    echo "✅ Created story for: " . $title . "\n";
}

echo "🚀 Done! Refresh your homepage.\n";
?>
