<?php
// public/process-return.php - Process Return Request
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/functions.php';
requireLogin();

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== csrf_token()) {
    $_SESSION['error'] = 'Invalid request. Please try again.';
    header('Location: orders.php');
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$reason = trim($_POST['reason'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validate inputs
if (empty($order_id) || empty($reason)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: return-order.php?id=' . $order_id);
    exit;
}

// Fetch order details
$st = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$st->execute([$order_id, $_SESSION['user']['id']]);
$order = $st->fetch();

if (!$order) {
    $_SESSION['error'] = 'Order not found.';
    header('Location: orders.php');
    exit;
}

// Check if order is eligible for return
$status = strtolower($order['status']);
if (!in_array($status, ['completed', 'delivered'])) {
    $_SESSION['error'] = 'Only completed or delivered orders can be returned.';
    header('Location: orders.php');
    exit;
}

try {
    // Create returns table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `returns` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `order_id` INT(11) NOT NULL,
          `user_id` INT(11) NOT NULL,
          `reason` VARCHAR(255) NOT NULL,
          `description` TEXT,
          `status` ENUM('pending', 'approved', 'rejected', 'refunded') DEFAULT 'pending',
          `refund_amount` DECIMAL(10,2) DEFAULT 0.00,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `order_id` (`order_id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Check if return already exists
    $checkReturn = $pdo->prepare("SELECT * FROM returns WHERE order_id = ?");
    $checkReturn->execute([$order_id]);
    if ($checkReturn->fetch()) {
        $_SESSION['error'] = 'A return request already exists for this order.';
        header('Location: orders.php');
        exit;
    }

    // Insert return request
    $insertReturn = $pdo->prepare("
        INSERT INTO returns (order_id, user_id, reason, description, refund_amount, status)
        VALUES (?, ?, ?, ?, ?, 'refunded')
    ");
    
    $insertReturn->execute([
        $order_id,
        $_SESSION['user']['id'],
        $reason,
        $description,
        $order['total']
    ]);

    // Update order status to 'returned'
    $updateOrder = $pdo->prepare("UPDATE orders SET status = 'returned' WHERE id = ?");
    $updateOrder->execute([$order_id]);

    $_SESSION['success'] = 'Return request submitted successfully! Your refund of ₹' . number_format($order['total'], 2) . ' has been processed.';
    header('Location: return-success.php?id=' . $order_id);
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to process return request. Please try again.';
    error_log('Return processing error: ' . $e->getMessage());
    header('Location: return-order.php?id=' . $order_id);
    exit;
}
