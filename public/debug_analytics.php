<?php
require_once __DIR__ . '/../config/functions.php';

echo "<h1>Analytics Debugger</h1>";

// 1. Check Orders Table
echo "<h2>1. Orders Table</h2>";
$orders = $pdo->query("SELECT * FROM orders LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if ($orders) {
    echo "<pre>" . print_r($orders, true) . "</pre>";
} else {
    echo "<p style='color:red'>Orders table is EMPTY!</p>";
}

// 2. Check Order Items
echo "<h2>2. Order Items Table</h2>";
$items = $pdo->query("SELECT * FROM order_items LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if ($items) {
    echo "<pre>" . print_r($items, true) . "</pre>";
} else {
    echo "<p style='color:red'>Order Items table is EMPTY!</p>";
}

// 3. Check Categories Join
echo "<h2>3. Category Stats Query</h2>";
try {
    $catStats = $pdo->query("
        SELECT c.name, COUNT(oi.id) as count
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        GROUP BY c.id
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($catStats, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
