<?php
/**
 * API Endpoint for Live Search
 */

require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

// Search products (limit to 5 for dropdown)
$products = getProducts($q, 0, 5);

$results = [];
foreach ($products as $p) {
    $results[] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'price' => $p['price'],
        'image' => $p['image'] ? base_url('assets/uploads/' . $p['image']) : null,
        'url' => base_url('public/product.php?id=' . $p['id'])
    ];
}

echo json_encode(['success' => true, 'data' => $results]);
