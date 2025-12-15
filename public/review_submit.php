<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/functions.php';
requireLogin(); // only logged-in users may submit

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('public/index.php');

// ensure session present for CSRF
if (session_status() === PHP_SESSION_NONE) session_start();

$user_id   = $_SESSION['user']['id'];
$product_id= (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$title     = trim($_POST['title'] ?? '');
$comment   = trim($_POST['comment'] ?? '');
$order_id  = !empty($_POST['order_id']) ? (int)$_POST['order_id'] : null;
$csrf      = $_POST['csrf_token'] ?? '';

// Simple CSRF check (optional but recommended)
if (empty($csrf) || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    redirect("public/product.php?id=$product_id&msg=invalid_csrf");
}

// Basic validation
if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    redirect("public/product.php?id=$product_id&msg=invalid_input");
}

// Check purchase
if (!canUserReview($user_id, $product_id)) {
    redirect("public/product.php?id=$product_id&msg=not_purchased");
}

// Prevent duplicate
if (hasUserReviewedProduct($user_id, $product_id)) {
    redirect("public/product.php?id=$product_id&msg=already_reviewed");
}

// Insert
$rid = addReview($user_id, $product_id, $order_id, $rating, $title, $comment);
if ($rid) redirect("public/product.php?id=$product_id&msg=review_submitted");
else redirect("public/product.php?id=$product_id&msg=error");
