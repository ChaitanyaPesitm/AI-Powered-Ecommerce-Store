<?php
error_reporting(E_ALL);
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/razorpay_config.php';

// ✅ Get cart items before loading header
$items = cart_items(); 
if (!$items) redirect('public/cart.php');

$errors = [];
$name = $email = $phone = $address = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'Online';

    // ✅ Validation
    if ($name === '' || $email === '' || $phone === '' || $address === '') {
        $errors[] = "All fields are required.";
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($phone && !preg_match('/^\d{10}$/', $phone)) {
        $errors[] = "Phone must be 10 digits.";
    }

    // ✅ If valid, create order
    // ✅ If valid, create order
    if (!$errors) {
        $order_id = create_order(compact('name','email','phone','address', 'payment_method'), $items);
        cart_clear();
        redirect('public/order-confirmation.php?id='.$order_id);
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="checkout-page">
  <!-- Page Header -->
  <div class="page-header py-4 mb-5">
    <div class="container">
      <h1 class="display-6 fw-bold mb-2">
        <i class="fas fa-shopping-bag me-3"></i>Checkout
      </h1>
      <p class="text-muted mb-0">Complete your order</p>
      <!-- DEBUG LOG -->
      <div id="debug-log" class="alert alert-warning mt-3 d-none" style="white-space: pre-wrap; font-family: monospace;"></div>

    </div>
  </div>

  <div class="container mb-5">
    <!-- Progress Steps -->
    <div class="checkout-steps mb-5" data-aos="fade-down">
      <div class="step completed">
        <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="step-label">Cart</div>
      </div>
      <div class="step-line completed"></div>
      <div class="step active">
        <div class="step-icon"><i class="fas fa-credit-card"></i></div>
        <div class="step-label">Checkout</div>
      </div>
      <div class="step-line"></div>
      <div class="step">
        <div class="step-icon"><i class="fas fa-check-circle"></i></div>
        <div class="step-label">Complete</div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Checkout Form -->
      <div class="col-lg-7" data-aos="fade-right">
        <div class="checkout-form-card">
          <h4 class="fw-bold mb-4">
            <i class="fas fa-user-circle me-2 text-primary"></i>Billing Details
          </h4>

          <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($e) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endforeach; ?>

          <form method="post" id="checkoutForm">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="fas fa-user me-2 text-primary"></i>Full Name
                </label>
                <input type="text" name="name" class="form-control form-control-lg" 
                       value="<?= htmlspecialchars($name) ?>" 
                       placeholder="Enter your full name" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">
                  <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                </label>
                <input type="email" name="email" class="form-control form-control-lg" 
                       value="<?= htmlspecialchars($email) ?>" 
                       placeholder="your@email.com" required>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold">
                  <i class="fas fa-phone me-2 text-primary"></i>Phone Number
                </label>
                <input type="text" name="phone" class="form-control form-control-lg" 
                       maxlength="10" value="<?= htmlspecialchars($phone) ?>" 
                       placeholder="10-digit mobile number" required>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">
                  <i class="fas fa-map-marker-alt me-2 text-primary"></i>Delivery Address
                </label>
                <textarea name="address" class="form-control form-control-lg" rows="4" 
                          placeholder="Enter complete delivery address" required><?= htmlspecialchars($address) ?></textarea>
              </div>

              <!-- Payment Method Selection -->
              <div class="col-12">
                  <label class="form-label fw-semibold">
                      <i class="fas fa-wallet me-2 text-primary"></i>Payment Method
                  </label>
                  <div class="payment-options d-flex gap-3">
                      <div class="form-check custom-option flex-fill p-3 border rounded">
                          <input class="form-check-input" type="radio" name="payment_method" id="pm_online" value="Online" checked>
                          <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="pm_online">
                              <span><i class="fas fa-credit-card me-2 text-success"></i>Online Payment</span>
                              <small class="text-muted">UPI, Cards, NetBanking</small>
                          </label>
                      </div>
                      <div class="form-check custom-option flex-fill p-3 border rounded">
                          <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="COD">
                          <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="pm_cod">
                              <span><i class="fas fa-money-bill-wave me-2 text-warning"></i>Cash on Delivery</span>
                              <small class="text-muted">Pay at doorstep</small>
                          </label>
                      </div>
                  </div>
              </div>

              <div class="col-12 mt-4">
                <button type="submit" class="btn btn-success btn-lg w-100 place-order-btn">
                  <i class="fas fa-lock me-2"></i> Place Order Securely
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-5" data-aos="fade-left">
        <div class="order-summary-card sticky-top" style="top: 100px;">
          <h4 class="fw-bold mb-4">
            <i class="fas fa-receipt me-2 text-primary"></i>Order Summary
          </h4>

          <div class="order-items">
            <?php foreach ($items as $it): $p = $it['product']; ?>
              <div class="order-item">
                <div class="order-item-image">
                  <img src="<?= $p['image'] ? base_url('assets/uploads/'.$p['image']) : 'https://via.placeholder.com/80x80' ?>" 
                       alt="<?= htmlspecialchars($p['name']) ?>">
                </div>
                <div class="order-item-details">
                  <h6 class="mb-1"><?= htmlspecialchars($p['name']) ?></h6>
                  <p class="text-muted small mb-0">Qty: <?= $it['qty'] ?></p>
                </div>
                <div class="order-item-price">
                  ₹<?= number_format($it['line_total'], 2) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <hr class="my-3">

          <div class="order-totals">
            <div class="total-row">
              <span>Subtotal</span>
              <span>₹<?= number_format(cart_total(), 2) ?></span>
            </div>
            <div class="total-row">
              <span>Shipping</span>
              <span class="text-success fw-bold">FREE</span>
            </div>
            <div class="total-row">
              <span class="text-muted small">Tax</span>
              <span class="text-muted small">Included</span>
            </div>
          </div>

          <hr class="my-3">

          <div class="grand-total">
            <span class="fw-bold">Total</span>
            <span class="total-amount">₹<?= number_format(cart_total(), 2) ?></span>
          </div>

          <div class="security-badges mt-4">
            <div class="badge-item">
              <i class="fas fa-shield-alt text-success"></i>
              <span>Secure Checkout</span>
            </div>
            <div class="badge-item">
              <i class="fas fa-truck text-primary"></i>
              <span>Free Delivery</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<style>
/* Page Header */
.page-header {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-bottom: 3px solid var(--primary);
}

/* Checkout Steps */
.checkout-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.step-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--gray-200);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--gray-500);
  transition: all 0.3s ease;
}

