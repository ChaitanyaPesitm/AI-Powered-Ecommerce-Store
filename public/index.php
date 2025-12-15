<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../config/functions.php';

// Fetch latest products
$products = getProducts('', 0, 8);
?>

<!-- 📸 Stories Feed -->
<?php require_once __DIR__ . '/../partials/stories.php'; ?>

<!-- ✨ Enhanced Hero Section -->
<section class="hero-section text-center text-white position-relative overflow-hidden">
  <div class="hero-background-animation"></div>
  <div class="container hero-content py-5">
    <div class="row align-items-center">
      <div class="col-lg-12">
        <span class="badge bg-light text-primary mb-3 px-4 py-2 fs-6 slide-in-left" style="animation-delay: 0.1s;">
          <i class="fas fa-sparkles"></i> AI-Powered Shopping
        </span>
        <h1 class="display-3 fw-bold mb-4 slide-in-left" style="animation-delay: 0.2s;">
          Welcome to <span class="text-warning">The Seventh Com</span>
        </h1>
        <p class="lead mb-4 fs-4 slide-in-left" style="animation-delay: 0.3s; max-width: 700px; margin: 0 auto;">
          Discover amazing products with intelligent recommendations tailored just for you
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap slide-in-left" style="animation-delay: 0.4s;">
          <a href="<?= base_url('public/products.php') ?>" class="btn btn-warning btn-lg px-5 py-3 shadow-lg">
            <i class="fas fa-shopping-bag me-2"></i> Shop Now
          </a>
          <a href="<?= base_url('public/suggestions.php') ?>" class="btn btn-outline-light btn-lg px-5 py-3">
            <i class="fas fa-robot me-2"></i> AI Assistant
          </a>
        </div>
        
        <!-- Feature Pills -->
        <div class="d-flex gap-4 justify-content-center mt-5 flex-wrap slide-in-left" style="animation-delay: 0.5s;">
          <div class="feature-pill">
            <i class="fas fa-shipping-fast me-2"></i> Fast Delivery
          </div>
          <div class="feature-pill">
            <i class="fas fa-shield-alt me-2"></i> Secured Infromation
          </div>
          <div class="feature-pill">
            <i class="fas fa-headset me-2"></i> 24/7 Support
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Floating Elements -->
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>
  <div class="floating-shape shape-3"></div>
</section>

<!-- ✨ Latest Products Section -->
<div class="container my-5">
  <div class="section-header text-center mb-5 fade-in">
    <span class="section-badge">Featured Collection</span>
    <h2 class="display-5 fw-bold mt-3 mb-3">Latest Products</h2>
    <p class="text-muted fs-5">Handpicked products just for you</p>
  </div>

  <div class="row g-4">
    <?php if (empty($products)): ?>
      <div class="col-12 text-center py-5">
        <div class="empty-state">
          <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
          <h5 class="text-muted">No products available yet.</h5>
          <p class="text-secondary">Check back soon for amazing deals!</p>
        </div>
      </div>
    <?php endif; ?>

    <?php foreach ($products as $index => $p): ?>
      <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
        <div class="product-card h-100">
          <!-- Product Image -->
          <div class="product-image-container">
            <img src="<?= $p['image'] 
                          ? base_url('assets/uploads/'.$p['image']) 
                          : 'https://via.placeholder.com/400x300?text=No+Image' ?>" 
                 alt="<?= htmlspecialchars($p['name']) ?>"
                 loading="lazy">
          </div>

          <!-- Card Body -->
          <div class="card-body d-flex flex-column p-3">
            <h5 class="product-title mb-2"><?= htmlspecialchars($p['name']) ?></h5>
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="product-price">₹<?= number_format($p['price'], 2) ?></span>
              <div class="product-rating">
                <i class="fas fa-star text-warning"></i>
                <span class="ms-1">4.5</span>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-auto">
              <div class="d-flex gap-2 mb-2">
                <a href="<?= base_url('public/product.php?id='.$p['id']) ?>" 
                   class="btn btn-outline-primary btn-sm flex-grow-1" 
                   title="View Details">
                  <i class="fas fa-eye me-1"></i> View Details
                </a>
                <?php if (isLoggedIn()): ?>
                  <a href="<?= base_url('public/wishlist.php?action=add&id='.$p['id']) ?>" 
                     class="btn btn-outline-danger btn-sm" 
                     title="Add to Wishlist">
                    <i class="fas fa-heart"></i>
                  </a>
                <?php else: ?>
                  <a href="<?= base_url('public/login.php') ?>" 
                     class="btn btn-outline-secondary btn-sm" 
                     title="Add to Wishlist">
                    <i class="fas fa-heart"></i>
                  </a>
                <?php endif; ?>
              </div>
              <a href="<?= base_url('public/product.php?id='.$p['id']) ?>" 
                 class="btn btn-primary btn-sm w-100 btn-add-to-cart"
                 data-id="<?= $p['id'] ?>">
                <i class="fas fa-shopping-cart me-1"></i> Add to Cart
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-5 fade-in">
    <a href="<?= base_url('public/products.php') ?>" class="btn btn-primary btn-lg px-5 py-3 shadow">
      View All Products <i class="fas fa-arrow-right ms-2"></i>
    </a>
  </div>
</div>

<!-- ✨ Features Section -->
<section class="features-section py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="0">
        <div class="feature-box text-center p-4">
          <div class="feature-icon mb-3">
            <i class="fas fa-truck fa-3x text-primary"></i>
          </div>
          <h5 class="fw-bold">Free Shipping</h5>
          <p class="text-muted mb-0">On orders over ₹500</p>
        </div>
      </div>
      <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
        <div class="feature-box text-center p-4">
          <div class="feature-icon mb-3">
            <i class="fas fa-undo fa-3x text-success"></i>
          </div>
          <h5 class="fw-bold">Easy Returns</h5>
          <p class="text-muted mb-0">30-day return policy</p>
        </div>
      </div>
      <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
        <div class="feature-box text-center p-4">
          <div class="feature-icon mb-3">
            <i class="fas fa-lock fa-3x text-warning"></i>
          </div>
          <h5 class="fw-bold">Secured Information</h5>
          <p class="text-muted mb-0">100% Secured</p>
        </div>
      </div>
      <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
        <div class="feature-box text-center p-4">
          <div class="feature-icon mb-3">
            <i class="fas fa-headset fa-3x text-info"></i>
          </div>
          <h5 class="fw-bold">24/7 Support</h5>
          <p class="text-muted mb-0">Always here to help</p>
        </div>
      </div>
    </div>
  </div>
</section>


<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- Styles moved to assets/css/style.css -->

<script>
// Initialize AOS
AOS.init({
  duration: 800,
  once: true,
  easing: 'ease-out-cubic',
  offset: 100
});

// Add loading animation
document.addEventListener('DOMContentLoaded', function() {
  // Fade in elements
  const fadeElements = document.querySelectorAll('.fade-in');
  fadeElements.forEach((el, index) => {
    setTimeout(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, index * 100);
  });
});
</script>
