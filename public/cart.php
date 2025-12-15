<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../config/functions.php';

// Handle cart updates or clear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $pid => $qty) {
      cart_update($pid, $qty);
    }
  } elseif (isset($_POST['clear'])) {
    cart_clear();
  }
}

$items = cart_items();
$total = cart_total();
?>

<div class="cart-page">
  <!-- Page Header -->
  <div class="page-header py-4 mb-5">
    <div class="container">
      <h1 class="display-5 fw-bold mb-2">
        <i class="fas fa-shopping-cart me-3"></i>Shopping Cart
      </h1>
      <p class="text-muted mb-0">Review your items before checkout</p>
    </div>
  </div>

  <div class="container mb-5">
    <?php if (empty($items)): ?>
      <div class="empty-cart-state" data-aos="fade-up">
        <div class="empty-cart-icon mb-4">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <h3 class="fw-bold mb-3">Your Cart is Empty</h3>
        <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet</p>
        <a href="<?= base_url('public/products.php') ?>" class="btn btn-primary btn-lg px-5">
          <i class="fas fa-shopping-bag me-2"></i> Start Shopping
        </a>
      </div>
    <?php else: ?>

    <div class="row g-4">
      <!-- Cart Items -->
      <div class="col-lg-8">
        <form method="post" id="cartForm">
          <div class="cart-items-container">
            <div class="cart-header mb-3">
              <h5 class="fw-bold mb-0">Cart Items (<?= count($items) ?>)</h5>
            </div>

            <?php foreach ($items as $index => $it): $p = $it['product']; ?>
              <div class="cart-item-card mb-3" data-aos="fade-up" data-aos-delay="<?= $index * 50 ?>">
                <div class="row g-3 align-items-center">
                  <div class="col-md-2 col-3">
                    <div class="cart-item-image">
                      <img src="<?= $p['image'] ? base_url('assets/uploads/'.$p['image']) : 'https://via.placeholder.com/150x150?text=No+Image' ?>" 
                           alt="<?= htmlspecialchars($p['name']) ?>" 
                           class="img-fluid rounded">
                    </div>
                  </div>
                  <div class="col-md-4 col-9">
                    <h6 class="cart-item-name mb-2"><?= htmlspecialchars($p['name']) ?></h6>
                    <p class="text-muted small mb-0">SKU: #<?= $p['id'] ?></p>
                  </div>
                  <div class="col-md-2 col-4">
                    <div class="cart-item-price">
                      <span class="fw-bold">₹<?= number_format($p['price'], 2) ?></span>
                    </div>
                  </div>
                  <div class="col-md-2 col-4">
                    <div class="quantity-control">
                      <button type="button" class="qty-btn" onclick="updateQty(<?= $p['id'] ?>, -1)">
                        <i class="fas fa-minus"></i>
                      </button>
                      <input type="number" name="qty[<?= $p['id'] ?>]" 
                             id="qty_<?= $p['id'] ?>"
                             value="<?= (int)$it['qty'] ?>" 
                             min="0" 
                             class="qty-input"
                             onchange="this.form.submit()">
                      <button type="button" class="qty-btn" onclick="updateQty(<?= $p['id'] ?>, 1)">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-md-2 col-4">
                    <div class="cart-item-total">
                      <span class="fw-bold text-primary">₹<?= number_format($it['line_total'], 2) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <div class="cart-actions mt-4">
              <button type="submit" name="update" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt me-2"></i> Update Cart
              </button>
              <button type="submit" name="clear" class="btn btn-outline-danger" 
                      onclick="return confirm('Are you sure you want to clear your cart?')">
                <i class="fas fa-trash me-2"></i> Clear Cart
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-4">
        <div class="order-summary-card" data-aos="fade-left">
          <h5 class="fw-bold mb-4">Order Summary</h5>
          
          <div class="summary-row">
            <span>Subtotal</span>
            <span class="fw-bold">₹<?= number_format($total, 2) ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span class="text-success fw-bold">FREE</span>
          </div>
          <div class="summary-row">
            <span class="text-muted small">Tax</span>
            <span class="text-muted small">Calculated at checkout</span>
          </div>
          
          <hr class="my-3">
          
          <div class="summary-total">
            <span class="fw-bold">Total</span>
            <span class="total-amount">₹<?= number_format($total, 2) ?></span>
          </div>

          <a href="<?= base_url('public/checkout.php') ?>" class="btn btn-success btn-lg w-100 mt-4 checkout-btn">
            <i class="fas fa-lock me-2"></i> Proceed to Checkout
            <i class="fas fa-arrow-right ms-2"></i>
          </a>

          <div class="security-badges mt-4">
            <div class="badge-item">
              <i class="fas fa-shield-alt text-success"></i>
              <span>Secure Checkout</span>
            </div>
            <div class="badge-item">
              <i class="fas fa-truck text-primary"></i>
              <span>Free Shipping</span>
            </div>
          </div>
        </div>

        <!-- Continue Shopping -->
        <a href="<?= base_url('public/products.php') ?>" class="btn btn-outline-secondary w-100 mt-3">
          <i class="fas fa-arrow-left me-2"></i> Continue Shopping
        </a>
      </div>
    </div>

    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<style>
