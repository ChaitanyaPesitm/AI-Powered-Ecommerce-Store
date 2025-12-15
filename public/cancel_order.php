<?php
require_once __DIR__ . '/../config/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';

    if (!csrf_check($token)) {
        die('Invalid CSRF token');
    }

    try {
        cancelOrder($order_id, $_SESSION['user']['id']);
        $_SESSION['flash_success'] = "✅ Order #$order_id cancelled successfully.";
    } catch (Exception $e) {
        $_SESSION['flash_error'] = "⚠️ " . $e->getMessage();
    }
}

redirect('public/orders.php');
