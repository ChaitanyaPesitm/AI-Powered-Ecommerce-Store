<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/functions.php';
requireLogin();

$user_id = $_SESSION['user']['id'];
$action = $_GET['action'] ?? '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Add to wishlist
if ($action === 'add' && $product_id > 0) {
    $st = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $st->execute([$user_id, $product_id]);
    header("Location: wishlist.php");
    exit;
}

// Remove from wishlist
if ($action === 'remove' && $product_id > 0) {
    $st = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $st->execute([$user_id, $product_id]);
    header("Location: wishlist.php");
    exit;
}

// Fetch wishlist items
$st = $pdo->prepare("
    SELECT w.*, p.name, p.price, p.image, p.id AS product_id
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    WHERE w.user_id = ?
");
$st->execute([$user_id]);
$items = $st->fetchAll();
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="wishlist-page">
  <!-- Page Header -->
  <div class="page-header py-4 mb-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h1 class="display-6 fw-bold mb-2">
            <i class="fas fa-heart me-3 text-danger"></i>My Wishlist
          </h1>
          <p class="text-muted mb-0">Your favorite products in one place</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <span class="wishlist-count"><?= count($items) ?> Items</span>
        </div>
      </div>
    </div>
  </div>

  <div class="container mb-5">
    <?php if (count($items) > 0): ?>
      <div class="row g-4">
        <?php foreach($items as $index => $item): ?>
          <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
            <div class="wishlist-card">
              <!-- Remove Button -->
              <a href="wishlist.php?action=remove&id=<?= $item['product_id'] ?>" 
                 class="remove-btn"
                 onclick="return confirm('Remove this product from wishlist?')">
                <i class="fas fa-times"></i>
              </a>

              <!-- Product Image -->
              <div class="wishlist-image">
                <a href="<?= base_url('public/product.php?id='.$item['product_id']) ?>">
                  <img src="<?= $item['image'] 
                                ? base_url('assets/uploads/'.$item['image']) 
                                : 'https://via.placeholder.com/400x300?text=No+Image' ?>" 
                       alt="<?= htmlspecialchars($item['name']) ?>">
                </a>
              </div>

              <!-- Product Info -->
              <div class="wishlist-body">
                <a href="<?= base_url('public/product.php?id='.$item['product_id']) ?>" class="wishlist-title">
                  <?= htmlspecialchars($item['name']) ?>
                </a>
                <div class="wishlist-price">₹<?= number_format($item['price'], 2) ?></div>

                <!-- Actions -->
                <div class="wishlist-actions">
                  <form action="cart_add.php" method="POST" class="w-100">
                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary w-100">
                      <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Bulk Actions -->
      <div class="bulk-actions mt-5" data-aos="fade-up">
        <a href="<?= base_url('public/products.php') ?>" class="btn btn-outline-primary btn-lg">
          <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
        </a>
      </div>
    <?php else: ?>
      <div class="empty-wishlist-state" data-aos="fade-up">
        <div class="empty-wishlist-icon mb-4">
          <i class="fas fa-heart-broken"></i>
        </div>
        <h3 class="fw-bold mb-3">Your Wishlist is Empty</h3>
        <p class="text-muted mb-4">Save your favorite items here for later</p>
        <a href="<?= base_url('public/products.php') ?>" class="btn btn-primary btn-lg px-5">
          <i class="fas fa-shopping-bag me-2"></i> Start Shopping
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<style>
/* Page Header */
.page-header {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-bottom: 3px solid var(--danger);
}

.wishlist-count {
  background: var(--danger);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: var(--radius-full);
  font-weight: 700;
  font-size: 1.1rem;
  display: inline-block;
}

/* Empty Wishlist State */
.empty-wishlist-state {
  text-align: center;
  padding: 5rem 2rem;
  background: white;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
}

.empty-wishlist-icon i {
  font-size: 6rem;
  color: var(--danger);
  animation: pulse 2s ease-in-out infinite;
}

/* Wishlist Card */
.wishlist-card {
  background: white;
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow);
  transition: all 0.3s ease;
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.wishlist-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-xl);
}

/* Remove Button */
.remove-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 35px;
  height: 35px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--danger);
  box-shadow: var(--shadow-md);
  z-index: 10;
  transition: all 0.3s ease;
  text-decoration: none;
}

.remove-btn:hover {
  background: var(--danger);
  color: white;
  transform: rotate(90deg) scale(1.1);
}

/* Wishlist Image */
.wishlist-image {
  position: relative;
  overflow: hidden;
  background: var(--gray-100);
  height: 250px;
}

.wishlist-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 1rem;
  transition: transform 0.5s ease;
}

.wishlist-card:hover .wishlist-image img {
  transform: scale(1.1);
}

/* Wishlist Body */
.wishlist-body {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.wishlist-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  text-decoration: none;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 3rem;
  margin-bottom: 0.75rem;
  transition: color 0.3s ease;
}

.wishlist-title:hover {
  color: var(--primary);
}

.wishlist-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 1rem;
}

.wishlist-actions {
  margin-top: auto;
}

.wishlist-actions .btn {
  font-weight: 600;
  padding: 0.75rem;
  transition: all 0.3s ease;
}

.wishlist-actions .btn:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

/* Bulk Actions */
.bulk-actions {
  text-align: center;
  padding: 2rem;
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
}

/* Responsive */
@media (max-width: 768px) {
  .wishlist-image {
    height: 200px;
  }
  
  .wishlist-count {
    font-size: 0.95rem;
    padding: 0.4rem 1rem;
  }
}
</style>

<!-- ✅ Animation Init -->
<script>
  AOS.init({
    duration: 800,
    once: true,
    easing: 'ease-out-cubic'
  });
</script>
