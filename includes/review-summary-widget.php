<?php
/**
 * AI Review Summary Widget
 * Displays AI-generated review summary on product pages
 * 
 * Usage: 
 * $widget_product_id = $product['id'];
 * include __DIR__ . '/../includes/review-summary-widget.php';
 */

$widget_product_id = $widget_product_id ?? 0;

if ($widget_product_id > 0) {
    // Get review summary data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            COALESCE(AVG(rating), 0) as avg_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews
        WHERE product_id = ? AND approved = 1
    ");
    $stmt->execute([$widget_product_id]);
    $review_stats = $stmt->fetch();
    
    $avg_rating = round($review_stats['avg_rating'], 1);
    $total_reviews = $review_stats['total_reviews'];
    
    if ($total_reviews > 0):
?>

<div class="ai-review-summary-widget">
    <div class="widget-header">
        <div class="ai-badge">
            <i class="fas fa-robot"></i>
            <span>AI Analysis</span>
        </div>
        <h3>Customer Review Summary</h3>
    </div>
    
    <div class="rating-overview">
        <div class="big-rating">
            <div class="rating-number"><?= $avg_rating ?></div>
            <div class="rating-stars">
                <?php
                $full_stars = floor($avg_rating);
                $half_star = ($avg_rating - $full_stars) >= 0.5;
                
                for ($i = 0; $i < $full_stars; $i++) {
                    echo '<i class="fas fa-star"></i>';
                }
                if ($half_star) {
                    echo '<i class="fas fa-star-half-alt"></i>';
                }
                for ($i = ceil($avg_rating); $i < 5; $i++) {
                    echo '<i class="far fa-star"></i>';
                }
                ?>
            </div>
            <div class="rating-text"><?= $total_reviews ?> reviews</div>
        </div>
        
        <div class="rating-bars">
            <?php
            $ratings = [5, 4, 3, 2, 1];
            foreach ($ratings as $r) {
                $count = $review_stats[['five_star', 'four_star', 'three_star', 'two_star', 'one_star'][$r-1]];
                $percentage = ($total_reviews > 0) ? ($count / $total_reviews) * 100 : 0;
            ?>
            <div class="bar-row">
                <span class="bar-label"><?= $r ?> <i class="fas fa-star"></i></span>
                <div class="bar-container">
                    <div class="bar-fill" style="width: <?= $percentage ?>%"></div>
                </div>
                <span class="bar-count"><?= $count ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <div class="ai-summary-box" id="aiSummary<?= $widget_product_id ?>">
        <div class="summary-loading">
            <i class="fas fa-spinner fa-spin"></i> Analyzing reviews...
        </div>
    </div>
    
    <a href="review-analyzer.php?id=<?= $widget_product_id ?>" class="view-full-analysis">
        <i class="fas fa-chart-bar"></i>
        View Full AI Analysis
    </a>
</div>

<style>
.ai-review-summary-widget {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 20px;
    padding: 25px;
    margin: 30px 0;
    border: 2px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.widget-header {
    margin-bottom: 20px;
}

.ai-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
}

.ai-badge i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.widget-header h3 {
    color: #2d3748;
    font-size: 22px;
    font-weight: 700;
    margin: 0;
}

.rating-overview {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 30px;
    margin-bottom: 25px;
    padding: 20px;
    background: white;
    border-radius: 15px;
}

.big-rating {
    text-align: center;
}

.rating-number {
    font-size: 48px;
    font-weight: 700;
    color: #2d3748;
    line-height: 1;
    margin-bottom: 10px;
}

.rating-stars {
    color: #f59e0b;
    font-size: 20px;
    margin-bottom: 8px;
}

.rating-text {
    color: #718096;
    font-size: 14px;
}

.rating-bars {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bar-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.bar-label {
    min-width: 50px;
    font-size: 13px;
    color: #4a5568;
    font-weight: 600;
}

.bar-label i {
    color: #f59e0b;
    font-size: 11px;
}

.bar-container {
    flex: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.5s ease;
}

.bar-count {
    min-width: 30px;
    text-align: right;
    font-size: 13px;
    color: #718096;
}

.ai-summary-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #667eea;
    margin-bottom: 20px;
    min-height: 80px;
}

.summary-loading {
    text-align: center;
    color: #667eea;
    padding: 20px;
}

.summary-loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.summary-content {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.summary-rating {
    font-size: 18px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 10px;
}

.summary-rating i {
    color: #f59e0b;
}

.summary-text {
    color: #4a5568;
    line-height: 1.7;
    font-size: 15px;
}

.view-full-analysis {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.view-full-analysis:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
    .rating-overview {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<script>
(function() {
    const productId = <?= $widget_product_id ?>;
    const summaryBox = document.getElementById('aiSummary' + productId);
    
    // Fetch AI analysis
    const formData = new FormData();
    formData.append('action', 'analyze_reviews');
    formData.append('product_id', productId);
    
    fetch('review-analyzer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            summaryBox.innerHTML = `
                <div class="summary-content">
                    <div class="summary-rating">
                        ⭐ ${data.average_rating}/5 • ${data.sentiment}
                    </div>
                    <div class="summary-text">
                        💬 ${data.summary}
                    </div>
                </div>
            `;
        } else {
            summaryBox.innerHTML = `
                <div class="summary-text">
                    No reviews available for AI analysis yet.
                </div>
            `;
        }
    })
    .catch(error => {
        summaryBox.innerHTML = `
            <div class="summary-text">
                Unable to load AI summary.
            </div>
        `;
    });
})();
</script>

<?php
    endif; // if total_reviews > 0
} // if widget_product_id > 0
?>
