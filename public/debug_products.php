<?php
require_once __DIR__ . '/../config/functions.php';
global $pdo;
$stmt = $pdo->query("SELECT id, name, price FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total Products: " . count($products) . "\n";
foreach ($products as $p) {
    echo "{$p['id']}: {$p['name']} - {$p['price']}\n";
}
