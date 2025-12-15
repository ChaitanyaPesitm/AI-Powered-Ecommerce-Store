<?php
/**
 * AI Review Analyzer for The Seventh Com
 * Analyzes and summarizes customer reviews intelligently using Groq AI
 */

require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/ai.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    try {
        if ($action === 'analyze_reviews') {
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            
            if ($product_id > 0) {
                $analysis = analyzeProductReviews($product_id);
                echo json_encode($analysis);
            } else {
                echo json_encode(['error' => 'Invalid product ID']);
            }
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to analyze reviews: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Analyze reviews for a product using AI logic
 */
function analyzeProductReviews($product_id) {
    global $pdo;
    
    // Get all approved reviews for the product
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.approved = 1
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reviews)) {
        return [
            'success' => false,
            'message' => 'No reviews available for analysis'
        ];
    }
    
    // 1. Basic Stats
    $total_rating = 0;
    $rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    $trend_data = []; // [Month => Avg Rating]
    
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
        $rating_counts[$review['rating']]++;
        
        // Group by Month for Trend Chart (YYYY-MM)
        $month = date('Y-m', strtotime($review['created_at']));
        if (!isset($trend_data[$month])) {
            $trend_data[$month] = ['sum' => 0, 'count' => 0];
        }
        $trend_data[$month]['sum'] += $review['rating'];
        $trend_data[$month]['count']++;
    }
    
    $avg_rating = round($total_rating / count($reviews), 1);
    
    // Format Trend Data for Chart.js
    $chart_labels = [];
    $chart_values = [];
    ksort($trend_data); // Sort by date
    foreach ($trend_data as $m => $d) {
        $chart_labels[] = date('M Y', strtotime($m));
        $chart_values[] = round($d['sum'] / $d['count'], 1);
    }
    
    // 2. AI Analysis
    // Prepare text for AI (limit to last 30 reviews to save tokens)
    $reviewText = "";
    $count = 0;
    foreach ($reviews as $r) {
        if ($count >= 30) break;
        $reviewText .= "- {$r['rating']} stars: {$r['title']} - {$r['comment']}\n";
        $count++;
    }
    
    $aiResult = callAIAnalysis($reviewText, $avg_rating);
    
    return [
        'success' => true,
        'average_rating' => $avg_rating,
        'total_reviews' => count($reviews),
        'rating_distribution' => $rating_counts,
        'summary' => $aiResult['summary'],
        'pros' => $aiResult['pros'],
        'cons' => $aiResult['cons'],
        'sentiment' => $aiResult['sentiment'],
        'chart_labels' => $chart_labels,
        'chart_values' => $chart_values
    ];
}

/**
 * Call Groq API for Analysis
 */
