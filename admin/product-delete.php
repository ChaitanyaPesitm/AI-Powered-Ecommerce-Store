<?php 
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    redirect('admin/products.php');
    exit;
}

$id = (int)$_GET['id'];
$pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
redirect('admin/products.php');
exit;