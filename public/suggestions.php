<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
ob_start();

require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/ai.php';

function h($s){ return htmlspecialchars((string)$s); }

// -------------------- DATABASE HELPER FUNCTIONS --------------------

// 1. RAG: Search for relevant products based on keywords
function getRelevantProducts($query, $pdo) {
    // Extract meaningful keywords
    $cleanQuery = preg_replace('/[^a-zA-Z0-9 ]/', '', $query);
    $words = explode(' ', $cleanQuery);
    
    $keywords = [];
    foreach ($words as $w) {
        if (strlen($w) > 2) {
            $keywords[] = $w;
            if (substr($w, -1) === 's') {
                $keywords[] = substr($w, 0, -1);
            }
        }
    }
    $keywords = array_unique($keywords);
    
    if (empty($keywords)) {
        // Fallback
        $stmt = $pdo->query("SELECT p.id, p.name, p.price, p.description, p.image, c.name as category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             ORDER BY RAND() LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Join categories to search category name too
    $sql = "SELECT p.id, p.name, p.price, p.description, p.image, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE ";
            
    $clauses = [];
    $params = [];
    
    foreach ($keywords as $k) {
        // Search in Product Name, Description, OR Category Name
        $clauses[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%$k%";
        $params[] = "%$k%";
        $params[] = "%$k%";
    }
    
    $sql .= implode(' OR ', $clauses);

    // Smart Sorting
    if (preg_match('/(cheap|budget|lowest|under|less than|affordable)/i', $query)) {
        $sql .= " ORDER BY p.price ASC";
    } elseif (preg_match('/(expensive|premium|highest|best|top)/i', $query)) {
        $sql .= " ORDER BY p.price DESC";
    }

    $sql .= " LIMIT 50"; 

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        $stmt = $pdo->query("SELECT p.id, p.name, p.price, p.description, p.image, c.name as category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             ORDER BY p.price DESC LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $results;
}

// 2. Fetch specific products by IDs for rendering
function getProductsByIds($ids, $pdo) {
    if (empty($ids)) return [];
    $ids = array_map('intval', $ids);
    $in  = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT * FROM products WHERE id IN ($in)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// -------------------- INIT CHAT SESSION --------------------
if (!isset($_SESSION['suggestions_chat'])) {
    ob_end_clean();
    $_SESSION['suggestions_chat'] = [[
        'role' => 'assistant',
        'content' => "👋 Hi there! Welcome to The Seventh Com 🛍️\n\nI’m your AI Shopping Copilot. I can help you find products, compare options, and choose the best one for your needs.\n\nTry asking:\n• “Show me gaming laptops under ₹60,000”\n• “Which smartwatch is best for fitness?”",
        'product_ids' => [] 
    ]];
}

// Clear chat
if (isset($_POST['action']) && $_POST['action'] === 'clear_chat') {
    $_SESSION['suggestions_chat'] = [[
        'role' => 'assistant',
        'content' => "👋 Chat cleared! Let's start fresh 😊 What would you like to explore today?",
        'product_ids' => []
    ]];
    header('Location: ' . base_url('public/suggestions.php'));
    exit;
}

// -------------------- AI RESPONSE LOGIC --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    global $pdo;
    $query = trim($_POST['query']);
    
    $_SESSION['suggestions_chat'][] = [
        'role' => 'user', 
        'content' => $query,
        'product_ids' => []
    ];

    $_SESSION['suggestions_chat'][] = [
        'role' => 'assistant', 
        'content' => "🤖 Thinking... please wait!",
        'product_ids' => []
    ];

    // 1. Retrieve Context
    $relevantProducts = getRelevantProducts($query, $pdo);
    
    $catalogText = "Product Catalog (ID: Name [Category] - Price | Description):\n";
    foreach ($relevantProducts as $p) {
        $cat = $p['category_name'] ?? 'General';
        // Increased description limit to 400 to include more specs for Q&A
        $catalogText .= "ID:{$p['id']} | {$p['name']} [{$cat}] - ₹" . number_format($p['price'], 2) . " | " . mb_strimwidth($p['description'], 0, 400, "...") . "\n";
    }

    // Detect Budget for AI Hint
    $budgetHint = "";
    if (preg_match('/(?:under|below|less than|budget)\s*(?:₹|rs\.?)?\s*([\d,]+)/i', $query, $matches)) {
        $detectedPrice = (int)str_replace(',', '', $matches[1]);
        $budgetHint = "SYSTEM NOTE: User's detected budget is {$detectedPrice}. Compare prices numerically. Example: 130000 is LESS than 150000.";
    }

    // 2. Construct Prompt
    $systemInstruction = "You are a helpful AI shopping assistant for 'The Seventh Com'.
    1. Analyze the user's query and the provided Product Catalog.
    2. **Context**: Ignore price constraints from previous messages. Focus ONLY on the current query.
    3. **Comparison**: If the user asks to COMPARE or for the BEST option, analyze specs and price to give a detailed recommendation.
    4. **Specific Questions**: If the user asks about specific features (e.g. 'Does X have bluetooth?', 'Battery life of Y?'), use the provided Description to answer accurately. If the info is not in the catalog, admit you don't know.
    5. **Price Constraints**:
       - $budgetHint
       - If items exist under the budget, recommend them.
       - If NONE exist under the budget, recommend the CLOSEST matches in the SAME category and explain they are alternatives.
       - **CRITICAL**: You MUST include the IDs of ALL recommended products (exact matches OR alternatives) in 'recommended_product_ids'.
    6. **Response Format**: You MUST return strict JSON:
       {
         \"message\": \"Your conversational response here. Use emojis. Be helpful.\",
         \"recommended_product_ids\": [101, 105] 
       }
    7. 'recommended_product_ids' should be an array of integers (IDs).
    8. No markdown formatting.";

    $messages = [
        ["role" => "system", "content" => $systemInstruction],
        ["role" => "user", "content" => "Product Catalog:\n$catalogText\n\nUser Query: $query"]
    ];

    // 3. Call API
    $url = AI_API_URL;
    $payload = json_encode([
        "model" => AI_MODEL,
        "messages" => $messages,
        "temperature" => 0.7,
        "max_tokens" => 800,
        "response_format" => ["type" => "json_object"] // Enforce JSON if supported by model, otherwise prompt handles it
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    $responseText = "⚠️ Error: AI service unavailable.";
    $productIds = [];

    if ($res !== false) {
        $json = json_decode($res, true);
        
        if (!empty($json['choices'][0]['message']['content'])) {
            $rawContent = $json['choices'][0]['message']['content'];
            
            // Attempt to parse JSON response from AI
            $aiData = json_decode($rawContent, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($aiData['message'])) {
                $responseText = $aiData['message'];
                $productIds = $aiData['recommended_product_ids'] ?? [];
            } else {
                // Fallback if AI didn't return valid JSON (rare with good prompting)
                $responseText = $rawContent;
                $productIds = [];
            }
        } elseif (!empty($json['error'])) {
            $responseText = "⚠️ API Error: " . $json['error']['message'];
        }
    } elseif ($err) {
        $responseText = "⚠️ Connection error: " . $err;
    }

    // Replace “thinking” message
    array_pop($_SESSION['suggestions_chat']);
    $_SESSION['suggestions_chat'][] = [
        'role' => 'assistant', 
        'content' => $responseText,
        'product_ids' => $productIds
    ];

    header('Location: ' . base_url('public/suggestions.php?typed=1'));
    exit;
}

// -------------------- RENDER FUNCTION --------------------
function renderProductCards($ids) {
    global $pdo;
    if (empty($ids)) return '';

    $products = getProductsByIds($ids, $pdo);
    if (empty($products)) return '';

    $html = "<div class='row g-3 mt-2'>";
    foreach ($products as $f) {
        $img = $f['image'] ? base_url('assets/uploads/'.$f['image']) : 'https://via.placeholder.com/280x200?text=No+Image';
        $url = base_url('public/product.php?id='.$f['id']);
        $cartUrl = base_url('public/cart_add.php');
        
        $html .= "
        <div class='col-md-4 col-sm-6'>
          <div class='card h-100 shadow-sm border-0 product-card'>
            <div class='position-relative'>
              <img src='$img' class='card-img-top' style='height:200px;object-fit:cover;' alt='{$f['name']}'>
            </div>
            <div class='card-body d-flex flex-column p-3'>
              <h6 class='fw-bold text-dark mb-2 text-center' style='min-height:40px; font-size: 0.95rem;'>{$f['name']}</h6>
              <p class='text-success fw-bold fs-5 mb-3 text-center'>₹".number_format($f['price'],2)."</p>
              <div class='mt-auto'>
                <a href='$url' class='btn btn-outline-primary w-100 mb-2 btn-sm'>
                  <i class='fas fa-eye me-1'></i> View
                </a>
                <form method='post' action='$cartUrl' class='m-0'>
                  <input type='hidden' name='product_id' value='{$f['id']}'>
                  <input type='hidden' name='qty' value='1'>
                  <button type='submit' class='btn btn-primary w-100 btn-sm'>
                    <i class='fas fa-shopping-cart me-1'></i> Add
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>";
    }
    return $html . "</div>";
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="ai-chat-container">
  <div class="container py-5">
    <div class="chat-box">
      <!-- Header -->
      <div class="chat-header">
        <div class="d-flex align-items-center">
          <div class="ai-avatar me-3">
            <i class="fas fa-robot"></i>
          </div>
          <div>
            <h4 class="mb-0 fw-bold">AI Shopping Assistant</h4>
            <small class="text-white-50"><i class="fas fa-circle text-success" style="font-size: 8px;"></i> Online • Powered by Llama 3</small>
          </div>
        </div>
        <form method="post" class="m-0">
          <input type="hidden" name="action" value="clear_chat">
          <button type="submit" class="btn btn-sm btn-outline-light" title="Clear Chat">
            <i class="fas fa-trash-alt"></i>
          </button>
        </form>
      </div>

      <!-- Chat Messages -->
      <div class="chat-window" id="chatMessages">
        <?php foreach ($_SESSION['suggestions_chat'] as $idx => $msg): ?>
          <?php if ($msg['role'] === 'user'): ?>
            <div class="message-wrapper user-message">
              <div class="message-bubble user-bubble" data-msg-index="<?= $idx ?>">
                <?= nl2br(h($msg['content'])) ?>
              </div>
              <div class="message-avatar">
                <i class="fas fa-user"></i>
              </div>
            </div>
          <?php else: ?>
            <div class="message-wrapper ai-message">
              <div class="message-avatar ai-avatar-small">
                <i class="fas fa-robot"></i>
              </div>
              <div class="message-bubble ai-bubble" data-msg-index="<?= $idx ?>">
                <p class="mb-0"><?= nl2br(h($msg['content'])) ?></p>
                <?php 
                  // Render products if IDs are present
                  if (!empty($msg['product_ids'])) {
                      echo renderProductCards($msg['product_ids']);
                  }
                ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <!-- Input Form -->
      <div class="chat-input-container">
        <form method="post" id="chatForm" class="chat-input-form">
          <input 
            name="query" 
            id="queryInput" 
            class="chat-input" 
            placeholder="Ask about products, prices, or recommendations..." 
            required
            autocomplete="off"
          >
          <button type="submit" class="send-button">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
        <div class="quick-suggestions mt-2">
          <small class="text-muted me-2">Try:</small>
          <button class="suggestion-chip" onclick="document.getElementById('queryInput').value='Show me laptops under ₹50000'; return false;">
            💻 Laptops
          </button>
          <button class="suggestion-chip" onclick="document.getElementById('queryInput').value='Best smartphones'; return false;">
            📱 Smartphones
          </button>
          <button class="suggestion-chip" onclick="document.getElementById('queryInput').value='Gaming accessories'; return false;">
            🎮 Gaming
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- ✅ Modern Enhanced Styles -->
<style>
/* Container & Background */
.ai-chat-container {
  min-height: 80vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

.ai-chat-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
  background-size: cover;
  opacity: 0.5;
}

/* Chat Box */
.chat-box {
  max-width: 1000px;
  margin: 0 auto;
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  overflow: hidden;
  position: relative;
  z-index: 1;
}

/* Header */
.chat-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 25px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 3px solid rgba(255,255,255,0.2);
}

.ai-avatar {
  width: 50px;
  height: 50px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #667eea;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.ai-avatar-small {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
}

/* Chat Window */
.chat-window {
  height: 550px;
  overflow-y: auto;
  padding: 25px;
  background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
}

.chat-window::-webkit-scrollbar {
  width: 8px;
}

.chat-window::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.chat-window::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
}

.chat-window::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

/* Message Wrappers */
.message-wrapper {
  display: flex;
  margin-bottom: 20px;
  animation: slideIn 0.3s ease-out;
}

.user-message {
  justify-content: flex-end;
  gap: 12px;
}

.ai-message {
  justify-content: flex-start;
  gap: 12px;
}

/* Message Bubbles */
.message-bubble {
  max-width: 75%;
  padding: 14px 18px;
  border-radius: 18px;
  word-wrap: break-word;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  animation: fadeIn 0.4s ease-out;
}

.user-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.ai-bubble {
  background: white;
  color: #333;
  border: 1px solid #e9ecef;
  border-bottom-left-radius: 4px;
}

.message-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
  font-size: 16px;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
}

/* Product Cards */
.ai-bubble .row {
  margin: 15px -8px 0;
}

.product-card {
  transition: all 0.3s ease;
  border: 2px solid #e9ecef !important;
  border-radius: 12px !important;
  overflow: hidden;
  background: white;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25) !important;
  border-color: #667eea !important;
}

.product-card img {
  border-bottom: 2px solid #f0f0f0;
  transition: transform 0.3s ease;
}

.product-card:hover img {
  transform: scale(1.05);
}

.product-card .card-body {
  background: #ffffff;
}

.product-card .btn {
  font-size: 13px;
  padding: 8px 12px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.product-card .btn-outline-primary {
  border: 2px solid #667eea;
  color: #667eea;
}

.product-card .btn-outline-primary:hover {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
}

.product-card .btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
}

.product-card .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Ensure AI bubble expands for products */
.ai-bubble:has(.row) {
  max-width: 95%;
  padding: 20px;
}

/* Input Container */
.chat-input-container {
  padding: 20px 25px;
  background: white;
  border-top: 2px solid #f0f0f0;
}

.chat-input-form {
  display: flex;
  gap: 12px;
  align-items: center;
}

.chat-input {
  flex: 1;
  padding: 14px 20px;
  border: 2px solid #e9ecef;
  border-radius: 25px;
  font-size: 15px;
  transition: all 0.3s ease;
  outline: none;
}

.chat-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.send-button {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-button:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.6);
}

.send-button:active {
  transform: scale(0.95);
}

/* Quick Suggestions */
.quick-suggestions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}

.suggestion-chip {
  padding: 6px 14px;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 20px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #495057;
}

.suggestion-chip:hover {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: transparent;
  transform: translateY(-2px);
}

/* Animations */
@keyframes slideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Responsive */
@media (max-width: 768px) {
  .chat-box {
    border-radius: 0;
    height: 100vh;
  }
  .message-bubble {
    max-width: 85%;
  }
  .chat-window {
    height: calc(100vh - 250px);
  }
}
</style>

<!-- ✅ Typing Animation -->
<!-- ✅ Typing Animation (Optimized) -->
<script>
function scrollToBottom() {
  const chat = document.getElementById('chatMessages');
  if (chat) {
    // Use smooth scroll only if not near bottom to avoid "fighting" the user
    chat.scrollTo({
      top: chat.scrollHeight,
      behavior: 'smooth'
    });
  }
}

// Initial scroll
window.addEventListener('load', scrollToBottom);

// Typing animation for AI responses
window.addEventListener('load', () => {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('typed')) {
    setTimeout(() => {
      const bubbles = document.querySelectorAll('.ai-bubble');
      if (!bubbles.length) return;
      const last = bubbles[bubbles.length - 1];
      
      // Get text part only (first p tag)
      const textP = last.querySelector('p');
      if (!textP) return;
      
      const fullText = textP.textContent;
      textP.textContent = '';
      
      // Hide product row initially if present
      const productRow = last.querySelector('.row');
      if (productRow) {
        productRow.style.opacity = '0';
        productRow.style.display = 'none'; 
      }
      
      let i = 0;
      const speed = 10; // Faster typing (ms per chunk)
      const chunkSize = 2; // Add 2 chars at a time for smoother/faster feel
      
      function typeLoop() {
        if (i < fullText.length) {
          // Append chunk
          textP.textContent += fullText.substring(i, i + chunkSize);
          i += chunkSize;
          
          // Scroll every few frames, not every loop
          if (i % 10 === 0) {
            requestAnimationFrame(() => {
                const chat = document.getElementById('chatMessages');
                if (chat) chat.scrollTop = chat.scrollHeight;
            });
          }
          
          setTimeout(typeLoop, speed);
        } else {
          // Done typing
          // Ensure full text is there (in case chunk math missed last char)
          textP.textContent = fullText;
          
          // Fade in products if any
          if (productRow) {
            productRow.style.display = 'flex';
            // Trigger reflow
            void productRow.offsetWidth; 
            productRow.style.transition = 'opacity 0.5s ease';
            productRow.style.opacity = '1';
            
            // Final scroll
            setTimeout(() => {
               const chat = document.getElementById('chatMessages');
               if (chat) chat.scrollTop = chat.scrollHeight;
            }, 100);
          }
        }
      }
      
      typeLoop();
      
    }, 300);
  }
});

// Auto-scroll on form submit
const form = document.getElementById('chatForm');
if (form) {
  form.addEventListener('submit', () => {
    // Instant scroll to show user message
    const chat = document.getElementById('chatMessages');
    if (chat) chat.scrollTop = chat.scrollHeight;
  });
}
</script>
