<?php
require_once __DIR__ . '/config/razorpay_config.php';

echo "Testing Razorpay Keys...\n";
echo "Key ID: " . RAZORPAY_KEY_ID . "\n";

$url = 'https://api.razorpay.com/v1/orders';
$data = [
    'amount' => 100, // 1 INR
    'currency' => RAZORPAY_CURRENCY,
    'receipt' => 'test_receipt_' . time(),
    'payment_capture' => 1
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL Error: " . $error . "\n";
} else {
    echo "HTTP Status: " . $http_status . "\n";
    echo "Response: " . $response . "\n";
}
?>
