<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../partials/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
// Fix: Check if value is not empty string before casting to int
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$categories = getCategories();
$products = getProducts($q, $cat, 50, $min_price, $max_price, $sort);
?>

<!-- Main Section -->
<div class="products-page">
  <!-- Page Header -->
  <div class="page-header py-5 mb-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h1 class="display-4 fw-bold mb-3 slide-in-left">Discover Amazing Products</h1>
          <p class="lead text-muted slide-in-left" style="animation-delay: 0.1s;">
            Browse through our curated collection of quality products
          </p>
        </div>
        <div class="col-lg-4 text-lg-end slide-in-right">
          <a class="btn btn-primary btn-lg shadow" href="<?= base_url('public/suggestions.php') ?>">
            <i class="fas fa-robot me-2"></i> AI Assistant
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <!-- Search + Filter Bar -->
    <div class="filter-section mb-5 fade-in">
      <form method="get" class="card shadow-sm border-0 p-4">
        <div class="row g-3">
          <!-- Search -->
          <div class="col-lg-4">
            <label class="form-label fw-semibold mb-2">Search</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
              <input class="form-control border-start-0 ps-0" type="text" name="q" 
                     placeholder="Search products..." value="<?= htmlspecialchars($q) ?>">
            </div>
          </div>
          
          <!-- Category -->
          <div class="col-lg-4">
            <label class="form-label fw-semibold mb-2">Category</label>
            <select name="cat" class="form-select">
              <option value="0">All Categories</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <!-- Sort -->
          <div class="col-lg-4">
            <label class="form-label fw-semibold mb-2">Sort By</label>
            <select name="sort" class="form-select">
              <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
              <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
          </div>
          
          <!-- Apply Button -->
          <div class="col-12 text-end mt-3">
            <button class="btn btn-primary px-4" type="submit">
              <i class="fas fa-filter me-2"></i> Apply Filters
            </button>
            <a href="<?= base_url('public/products.php') ?>" class="btn btn-outline-secondary ms-2">
              Reset
            </a>
          </div>
        </div>
      </form>
    </div>

    <!-- Results Count -->
    <?php if (!empty($products)): ?>
      <div class="results-info mb-4 fade-in">
        <p class="text-muted">
          <i class="fas fa-check-circle text-success me-2"></i>
          Found <strong><?= count($products) ?></strong> products
          <?php if ($q): ?>
            matching "<strong><?= htmlspecialchars($q) ?></strong>"
          <?php endif; ?>
        </p>
      </div>
    <?php endif; ?>

    <!-- Product Grid -->
    <div class="row g-4 mb-5">
      <?php if (empty($products)): ?>
        <div class="col-12">
          <div class="empty-state text-center py-5">
            <div class="empty-icon mb-4">
              <i class="fas fa-search fa-5x text-muted"></i>
            </div>
            <h3 class="fw-bold mb-3">No Products Found</h3>
            <p class="text-muted mb-4">Try adjusting your search or filters</p>
            <a href="<?= base_url('public/products.php') ?>" class="btn btn-primary">
              <i class="fas fa-redo me-2"></i> Clear Filters
            </a>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($products as $index => $p): ?>
        <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?= ($index % 8) * 50 ?>">
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
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<style>
/* Page Header */
.page-header {
  background: var(--theme-bg-card);
  border-bottom: 3px solid var(--primary);
  transition: background 0.3s ease;
}

/* Filter Section */
.filter-section .card {
  border-radius: var(--radius-lg);
  background: var(--theme-bg-card);
  transition: background 0.3s ease;
}

.filter-section .form-label {
  color: var(--text-primary);
  font-size: 0.9rem;
}

/* Results Info */
.results-info {
  padding: 1rem;
  background: var(--theme-bg-card);
  border-radius: var(--radius);
  border-left: 4px solid var(--success);
  transition: background 0.3s ease;
}

/* Empty State */
.empty-state {
  background: var(--theme-bg-card);
  border-radius: var(--radius-lg);
  padding: 4rem 2rem;
  transition: background 0.3s ease;
}

.empty-icon {
  animation: pulse 2s ease-in-out infinite;
}

/* Product Card Layout */
.product-card {
  display: flex;
  flex-direction: column;
}

.product-card .card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Product Title */
.product-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--theme-text-primary);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 3rem;
  line-height: 1.5rem;
  transition: color 0.3s ease;
}

.product-price {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.product-rating {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: var(--theme-text-secondary);
  font-weight: 600;
  transition: color 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header h1 {
    font-size: 2rem;
  }
  
  .filter-section .card {
    padding: 1.5rem !important;
  }
}
</style>

<script>
// Initialize AOS
AOS.init({
  duration: 800,
  once: true,
  easing: 'ease-out-cubic',
  offset: 50
});

// Add smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});
</script>
