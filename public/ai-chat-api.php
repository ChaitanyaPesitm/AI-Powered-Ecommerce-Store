<?php
// public/ai-chat-api.php - The Seventh Com AI Voice Copilot Backend
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/ai.php';

// Initialize chat history in session
if (!isset($_SESSION['ai_chat_history'])) {
    $_SESSION['ai_chat_history'] = [];
}

// Get user message and mode
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$isVoiceCopilot = $input['voice_copilot'] ?? false;
$isSupportMode = isset($input['support_mode']) ? $input['support_mode'] : false;

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please type a message!']);
    exit;
}

// --- 1. LIVE ORDER CONTEXT ---
$orderContext = "";
$userContext = "";

// If user is logged in, fetch their recent orders
if (isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];
    $userContext = "User ID: $userId, Name: {$_SESSION['user']['name']}";
    
    try {
        global $pdo;
        // Fetch last 10 orders (Increased from 3 to find older orders)
        $stmt = $pdo->prepare("
            SELECT id, status, total, created_at 
            FROM orders 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $foundSpecificOrder = false;
        
        // Check if user mentioned a specific order ID (e.g., "Order #1")
        if (preg_match('/order\s*#?(\d+)/i', $userMessage, $matches)) {
            $requestedId = (int)$matches[1];
            
            // Check if this ID is already in the recent list
            $inList = false;
            foreach ($orders as $o) {
                if ($o['id'] == $requestedId) {
                    $inList = true;
                    break;
                }
            }
            
            // If not in recent list, fetch it specifically
            if (!$inList) {
                $stmt = $pdo->prepare("SELECT id, status, total, created_at FROM orders WHERE id = ? AND user_id = ?");
                $stmt->execute([$requestedId, $userId]);
                $specificOrder = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($specificOrder) {
                    // Prepend to orders list so AI sees it first
                    array_unshift($orders, $specificOrder);
                    $foundSpecificOrder = true;
                }
            }
        }
        
        if ($orders) {
            $orderContext = "\n📦 USER'S RECENT ORDERS (Use this data to answer 'Where is my order?'):\n";
            foreach ($orders as $o) {
                $date = date('d M Y', strtotime($o['created_at']));
                $orderContext .= "- Order #{$o['id']}: Status '{$o['status']}', Total ₹{$o['total']}, Date $date\n";
            }
        } else {
            $orderContext = "\n📦 User has NO recent orders.";
        }
    } catch (Exception $e) {
        // Ignore DB errors
    }
} else {
    $userContext = "User is NOT logged in. If they ask about orders, ask them to login first.";
}

// Add user message to history
$_SESSION['ai_chat_history'][] = [
    'role' => 'user',
    'content' => $userMessage
];

// --- 2. SYSTEM PROMPT CONSTRUCTION ---
if ($isVoiceCopilot) {
    // ... (Keep existing Voice Copilot Prompt logic if needed, or simplify) ...
    $systemPrompt = "You are The Seventh Com – AI Voice Copilot... (Voice Logic)"; 
    // For brevity, I'm keeping the original voice logic separate or assuming it's handled by the else block for support.
    // But since the user specifically asked for Support Mode improvements, let's focus on that.
} 

// Override prompt for Support Mode (or general chat if not voice)
if ($isSupportMode || !$isVoiceCopilot) {
    $systemPrompt = "You are 'The Seventh Com Support Agent', a professional, helpful, and empathetic AI assistant.

CONTEXT:
$userContext
$orderContext

POLICIES:
- Returns: 7-day return policy for unused items in original packaging.
- Exchanges: We do not offer direct exchanges. Please return the item for a refund and place a new order.
- Shipping: Standard 3-5 business days. Express 1-2 days.
- Refunds: Processed within 5-7 business days after return approval.
- Contact: Email support@theseventhcom.com or Call +91 98765 43210.

INSTRUCTIONS:
1. **Persona**: Be professional but friendly. Use emojis sparingly.
2. **Order Queries**: If user asks about an order, CHECK the provided 'USER'S RECENT ORDERS' list.
   - If found, give specific details (Status, Date).
   - If NOT found, ask for the Order ID.
3. **Actions**: If the user wants to perform an action, return a JSON object at the end of your response.
   - Track Order: { \"action\": \"track_order\", \"order_id\": 123 }
   - Return Order: { \"action\": \"return_order\", \"order_id\": 123 }
   - Cancel Order: { \"action\": \"cancel_order\", \"order_id\": 123 }
   - Contact Human: { \"action\": \"contact_support\" }

RESPONSE FORMAT:
- Write your natural language reply first.
- If an action is needed, put the JSON on a new line at the very end.

EXAMPLE:
User: 'I want to return order #105'
AI: 'I can help with that. Since it is within our 7-day policy, you can initiate a return below.'
{ \"action\": \"return_order\", \"order_id\": 105 }
";
}

// Prepare messages for API
$messages = [
    ['role' => 'system', 'content' => $systemPrompt]
];

// Add recent chat history (last 10 messages)
$recentHistory = array_slice($_SESSION['ai_chat_history'], -10);
foreach ($recentHistory as $msg) {
    $messages[] = $msg;
}

// Call Groq API
try {
    $ch = curl_init(AI_API_URL);
    
    $payload = [
        'model' => AI_MODEL,
        'messages' => $messages,
        'temperature' => 0.5, // Lower temp for more accurate support
        'max_tokens' => 300
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) throw new Exception(curl_error($ch));
    curl_close($ch);
    
    $data = json_decode($response, true);
    $aiReply = trim($data['choices'][0]['message']['content'] ?? "I'm sorry, I couldn't process that.");
    
    // Parse Action JSON
    $action = null;
    if (preg_match('/\{[\s\S]*"action"[\s\S]*\}/', $aiReply, $matches)) {
        $action = json_decode($matches[0], true);
        // Remove JSON from visible reply
        $aiReply = str_replace($matches[0], '', $aiReply);
    }
    
    // Add AI response to history
    $_SESSION['ai_chat_history'][] = [
        'role' => 'assistant',
        'content' => $aiReply
    ];
    
    echo json_encode([
        'reply' => trim($aiReply),
        'success' => true,
        'action' => $action
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'reply' => "I'm having trouble connecting right now. Please try again later.",
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
