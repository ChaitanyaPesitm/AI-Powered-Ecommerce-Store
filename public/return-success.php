<?php
// public/return-success.php - Return Success Page
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/functions.php';
requireLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch return details
$st = $pdo->prepare("
    SELECT r.*, o.total, o.created_at as order_date
    FROM returns r
    JOIN orders o ON o.id = r.order_id
    WHERE r.order_id = ? AND r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 1
");
$st->execute([$order_id, $_SESSION['user']['id']]);
$return = $st->fetch();

if (!$return) {
    header('Location: orders.php');
    exit;
}

include __DIR__ . '/../partials/header.php';
?>

<div class="container mt-5 mb-5">
  <div class="card shadow-lg mx-auto" style="max-width: 700px;">
    <div class="card-body text-center p-5">
      <!-- Success Icon -->
      <div class="success-icon mb-4">
        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
      </div>

      <h2 class="text-success fw-bold mb-3">Return Request Submitted!</h2>
      <p class="lead mb-4">Your return request has been successfully processed.</p>

      <!-- Return Details -->
      <div class="alert alert-success text-start mb-4">
        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Return Details</h5>
        <div class="row">
          <div class="col-md-6 mb-2">
            <strong>Order ID:</strong> #<?= htmlspecialchars($order_id) ?>
          </div>
          <div class="col-md-6 mb-2">
            <strong>Return ID:</strong> #<?= htmlspecialchars($return['id']) ?>
          </div>
          <div class="col-md-6 mb-2">
            <strong>Reason:</strong> <?= htmlspecialchars($return['reason']) ?>
          </div>
          <div class="col-md-6 mb-2">
            <strong>Status:</strong> 
            <span class="badge bg-success">Refunded</span>
          </div>
          <div class="col-12 mt-2">
            <strong>Refund Amount:</strong> 
            <span class="text-success fs-4">₹<?= number_format($return['refund_amount'], 2) ?></span>
          </div>
        </div>
      </div>

      <!-- Refund Information -->
      <div class="card bg-light mb-4">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-money-bill-wave text-success"></i> Refund Information</h5>
          <p class="card-text text-start">
            <i class="fas fa-check text-success"></i> Your refund of <strong>₹<?= number_format($return['refund_amount'], 2) ?></strong> has been processed.<br>
            <i class="fas fa-check text-success"></i> The amount will be credited to your original payment method.<br>
            <i class="fas fa-check text-success"></i> Please allow 5-7 business days for the refund to reflect in your account.<br>
            <i class="fas fa-check text-success"></i> You will receive an email confirmation shortly.
          </p>
        </div>
      </div>

      <!-- Additional Details -->
      <?php if (!empty($return['description'])): ?>
        <div class="alert alert-info text-start mb-4">
          <strong><i class="fas fa-comment"></i> Your Comments:</strong>
          <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($return['description'])) ?></p>
        </div>
      <?php endif; ?>

      <!-- What's Next -->
      <div class="card border-warning mb-4">
        <div class="card-body text-start">
          <h5 class="card-title text-warning"><i class="fas fa-lightbulb"></i> What's Next?</h5>
          <ul class="mb-0">
            <li>Our team will review your return request</li>
            <li>You may be contacted for additional information</li>
            <li>Track your refund status in "My Orders"</li>
            <li>Contact support if you have any questions</li>
          </ul>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="orders.php" class="btn btn-primary btn-lg">
          <i class="fas fa-box"></i> View My Orders
        </a>
        <a href="<?= base_url('public/index.php') ?>" class="btn btn-outline-primary btn-lg">
          <i class="fas fa-home"></i> Back to Home
        </a>
        <a href="<?= base_url('public/support.php') ?>" class="btn btn-outline-secondary btn-lg">
          <i class="fas fa-headset"></i> Contact Support
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<style>
  body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
  }
  .card {
    border-radius: 20px;
    border: none;
    animation: slideUp 0.5s ease-out;
  }
  .success-icon {
    animation: scaleIn 0.6s ease-out;
  }
  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  @keyframes scaleIn {
    from {
      transform: scale(0);
    }
    to {
      transform: scale(1);
    }
  }
</style>

<script>
  // Confetti effect
  setTimeout(() => {
    if (typeof confetti !== 'undefined') {
      confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
      });
    }
  }, 300);
</script>
