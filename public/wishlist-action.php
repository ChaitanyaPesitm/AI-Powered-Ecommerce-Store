<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/functions.php';
requireLogin();

$user_id    = $_SESSION['user']['id'];
$action     = $_GET['action'] ?? $_POST['action'] ?? '';
$product_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// where to return after action
$returnTo = $_GET['return'] ?? $_POST['return'] ?? base_url('public/wishlist.php');

if ($action === 'add' && $product_id > 0) {
    wishlist_add($user_id, $product_id);
    header('Location: ' . $returnTo);
    exit;
}

if ($action === 'remove' && $product_id > 0) {
    wishlist_remove($user_id, $product_id);
    header('Location: ' . $returnTo);
    exit;
}

// default
header('Location: ' . base_url('public/wishlist.php'));
