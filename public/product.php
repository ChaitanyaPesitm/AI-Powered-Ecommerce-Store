<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../partials/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProduct($id);

if (!$product) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Product not found.</div></div>";
    require_once __DIR__ . '/../partials/footer.php';
    exit;
}

// Fetch reviews data
$avgData = getProductAverageRating($product['id']);
$avgRating = $avgData['avg_rating'] ?? 0;
$reviewCount = $avgData['count_rating'] ?? 0;
$reviews = getProductReviews($product['id']);
?>

<!-- 🛍️ Product Details Section -->
<div class="product-detail-page">
  <div class="container mt-5">
    <div class="row g-5">
      
      <!-- Left Column: Image + Buttons + Badges -->
      <div class="col-lg-6" data-aos="fade-right">
        <!-- Product Image -->
        <!-- Product Image & 3D Viewer -->
        <div class="product-gallery mb-4">
          <div class="main-image-container position-relative">
            
            <!-- 2D Image View -->
            <div id="view-2d" class="view-mode active">
                <div class="image-badge">
                  <i class="fas fa-star"></i> Featured
                </div>
                <img src="<?= $product['image'] 
                              ? base_url('assets/uploads/'.$product['image']) 
                              : 'https://via.placeholder.com/600x500?text=No+Image' ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="main-product-image img-fluid rounded-3 shadow-lg"
                     id="mainImage">
                <div class="image-zoom-hint">
                  <i class="fas fa-search-plus"></i> Hover to zoom
                </div>
            </div>

            <!-- 3D Model View -->
            <div id="view-3d" class="view-mode" style="display: none;">
                <?php if (!empty($product['model_glb'])): ?>
                <?php 
                    $modelUrl = $product['model_glb'];
                    if (!preg_match('/^https?:\/\//', $modelUrl)) {
                        $modelUrl = base_url('assets/uploads/' . $modelUrl);
                    }
                ?>
                <model-viewer 
                    src="<?= $modelUrl ?>"
                    poster="<?= base_url('assets/uploads/' . $product['image']) ?>"
                    alt="A 3D model of <?= htmlspecialchars($product['name']) ?>"
                    ar
                    ar-modes="webxr scene-viewer quick-look" 
                    camera-controls
                    auto-rotate
                    shadow-intensity="1"
                    style="width: 100%; height: 500px; background-color: #f0f0f0; border-radius: 1rem;"
                >
                    <button slot="ar-button" class="btn btn-primary btn-sm position-absolute bottom-0 start-50 translate-middle-x mb-3">
                        <i class="fas fa-cube me-2"></i> View in your space
                    </button>
                </model-viewer>
                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded-3" style="min-height: 500px;">
                    <div class="text-center text-muted">
                        <i class="fas fa-cube fa-3x mb-3"></i>
                        <p>No 3D model available for this product.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Toggle Controls (Only show if model exists) -->
            <?php if (!empty($product['model_glb'])): ?>
            <div class="view-controls position-absolute top-0 end-0 m-3 d-flex gap-2">
                <button class="btn btn-light btn-sm shadow-sm active" onclick="switchView('2d')" id="btn-2d" title="2D View">
                    <i class="fas fa-image"></i>
                </button>
                <button class="btn btn-light btn-sm shadow-sm" onclick="switchView('3d')" id="btn-3d" title="3D View">
                    <i class="fas fa-cube"></i>
                </button>
            </div>
            <?php endif; ?>

          </div>
        </div>

        <!-- Action Buttons -->
        <div class="product-action-buttons mb-3">
          <div class="row g-3">
            <div class="col-6">
              <?php if (isLoggedIn()): ?>
                <a href="<?= base_url('public/wishlist.php?action=add&id='.$product['id']) ?>" 
                   class="btn btn-dark btn-lg w-100">
                  <i class="fas fa-heart me-2"></i> Add to Wishlist
                </a>
              <?php else: ?>
                <a href="<?= base_url('public/login.php') ?>" 
                   class="btn btn-dark btn-lg w-100">
                  <i class="fas fa-heart me-2"></i> Add to Wishlist
                </a>
              <?php endif; ?>
            </div>
            <div class="col-6">
              <a href="<?= base_url('public/suggestions.php') ?>" 
                 class="btn btn-dark btn-lg w-100">
                <i class="fas fa-robot me-2"></i> AI Suggestions
              </a>
            </div>
          </div>
        </div>

        <!-- Feature Badges -->
        <div class="product-feature-badges">
          <div class="row g-3">
            <div class="col-6">
              <div class="feature-badge">
                <div class="feature-badge-icon bg-primary">
                  <i class="fas fa-truck"></i>
                </div>
                <span class="feature-badge-text">Free Shipping</span>
              </div>
            </div>
            <div class="col-6">
              <div class="feature-badge">
                <div class="feature-badge-icon bg-success">
                  <i class="fas fa-undo"></i>
                </div>
                <span class="feature-badge-text">Easy Returns</span>
              </div>
            </div>
            <div class="col-6">
              <div class="feature-badge">
                <div class="feature-badge-icon bg-warning">
                  <i class="fas fa-shield-alt"></i>
                </div>
                <span class="feature-badge-text">Secure Payment</span>
              </div>
            </div>
            <div class="col-6">
              <div class="feature-badge">
                <div class="feature-badge-icon bg-info">
                  <i class="fas fa-headset"></i>
                </div>
                <span class="feature-badge-text">24/7 Support</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Product Info + Specs + Quantity -->
      <div class="col-lg-6" data-aos="fade-left">
        <div class="product-info">
          <!-- Breadcrumb -->
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="<?= base_url('public/index.php') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= base_url('public/products.php') ?>">Products</a></li>
              <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
          </nav>

          <h1 class="product-name mb-3"><?= htmlspecialchars($product['name']) ?></h1>
          
          <!-- Rating -->
          <div class="product-rating-section mb-3">
            <div class="stars">
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star-half-alt text-warning"></i>
              <span class="ms-2 text-muted">(<?= $reviewCount ?> reviews)</span>
            </div>
          </div>

          <div class="price-section mb-4">
            <h2 class="product-price mb-0">₹<?= number_format($product['price'], 2) ?></h2>
            <p class="text-success mb-0"><i class="fas fa-check-circle"></i> In Stock</p>
          </div>

          <div class="product-description mb-4">
            <h5 class="fw-bold mb-2">Description</h5>
            <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
          </div>

          <?php if (!empty($product['specifications'])): ?>
            <div class="specifications-section mb-4">
              <h5 class="fw-bold mb-3">Specifications</h5>
              <div class="specs-grid">
                <?php foreach (explode("\n", $product['specifications']) as $line): ?>
                  <?php if (trim($line)): ?>
                    <div class="spec-item">
                      <i class="fas fa-check-circle text-primary me-2"></i>
                      <span><?= htmlspecialchars($line) ?></span>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Quantity Selector -->
          <div class="quantity-section mb-4">
            <h6 class="fw-bold mb-3">Quantity</h6>
            <form action="<?= base_url('public/cart_add.php') ?>" method="POST" id="addToCartForm">
              <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
              <div class="quantity-input-wrapper">
                <button type="button" class="qty-decrease" onclick="decreaseQty()">
                  <i class="fas fa-minus"></i>
                </button>
                <input type="number" name="qty" id="qtyInput" value="1" min="1" max="10" 
                       class="qty-display" readonly>
                <button type="button" class="qty-increase" onclick="increaseQty()">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<hr class="my-5">

