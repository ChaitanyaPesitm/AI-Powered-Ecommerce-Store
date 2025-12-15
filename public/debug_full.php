<?php
require_once __DIR__ . '/../config/functions.php';
global $pdo;

echo "--- Products ---\n";
$stmt = $pdo->query("SELECT id, name, price, category_id, description FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($products as $p) {
    echo "[{$p['id']}] {$p['name']} (Cat: {$p['category_id']}) - {$p['price']}\n";
    echo "Desc: {$p['description']}\n\n";
}

echo "\n--- Categories ---\n";
try {
    $stmt = $pdo->query("SELECT * FROM categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cats as $c) {
        echo "[{$c['id']}] {$c['name']}\n";
    }
} catch (Exception $e) {
    echo "Categories table error: " . $e->getMessage();
}
