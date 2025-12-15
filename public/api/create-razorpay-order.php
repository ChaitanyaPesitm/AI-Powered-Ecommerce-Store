<?php
// ✅ Prevent HTML errors from breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/razorpay_config.php';

header('Content-Type: application/json');

// ✅ Logger Function
function logError($msg) {
    $date = date('d-M-Y H:i:s');
    $logMsg = "[$date] $msg" . PHP_EOL;
    file_put_contents(__DIR__ . '/error.log', $logMsg, FILE_APPEND);
}

try {
    if (!isLoggedIn()) {
        throw new Exception('Unauthorized', 401);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed', 405);
    }

    // ✅ Log Raw Input
    $rawInput = file_get_contents('php://input');
    // logError("Raw Input: " . $rawInput);

    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON Input', 400);
    }

    $amount = $input['amount'] ?? 0;

    if ($amount <= 0) {
        throw new Exception('Invalid amount: ' . $amount, 400);
    }

    // ✅ Razorpay Order Creation API
    $url = 'https://api.razorpay.com/v1/orders';
    $data = [
        'amount' => $amount * 100, // Amount in paise
        'currency' => RAZORPAY_CURRENCY,
        'receipt' => 'order_rcptid_' . time(),
        'payment_capture' => 1 // Auto capture
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // ✅ SSL Verification (Optional: Disable if local issues)
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        logError("Curl Error: $curl_error");
        throw new Exception('Razorpay Connection Error', 500);
    }

    // ✅ Log Razorpay Response if not 200
    if ($http_status !== 200) {
        logError("Razorpay Error ($http_status): $response");
        http_response_code($http_status);
        echo $response; // Pass through Razorpay error
        exit;
    }

    echo $response;

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    logError("API Error ($code): " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