.step.completed .step-icon,
.step.active .step-icon {
  background: var(--primary);
  color: white;
  box-shadow: var(--shadow-lg);
}

.step.active .step-icon {
  animation: pulse 2s infinite;
}

.step-label {
  font-weight: 600;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.step.completed .step-label,
.step.active .step-label {
  color: var(--primary);
}

.step-line {
  width: 100px;
  height: 3px;
  background: var(--gray-300);
  margin: 0 1rem;
}

.step-line.completed {
  background: var(--primary);
}

/* Checkout Form Card */
.checkout-form-card {
  background: white;
  border-radius: var(--radius-lg);
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.checkout-form-card .form-control {
  border: 2px solid var(--gray-300);
  border-radius: var(--radius);
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.checkout-form-card .form-control:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.place-order-btn {
  background: linear-gradient(135deg, #10b981, #059669) !important;
  border: none !important;
  font-weight: 900 !important;
  text-transform: uppercase;
  letter-spacing: 2px;
  padding: 1.5rem 2rem !important;
  font-size: 1.25rem !important;
  transition: all 0.3s ease;
  box-shadow: 0 10px 40px rgba(16, 185, 129, 0.5) !important;
  position: relative;
  overflow: hidden;
  animation: pulse-glow 2s ease-in-out infinite;
}

.place-order-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  transition: left 0.5s;
}

.place-order-btn:hover::before {
  left: 100%;
}

.place-order-btn:hover {
  transform: translateY(-5px) scale(1.03);
  box-shadow: 0 15px 50px rgba(16, 185, 129, 0.6) !important;
  background: linear-gradient(135deg, #059669, #047857) !important;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.5);
  }
  50% {
    box-shadow: 0 15px 50px rgba(16, 185, 129, 0.7);
  }
}

/* Order Summary Card */
.order-summary-card {
  background: white;
  border-radius: var(--radius-lg);
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.order-items {
  max-height: 400px;
  overflow-y: auto;
}

.order-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--radius);
  margin-bottom: 0.75rem;
  transition: all 0.3s ease;
}

.order-item:hover {
  background: var(--gray-100);
  transform: translateX(5px);
}

.order-item-image {
  width: 60px;
  height: 60px;
  border-radius: var(--radius);
  overflow: hidden;
  flex-shrink: 0;
}

.order-item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.order-item-details {
  flex: 1;
}

.order-item-details h6 {
  font-size: 0.95rem;
  font-weight: 600;
}

.order-item-price {
  font-weight: 700;
  color: var(--primary);
  font-size: 1.1rem;
}

.order-totals .total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.95rem;
}

.grand-total {
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
  .checkout-steps {
    padding: 1rem;
  }
  
  .step-icon {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }
  
  .step-line {
    width: 50px;
  }
  
  .step-label {
    font-size: 0.75rem;
  }
}

/* ✅ Ensure Razorpay is on top */
.razorpay-container {
    z-index: 99999 !important;
}
</style>

<!-- ✅ Razorpay Checkout.js -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<!-- ✅ Animation -->
<script>
  AOS.init({
    duration: 800,
    once: true,
    easing: 'ease-out-cubic'
  });
</script>

<script>
    // Razorpay Integration
    // Razorpay Integration
    document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        // If COD, allow default form submission
        if (paymentMethod === 'COD') {
            return; 
        }

        // If Online, prevent default and run Razorpay logic
        e.preventDefault();
        
        const btn = document.querySelector('.place-order-btn');
        const originalBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

        const formData = new FormData(this);
        const orderData = Object.fromEntries(formData.entries());
        const amount = <?= cart_total() ?>;

        try {
            // 1. Create Order on Server
            const response = await fetch('api/create-razorpay-order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: amount })
            });

            if (!response.ok) throw new Error('Failed to create order');
            
            const order = await response.json();

            // 2. Initialize Razorpay
            const options = {
                "key": "<?= RAZORPAY_KEY_ID ?>",
                "amount": order.amount,
                "currency": order.currency,
                "name": "Ecommerce Store",
                "description": "Purchase Description",
                "image": "<?= base_url('assets/logo.png') ?>", // Replace with your logo
                "order_id": order.id, 
                "handler": async function (response){
                    // 3. Verify Payment on Server
                    try {
                        const verifyResponse = await fetch('api/verify-razorpay-payment.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature,
                                order_data: orderData
                            })
                        });

                        const verifyResult = await verifyResponse.json();

                        if (verifyResult.success) {
                            window.location.href = 'order-confirmation.php?id=' + verifyResult.order_id;
                        } else {
                            alert('Payment verification failed: ' + verifyResult.error);
                            btn.disabled = false;
                            btn.innerHTML = originalBtnText;
                        }
                    } catch (err) {
                        alert('Server error during verification');
                        console.error(err);
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                },
                "prefill": {
                    "name": orderData.name,
                    "email": orderData.email,
                    "contact": orderData.phone
                },
                "theme": {
                    "color": "#10b981"
                }
            };

            const rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response){
                alert('Payment Failed: ' + response.error.description);
                btn.disabled = false;
                btn.innerHTML = originalBtnText;
            });
            rzp1.open();

        } catch (err) {
            console.error(err);
            alert('Error initiating payment. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        }
    });
</script>
