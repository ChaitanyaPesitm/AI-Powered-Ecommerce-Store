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
$type = $input['type'] ?? 'return';
$reason = $input['reason'] ?? '';
$description = $input['description'] ?? '';
$user_id = $_SESSION['user']['id'];

if (!$order_id || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
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

    // 2. Check Status (Only delivered or completed orders can be returned/exchanged)
    if (!in_array(strtolower($order['status']), ['delivered', 'completed'])) {
        throw new Exception('Only delivered or completed orders can be returned or exchanged.');
    }

    // 3. Check if request already exists
    $checkStmt = $pdo->prepare("SELECT id FROM returns WHERE order_id = ?");
    $checkStmt->execute([$order_id]);
    if ($checkStmt->fetch()) {
        throw new Exception('A request has already been submitted for this order.');
    }

    // 4. Insert Request
    $insertStmt = $pdo->prepare("INSERT INTO returns (user_id, order_id, type, reason, description, refund_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $insertStmt->execute([
        $user_id,
        $order_id,
        $type,
        $reason,
        $description,
        $order['total'] // Default refund amount is total order amount, admin can adjust
    ]);

    // 5. Update Order Status
    $newStatus = ($type === 'exchange') ? 'Exchange Requested' : 'Return Requested';
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $order_id]);

    echo json_encode(['success' => true, 'message' => 'Request submitted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
