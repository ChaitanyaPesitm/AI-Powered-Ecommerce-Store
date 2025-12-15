<?php
// Simple test file to check AI configuration
session_start();
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/ai.php';

echo "<h1>AI Configuration Test</h1>";
echo "<hr>";

// Test 1: Check if AI constants are defined
echo "<h2>1. Configuration Check:</h2>";
echo "AI_API_KEY: " . (defined('AI_API_KEY') ? "✅ Defined" : "❌ Not defined") . "<br>";
echo "AI_MODEL: " . (defined('AI_MODEL') ? AI_MODEL : "❌ Not defined") . "<br>";
echo "<hr>";

// Test 2: Check session
echo "<h2>2. Session Check:</h2>";
if (!isset($_SESSION['suggestions_chat'])) {
    $_SESSION['suggestions_chat'] = [];
    $_SESSION['suggestions_chat'][] = [
        'role' => 'assistant',
        'content' => "Welcome message test!"
    ];
}
echo "Session chat count: " . count($_SESSION['suggestions_chat']) . "<br>";
echo "First message: " . ($_SESSION['suggestions_chat'][0]['content'] ?? 'None') . "<br>";
echo "<hr>";

// Test 3: Check products
global $pdo;
$products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch();
echo "<h2>3. Database Check:</h2>";
echo "Total products: " . $products['count'] . "<br>";
echo "<hr>";

// Test 4: Test AI API
echo "<h2>4. AI API Test:</h2>";
$testPrompt = "Say 'Hello, I am working!' in one sentence.";
$url = "https://generativelanguage.googleapis.com/v1/models/" . AI_MODEL . ":generateContent?key=" . AI_API_KEY;
$payload = json_encode(["contents"=>[["parts"=>[["text"=>$testPrompt]]]]]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 10
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $httpCode . "<br>";

if ($res === false) {
    echo "❌ Connection failed<br>";
} else {
    $json = json_decode($res, true);
    if (isset($json['error'])) {
        echo "❌ API Error: " . $json['error']['message'] . "<br>";
    } else {
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
        echo "✅ AI Response: " . htmlspecialchars($text) . "<br>";
    }
}

echo "<hr>";
echo "<h2>5. Actions:</h2>";
echo "<a href='suggestions.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Go to AI Assistant</a><br><br>";
echo "<form method='post' action='suggestions.php'>";
echo "<input type='hidden' name='action' value='clear_chat'>";
echo "<button type='submit' style='padding: 10px 20px; background: #f56565; color: white; border: none; border-radius: 5px; cursor: pointer;'>Clear Chat & Reset</button>";
echo "</form>";
?>
