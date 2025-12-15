<?php
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/razorpay_config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$razorpay_payment_id = $input['razorpay_payment_id'] ?? '';
$razorpay_order_id = $input['razorpay_order_id'] ?? '';
$razorpay_signature = $input['razorpay_signature'] ?? '';
$orderData = $input['order_data'] ?? [];

if (!$razorpay_payment_id || !$razorpay_order_id || !$razorpay_signature) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payment details']);
    exit;
}

// Verify Signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

if ($generated_signature === $razorpay_signature) {
    // Payment Successful
    try {
        // Create Order in Database
        $items = cart_items();
        if (!$items) {
            throw new Exception('Cart is empty');
        }

        // ✅ Set Payment Method to Online
        $orderData['payment_method'] = 'Online';
        $order_id = create_order($orderData, $items);

        // Update Order with Razorpay Details
        $stmt = $pdo->prepare("UPDATE orders SET 
            razorpay_payment_id = ?, 
            razorpay_order_id = ?, 
            razorpay_signature = ?, 
            status = 'paid' 
            WHERE id = ?");
        $stmt->execute([$razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $order_id]);

        cart_clear();

        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Order creation failed: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
}