/* Page Header */
.page-header {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-bottom: 3px solid var(--primary);
}

/* Empty Cart State */
.empty-cart-state {
  text-align: center;
  padding: 5rem 2rem;
  background: white;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
}

.empty-cart-icon i {
  font-size: 6rem;
  color: var(--gray-300);
  animation: bounce 2s ease-in-out infinite;
}

/* Cart Items Container */
.cart-items-container {
  background: white;
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  box-shadow: var(--shadow);
}

.cart-header {
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--gray-200);
}

/* Cart Item Card */
.cart-item-card {
  background: var(--gray-50);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.cart-item-card:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-md);
  transform: translateX(5px);
}

.cart-item-image {
  overflow: hidden;
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
}

.cart-item-image img {
  transition: transform 0.3s ease;
}

.cart-item-card:hover .cart-item-image img {
  transform: scale(1.1);
}

.cart-item-name {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 1.1rem;
}

.cart-item-price {
  font-size: 1.1rem;
  color: var(--text-secondary);
}

/* Quantity Control */
.quantity-control {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: white;
  border-radius: var(--radius);
  padding: 0.25rem;
  box-shadow: var(--shadow-sm);
}

.qty-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: var(--gray-100);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qty-btn:hover {
  background: var(--primary);
  color: white;
  transform: scale(1.1);
}

.qty-input {
  width: 50px;
  border: none;
  text-align: center;
  font-weight: 600;
  background: transparent;
}

.qty-input:focus {
  outline: none;
}

.cart-item-total {
  font-size: 1.25rem;
}

/* Cart Actions */
.cart-actions {
  display: flex;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 2px solid var(--gray-200);
}

/* Order Summary Card */
.order-summary-card {
  background: white;
  border-radius: var(--radius-lg);
  padding: 2rem;
  box-shadow: var(--shadow-lg);
  position: sticky;
  top: 100px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--gray-200);
}

.summary-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.25rem;
}

.total-amount {
  font-size: 2rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.checkout-btn {
  background: linear-gradient(135deg, #10b981, #059669) !important;
  border: none !important;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4) !important;
  transition: all 0.3s ease;
  font-size: 1.1rem !important;
  padding: 1.25rem !important;
  position: relative;
  overflow: hidden;
  animation: pulse-glow 2s ease-in-out infinite;
}

.checkout-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s;
}

.checkout-btn:hover::before {
  left: 100%;
}

.checkout-btn:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 15px 40px rgba(16, 185, 129, 0.5) !important;
  background: linear-gradient(135deg, #059669, #047857) !important;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
  }
  50% {
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.6);
  }
}

/* Security Badges */
.security-badges {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.badge-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--gray-50);
  border-radius: var(--radius);
  font-size: 0.875rem;
  font-weight: 600;
}

.badge-item i {
  font-size: 1.25rem;
}

/* Responsive */
@media (max-width: 768px) {
  .cart-item-card {
    padding: 1rem;
  }
  
  .order-summary-card {
    position: static;
  }
  
  .cart-actions {
    flex-direction: column;
  }
  
  .cart-actions .btn {
    width: 100%;
  }
}
</style>

<script>
// Initialize AOS
AOS.init({
  duration: 800,
  once: true,
  easing: 'ease-out-cubic'
});

// Quantity update function
function updateQty(productId, change) {
  const input = document.getElementById('qty_' + productId);
  if (!input) {
    console.error('Input not found for product:', productId);
    return;
  }
  
  let currentValue = parseInt(input.value) || 0;
  let newValue = currentValue + change;
  
  // Ensure value is at least 0
  if (newValue >= 0) {
    input.value = newValue;
    
    // Find the form and submit it
    const form = input.closest('form');
    if (form) {
      // Add update button name to form data
      const updateBtn = document.createElement('input');
      updateBtn.type = 'hidden';
      updateBtn.name = 'update';
      updateBtn.value = '1';
      form.appendChild(updateBtn);
      
      form.submit();
    } else {
      console.error('Form not found');
    }
  }
}

// Add animation on cart update
document.querySelectorAll('.qty-input').forEach(input => {
  input.addEventListener('change', function() {
    this.closest('.cart-item-card').style.animation = 'pulse 0.5s ease';
  });
});
</script>