<!-- ⭐ Reviews Section -->
<div class="container reviews-section fade-in" data-aos="fade-up">
  <h3 class="fw-bold mb-3 text-primary">Customer Reviews</h3>

  <?php
  // AI Review Summary Widget
  if ($reviewCount > 0) {
      $widget_product_id = $product['id'];
      include __DIR__ . '/../includes/review-summary-widget.php';
  }
  ?>

  <div class="mb-3">
    <strong>Average Rating:</strong>
    <?php if ($reviewCount > 0): ?>
      <span class="text-warning fw-semibold"><?= htmlspecialchars($avgRating) ?>/5</span> 
      (<?= (int)$reviewCount ?> reviews)
    <?php else: ?>
      <span class="text-muted">No reviews yet.</span>
    <?php endif; ?>
  </div>

  <!-- Review form -->
  <?php if (isLoggedIn()): 
    $user_id = $_SESSION['user']['id'];
    $canReview = canUserReview($user_id, $product['id']);
    $alreadyReviewed = hasUserReviewedProduct($user_id, $product['id']);
    if ($canReview && !$alreadyReviewed):
      if (session_status() === PHP_SESSION_NONE) session_start();
      if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
  ?>
    <div class="card border-0 shadow-sm p-3 mb-4">
      <h5 class="fw-semibold mb-2">Write a Review</h5>
      <form method="post" action="<?= base_url('public/review_submit.php') ?>">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-2">
          <label class="form-label fw-semibold">Rating</label>
          <select name="rating" class="form-select w-50" required>
            <option value="5">⭐️⭐️⭐️⭐️⭐️ Excellent</option>
            <option value="4">⭐️⭐️⭐️⭐️ Good</option>
            <option value="3">⭐️⭐️⭐️ Okay</option>
            <option value="2">⭐️⭐️ Poor</option>
            <option value="1">⭐ Very Poor</option>
          </select>
        </div>

        <div class="mb-2">
          <input type="text" name="title" class="form-control" placeholder="Title (optional)">
        </div>
        <div class="mb-3">
          <textarea name="comment" class="form-control" rows="4" placeholder="Write your experience..."></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Submit Review</button>
      </form>
    </div>
  <?php elseif (!$canReview): ?>
    <p class="text-muted">Only customers who purchased this product can write a review.</p>
  <?php else: ?>
    <p class="text-muted">You already reviewed this product. Thank you!</p>
  <?php endif; else: ?>
    <p><a href="<?= base_url('public/login.php') ?>">Log in</a> to write a review.</p>
  <?php endif; ?>

  <!-- List of Reviews -->
  <div class="review-list">
    <?php if (empty($reviews)): ?>
      <p class="text-muted">No reviews yet. Be the first to write one!</p>
    <?php else: ?>
      <?php foreach ($reviews as $r): ?>
        <div class="card mb-3 border-0 shadow-sm p-3" data-aos="fade-up">
          <div class="fw-semibold text-dark"><?= htmlspecialchars($r['user_name']) ?> 
            <span class="text-warning"><?= str_repeat('⭐', (int)$r['rating']) ?></span>
          </div>
          <?php if (!empty($r['title'])): ?>
            <div class="fw-bold mt-1"><?= htmlspecialchars($r['title']) ?></div>
          <?php endif; ?>
          <div class="mt-2 text-muted"><?= nl2br(htmlspecialchars($r['comment'])) ?></div>
          <div class="small text-secondary mt-1"><?= htmlspecialchars($r['created_at']) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<style>
