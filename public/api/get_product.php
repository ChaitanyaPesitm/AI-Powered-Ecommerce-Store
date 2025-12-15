<?php
/**
 * API Endpoint to get product details
 */

require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$product = getProduct($id);

if ($product) {
    // Add image URL
    $product['image_url'] = $product['image'] 
        ? base_url('assets/uploads/' . $product['image']) 
        : 'https://via.placeholder.com/400x300?text=No+Image';
        
    // Add product URL
    $product['url'] = base_url('public/product.php?id=' . $product['id']);
    
    echo json_encode(['success' => true, 'data' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}
