<?php
require_once __DIR__ . '/../../config/functions.php';

header('Content-Type: application/json');

// Ensure admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$return_id = $input['id'] ?? 0;
$action = $input['action'] ?? ''; // 'approve' or 'reject'

if (!$return_id || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Fetch Return Request
    $stmt = $pdo->prepare("SELECT * FROM returns WHERE id = ?");
    $stmt->execute([$return_id]);
    $return = $stmt->fetch();

    if (!$return) {
        throw new Exception('Return request not found');
    }

    if ($return['status'] !== 'pending') {
        throw new Exception('Request is already processed');
    }

    if ($action === 'approve') {
        // Update Return Status
        $pdo->prepare("UPDATE returns SET status = 'approved', updated_at = NOW() WHERE id = ?")->execute([$return_id]);

        // Update Order Status
        $newOrderStatus = ($return['type'] === 'exchange') ? 'Exchange Approved' : 'Returned';
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newOrderStatus, $return['order_id']]);

        // If Return, Restore Stock (Optional - assuming we received the item back)
        if ($return['type'] === 'return') {
            $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemsStmt->execute([$return['order_id']]);
            $items = $itemsStmt->fetchAll();

            foreach ($items as $item) {
                $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
                    ->execute([$item['qty'], $item['product_id']]);
            }
        }

        $message = ucfirst($return['type']) . ' request approved successfully.';

    } else {
        // Reject
        $pdo->prepare("UPDATE returns SET status = 'rejected', updated_at = NOW() WHERE id = ?")->execute([$return_id]);

        // Revert Order Status (or set to specific rejected status)
        // We revert to 'Delivered' or 'Completed' - strictly speaking we should know what it was before, 
        // but usually it was Delivered. Let's set to 'Return Rejected' or just keep it as is?
        // Better to set it back to 'Delivered' so user can try again or see it's done.
        // Or maybe 'Return Rejected' is better for clarity. Let's use 'Return Rejected' / 'Exchange Rejected'.
        
        $rejectStatus = ($return['type'] === 'exchange') ? 'Exchange Rejected' : 'Return Rejected';
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$rejectStatus, $return['order_id']]);

        $message = ucfirst($return['type']) . ' request rejected.';
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