function callAIAnalysis($reviewText, $avgRating) {
    $url = AI_API_URL;
    
    $systemPrompt = "You are an expert Product Review Analyst. 
    Analyze the provided customer reviews and return a JSON object with:
    1. 'summary': A concise 2-sentence summary of what customers think.
    2. 'pros': An array of top 3 specific positive themes (e.g. 'Battery Life', 'Build Quality').
    3. 'cons': An array of top 3 specific negative themes (e.g. 'Slow Charging', 'Expensive').
    4. 'sentiment': One word label (Excellent, Good, Mixed, Poor).
    
    Be honest and specific. Do not hallucinate features.";
    
    $userPrompt = "Average Rating: $avgRating\nReviews:\n$reviewText";
    
    $payload = json_encode([
        "model" => AI_MODEL,
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userPrompt]
        ],
        "temperature" => 0.5,
        "max_tokens" => 500,
        "response_format" => ["type" => "json_object"]
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
    
    if ($res) {
        $json = json_decode($res, true);
        if (!empty($json['choices'][0]['message']['content'])) {
            $data = json_decode($json['choices'][0]['message']['content'], true);
            if ($data) return $data;
        }
    }
    
    // Fallback if AI fails
    return [
        'summary' => "Analysis unavailable at the moment.",
        'pros' => [],
        'cons' => [],
        'sentiment' => "Unknown"
    ];
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Review Analyzer - The Seventh Com</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🛒</text></svg>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; color: white; margin-bottom: 30px; }
        .header h1 { font-size: 36px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 15px; }
        .analyzer-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin-bottom: 30px; }
        .product-selector label { display: block; font-weight: 600; color: #2d3748; margin-bottom: 10px; }
        .product-selector select { width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; cursor: pointer; }
        .analyze-btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 15px; transition: transform 0.2s; }
        .analyze-btn:hover { transform: translateY(-2px); }
        .results { display: none; animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        .rating-summary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 15px; text-align: center; margin-bottom: 30px; }
        .big-rating { font-size: 48px; font-weight: 700; }
        .sentiment-badge { display: inline-block; padding: 8px 20px; background: rgba(255,255,255,0.2); border-radius: 20px; font-weight: 600; margin-top: 10px; }
        
        .summary-box { background: #f7fafc; padding: 25px; border-radius: 15px; border-left: 4px solid #667eea; margin-bottom: 30px; }
        .summary-box h3 { color: #2d3748; margin-bottom: 15px; }
        
        .keywords-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .keywords-box { background: #f7fafc; padding: 20px; border-radius: 12px; }
        .keyword-tag { display: inline-block; padding: 6px 12px; background: white; border: 2px solid #e2e8f0; border-radius: 20px; font-size: 13px; margin: 5px; }
        .keyword-tag.positive { border-color: #48bb78; color: #22543d; background: #c6f6d5; }
        .keyword-tag.negative { border-color: #f56565; color: #742a2a; background: #fed7d7; }
        
        .chart-container { height: 300px; margin-bottom: 30px; }
        
        .loading { text-align: center; padding: 40px; color: #667eea; display: none; }
        .loading i { font-size: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        
        @media (max-width: 768px) { .keywords-section { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span>🤖</span> AI Review Analyzer</h1>
            <p>Powered by Llama 3 & Groq</p>
        </div>

        <div class="analyzer-card">
            <div class="product-selector">
                <label><i class="fas fa-box"></i> Select Product</label>
                <select id="productSelect">
                    <option value="">Choose a product...</option>
                    <?php
                    $products = $pdo->query("
                        SELECT p.id, p.name, COUNT(r.id) as review_count
                        FROM products p
                        LEFT JOIN reviews r ON p.id = r.product_id AND r.approved = 1
                        GROUP BY p.id
                        HAVING review_count > 0
                        ORDER BY p.name
                    ")->fetchAll();
                    foreach ($products as $p) {
                        $selected = ($product_id == $p['id']) ? 'selected' : '';
                        echo "<option value='{$p['id']}' {$selected}>{$p['name']} ({$p['review_count']} reviews)</option>";
                    }
                    ?>
                </select>
                <button class="analyze-btn" onclick="analyzeReviews()">
                    <i class="fas fa-brain"></i> Analyze Reviews
                </button>
            </div>

            <div id="loadingState" class="loading">
                <i class="fas fa-circle-notch"></i>
                <p class="mt-3">Reading reviews & analyzing sentiment...</p>
            </div>

            <div id="results" class="results"></div>
        </div>
        
        <div style="text-align: center;">
            <a href="index.php" style="color: white; text-decoration: none;">&larr; Back to Store</a>
        </div>
    </div>

    <script>
        let trendChart = null;

        <?php if ($product_id > 0): ?>
        window.addEventListener('load', analyzeReviews);
        <?php endif; ?>

        function analyzeReviews() {
            const productId = document.getElementById('productSelect').value;
            if (!productId) return alert('Please select a product');

            const loading = document.getElementById('loadingState');
            const results = document.getElementById('results');
            
            loading.style.display = 'block';
            results.style.display = 'none';

            const formData = new FormData();
            formData.append('action', 'analyze_reviews');
            formData.append('product_id', productId);

            fetch('review-analyzer.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    displayResults(data);
                } else {
                    alert(data.message || 'Error analyzing reviews');
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                alert('Connection error');
                console.error(err);
            });
        }

        function displayResults(data) {
            const stars = '⭐'.repeat(Math.round(data.average_rating));
            
            let html = `
                <div class="rating-summary">
                    <div class="big-rating">${data.average_rating}/5</div>
                    <div class="stars">${stars}</div>
                    <div class="sentiment-badge">${data.sentiment}</div>
                </div>

                <div class="summary-box">
                    <h3>💬 AI Summary</h3>
                    <p>${data.summary}</p>
                </div>
                
                <div class="keywords-section">
                    <div class="keywords-box">
                        <h4>👍 Pros</h4>
                        ${data.pros.map(k => `<span class="keyword-tag positive">${k}</span>`).join('')}
                    </div>
                    <div class="keywords-box">
                        <h4>👎 Cons</h4>
                        ${data.cons.map(k => `<span class="keyword-tag negative">${k}</span>`).join('')}
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            `;

            const results = document.getElementById('results');
            results.innerHTML = html;
            results.style.display = 'block';
            
            // Render Chart
            const ctx = document.getElementById('trendChart').getContext('2d');
            if (trendChart) trendChart.destroy();
            
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.chart_labels,
                    datasets: [{
                        label: 'Average Rating Trend',
                        data: data.chart_values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { min: 1, max: 5 }
                    }
                }
            });
        }
    </script>
</body>
</html>
