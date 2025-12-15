<?php
/**
 * AI Recommendation Widget
 * Can be included on any product page to show recommendations
 * 
 * Usage: include __DIR__ . '/../includes/recommendation-widget.php';
 */

$widget_product_id = $widget_product_id ?? 0;
?>

<div class="ai-recommendation-widget" id="aiRecommendations">
    <div class="widget-header">
        <div class="widget-icon">🤖</div>
        <div class="widget-title">
            <h3>AI Recommendations</h3>
            <p>Products you might also like</p>
        </div>
    </div>
    
    <div class="recommendations-container" id="recommendationsContainer">
        <div class="loading">
            <i class="fas fa-spinner fa-spin"></i> Finding perfect matches...
        </div>
    </div>
</div>

<style>
.ai-recommendation-widget {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 20px;
    padding: 25px;
    margin: 30px 0;
    border: 2px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.widget-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #cbd5e0;
}

.widget-icon {
    font-size: 40px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.widget-title h3 {
    color: #2d3748;
    font-size: 22px;
    margin: 0 0 5px 0;
    font-weight: 700;
}

.widget-title p {
    color: #718096;
    font-size: 14px;
    margin: 0;
}

.recommendations-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.loading {
    text-align: center;
    padding: 40px;
    color: #667eea;
    font-size: 18px;
    grid-column: 1 / -1;
}

.recommendation-card {
    background: white;
    border-radius: 15px;
    padding: 15px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.recommendation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
    border-color: #667eea;
}

.rec-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 12px;
}

.rec-emoji {
    font-size: 24px;
    margin-bottom: 8px;
}

.rec-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 16px;
    line-height: 1.4;
}

.rec-reason {
    color: #667eea;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.rec-reason i {
    font-size: 12px;
}

.rec-price {
    font-size: 20px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 5px;
}

.rec-rating {
    color: #f59e0b;
    font-size: 14px;
}

.rec-rating i {
    margin-right: 3px;
}

@media (max-width: 768px) {
    .recommendations-container {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .widget-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
(function() {
    const productId = <?= $widget_product_id ?>;
    
    if (productId > 0) {
        fetch(`product-recommendations.php?action=similar&product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('recommendationsContainer');
                
                if (data.success && data.recommendations && data.recommendations.length > 0) {
                    container.innerHTML = '';
                    
                    data.recommendations.forEach(rec => {
                        const card = document.createElement('a');
                        card.href = `product-detail.php?id=${rec.id}`;
                        card.className = 'recommendation-card';
                        
                        card.innerHTML = `
                            <img src="${rec.image}" alt="${rec.name}" class="rec-image" onerror="this.src='../assets/placeholder.jpg'">
                            <div class="rec-emoji">${rec.emoji}</div>
                            <div class="rec-name">${rec.name}</div>
                            <div class="rec-reason">
                                <i class="fas fa-lightbulb"></i>
                                ${rec.reason}
                            </div>
                            <div class="rec-price">₹${rec.price}</div>
                            ${rec.rating > 0 ? `
                                <div class="rec-rating">
                                    <i class="fas fa-star"></i>
                                    ${rec.rating} (${rec.reviews} reviews)
                                </div>
                            ` : ''}
                        `;
                        
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = '<div class="loading">No recommendations available at the moment.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading recommendations:', error);
                document.getElementById('recommendationsContainer').innerHTML = 
                    '<div class="loading">Unable to load recommendations.</div>';
            });
    }
})();
</script>
