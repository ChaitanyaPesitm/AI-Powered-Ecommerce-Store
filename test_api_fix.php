<?php
// Simulate a logged-in user session
session_start();
$_SESSION['user'] = ['id' => 1, 'name' => 'Test User', 'role' => 'user'];

$url = 'http://localhost/ecommerce/public/api/create-razorpay-order.php';
$data = ['amount' => 100];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: " . $http_status . "\n";
echo "Response: " . $response . "\n";
?>
