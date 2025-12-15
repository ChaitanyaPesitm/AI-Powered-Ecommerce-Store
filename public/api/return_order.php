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
$reason = $input['reason'] ?? 'No reason provided';
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

    // 2. Check Status (Only delivered orders can be returned)
    // 2. Check Status (Only delivered or completed orders can be returned)
    if (!in_array(strtolower($order['status']), ['delivered', 'completed'])) {
        throw new Exception('Only delivered or completed orders can be returned.');
    }

    // 3. Update Status to 'Returned' (or 'Return Requested' if you want an approval flow)
    // For simplicity, we'll mark it as 'Returned' directly or 'Return Requested'
    $pdo->prepare("UPDATE orders SET status = 'Returned' WHERE id = ?")->execute([$order_id]);

    // 4. Restore Stock (Optional: depends on policy. Usually checked physically first. We will restore for now)
    $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$order_id]);
    
    foreach ($items as $item) {
        $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
            ->execute([$item['qty'], $item['product_id']]);
    }

    echo json_encode(['success' => true, 'message' => 'Order returned successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
