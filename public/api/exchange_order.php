<?php
require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? 0;
$reason = $input['reason'] ?? 'Exchange requested';
$user_id = $_SESSION['user']['id'];

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

try {
    // 1. Fetch Order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception('Order not found');
    }

    // 2. Check Status (Only delivered orders can be exchanged)
    // 2. Check Status (Only delivered or completed orders can be exchanged)
    if (!in_array(strtolower($order['status']), ['delivered', 'completed'])) {
        throw new Exception('Only delivered or completed orders can be exchanged.');
    }

    // 3. Update Status
    $pdo->prepare("UPDATE orders SET status = 'Exchange Requested' WHERE id = ?")->execute([$order_id]);

    // Note: We don't restore stock immediately for exchange, as we need to receive the item first.

    echo json_encode(['success' => true, 'message' => 'Exchange request submitted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