/* Product Gallery */
.product-gallery {
  position: relative;
}

.main-image-container {
  position: relative;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: var(--gray-100);
}

.main-product-image {
  width: 100%;
  height: auto;
  transition: transform 0.5s ease;
  cursor: zoom-in;
}

.main-product-image:hover {
  transform: scale(1.05);
}

.image-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: linear-gradient(135deg, var(--warning), #ff6b6b);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-full);
  font-size: 0.875rem;
  font-weight: 600;
  z-index: 10;
  box-shadow: var(--shadow-lg);
  animation: pulse 2s ease-in-out infinite;
}

.image-zoom-hint {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: var(--radius);
  font-size: 0.75rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.main-image-container:hover .image-zoom-hint {
  opacity: 1;
}

/* Product Info */
.product-name {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
}

.product-rating-section .stars {
  font-size: 1.25rem;
}

.price-section {
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: var(--radius-lg);
  border-left: 4px solid var(--primary);
}

.product-price {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.product-description {
  padding: 1.5rem;
  background: white;
  border-radius: var(--radius-lg);
  border: 2px solid var(--gray-200);
}

/* Specifications */
.specifications-section {
  padding: 1.5rem;
  background: white;
  border-radius: var(--radius-lg);
  border: 2px solid var(--gray-200);
}

.specs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
}

.spec-item {
  padding: 0.75rem;
  background: var(--gray-50);
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.spec-item:hover {
  background: var(--primary);
  color: white;
  transform: translateX(5px);
}

.spec-item:hover i {
  color: white !important;
}

/* Quantity Section */
.quantity-section h6 {
  font-size: 1.1rem;
  color: var(--text-primary);
}

.quantity-input-wrapper {
  display: inline-flex;
  align-items: center;
  border: 2px solid var(--gray-300);
  border-radius: var(--radius);
  overflow: hidden;
  background: white;
}

.qty-decrease,
.qty-increase {
  width: 50px;
  height: 50px;
  border: none;
  background: white;
  color: var(--text-primary);
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qty-decrease:hover,
.qty-increase:hover {
  background: var(--gray-100);
  color: var(--primary);
}

.qty-display {
  width: 80px;
  height: 50px;
  border: none;
  border-left: 2px solid var(--gray-300);
  border-right: 2px solid var(--gray-300);
  text-align: center;
  font-size: 1.1rem;
  font-weight: 600;
  background: white;
}

.qty-display:focus {
  outline: none;
}

/* Add to Cart Button */
.add-to-cart-btn {
  background: linear-gradient(135deg, var(--success), #059669);
  border: none;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: var(--shadow-lg);
  transition: all 0.3s ease;
}

.add-to-cart-btn:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-xl);
}

/* Product Action Buttons */
.product-action-buttons .btn-dark {
  background: #1a1a1a;
  border: none;
  font-weight: 600;
  padding: 0.875rem 1.5rem;
  transition: all 0.3s ease;
}

.product-action-buttons .btn-dark:hover {
  background: #000;
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

/* Feature Badges */
.product-feature-badges {
  margin-top: 2rem;
}

.feature-badge {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
  border: 2px solid var(--gray-200);
}

.feature-badge:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary);
}

.feature-badge-icon {
  width: 45px;
  height: 45px;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.feature-badge-icon i {
  font-size: 1.25rem;
  color: white;
}

.feature-badge-text {
  font-weight: 600;
  font-size: 0.95rem;
  color: var(--text-primary);
}

/* Reviews Section */
.reviews-section {
  background: white;
  padding: 2.5rem;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
}

.review-list .card {
  transition: all 0.3s ease;
  border: 2px solid var(--gray-200);
}

.review-list .card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary);
}

