<?php
/**
 * Add product to cart
 * Handles adding products to shopping cart
 */

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/functions.php';

// Get product ID and quantity
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

// Validate inputs
if ($product_id <= 0) {
    $_SESSION['error'] = 'Invalid product';
    header('Location: ' . base_url('public/products.php'));
    exit;
}

if ($qty <= 0) {
    $qty = 1;
}

// Add to cart
cart_add($product_id, $qty);

// Check if coming from wishlist
if (isset($_POST['from_wishlist']) && isLoggedIn()) {
    // Remove from wishlist after adding to cart
    global $pdo;
    $user_id = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
}

// Set success message
$_SESSION['success'] = 'Product added to cart successfully!';

// Always redirect to cart page
header('Location: ' . base_url('public/cart.php'));
exit;
