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

    // 2. Check Status (Allow cancel if pending or processing)
    if (!in_array(strtolower($order['status']), ['pending', 'processing'])) {
        throw new Exception('Order cannot be cancelled at this stage.');
    }

    // 3. Update Status
    $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?")->execute([$order_id]);

    // 4. Restore Stock
    $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$order_id]);
    
    foreach ($items as $item) {
        $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
            ->execute([$item['qty'], $item['product_id']]);
    }

    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