/* Responsive */
@media (max-width: 768px) {
  .product-name {
    font-size: 1.5rem;
  }
  
  .product-price {
    font-size: 2rem;
  }
  
  .specs-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
// Initialize AOS
AOS.init({
  duration: 700,
  once: true,
  easing: 'ease-in-out'
});

// Quantity controls
function increaseQty() {
  const input = document.getElementById('qtyInput');
  const max = parseInt(input.max);
  const current = parseInt(input.value);
  if (current < max) {
    input.value = current + 1;
  }
}

function decreaseQty() {
  const input = document.getElementById('qtyInput');
  const min = parseInt(input.min);
  const current = parseInt(input.value);
  if (current > min) {
    input.value = current - 1;
  }
}

// Add to cart animation
document.querySelector('.add-to-cart-btn')?.addEventListener('click', function(e) {
  const btn = this;
  btn.innerHTML = '<i class="fas fa-check me-2"></i> Added!';
  btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
  
  setTimeout(() => {
    btn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i> Add to Cart';
  }, 2000);
});

// 3D/2D View Switcher
function switchView(mode) {
    const view2d = document.getElementById('view-2d');
    const view3d = document.getElementById('view-3d');
    const btn2d = document.getElementById('btn-2d');
    const btn3d = document.getElementById('btn-3d');

    if (mode === '2d') {
        view2d.style.display = 'block';
        view3d.style.display = 'none';
        btn2d.classList.add('active', 'btn-primary');
        btn2d.classList.remove('btn-light');
        btn3d.classList.remove('active', 'btn-primary');
        btn3d.classList.add('btn-light');
    } else {
        view2d.style.display = 'none';
        view3d.style.display = 'block';
        btn3d.classList.add('active', 'btn-primary');
        btn3d.classList.remove('btn-light');
        btn2d.classList.remove('active', 'btn-primary');
        btn2d.classList.add('btn-light');
    }
}

// Initialize buttons
document.addEventListener('DOMContentLoaded', () => {
    const btn2d = document.getElementById('btn-2d');
    if(btn2d) {
        btn2d.classList.add('btn-primary');
        btn2d.classList.remove('btn-light');
    }
});
</script>
