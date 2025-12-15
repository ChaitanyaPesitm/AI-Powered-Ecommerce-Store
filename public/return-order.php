<?php
// public/return-order.php - Return/Refund Request Page
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/functions.php';
requireLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details
$st = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$st->execute([$order_id, $_SESSION['user']['id']]);
$order = $st->fetch();

// Check if order exists and is eligible for return
if (!$order) {
    header('Location: orders.php');
    exit;
}

$status = strtolower($order['status']);
if (!in_array($status, ['completed', 'delivered'])) {
    $_SESSION['error'] = 'Only completed or delivered orders can be returned.';
    header('Location: orders.php');
    exit;
}

// Check if return already exists
$existingReturn = false;
try {
    $checkReturn = $pdo->prepare("SELECT * FROM returns WHERE order_id = ?");
    $checkReturn->execute([$order_id]);
    $existingReturn = $checkReturn->fetch();
} catch (PDOException $e) {
    // Table doesn't exist yet, will be created on first submission
}

if ($existingReturn) {
    $_SESSION['error'] = 'A return request already exists for this order.';
    header('Location: orders.php');
    exit;
}

// Fetch order items
$itemsQuery = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE order_id = ?
");
$itemsQuery->execute([$order_id]);
$items = $itemsQuery->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

<div class="container mt-5 mb-5">
  <div class="card shadow-lg mx-auto" style="max-width: 700px;">
    <div class="card-header bg-warning text-dark">
      <h3 class="mb-0"><i class="fas fa-undo"></i> Return Order #<?= htmlspecialchars($order['id']) ?></h3>
    </div>
    
    <div class="card-body p-4">
      <!-- Order Summary -->
      <div class="alert alert-info mb-4">
        <h5 class="mb-2"><i class="fas fa-info-circle"></i> Order Details</h5>
        <p class="mb-1"><strong>Order Date:</strong> <?= htmlspecialchars(date('d M Y, h:i A', strtotime($order['created_at']))) ?></p>
        <p class="mb-1"><strong>Total Amount:</strong> ₹<?= number_format($order['total'], 2) ?></p>
        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success"><?= ucfirst($status) ?></span></p>
      </div>

      <!-- Items in Order -->
      <h5 class="mb-3"><i class="fas fa-box"></i> Items to Return</h5>
      <ul class="list-group mb-4">
        <?php foreach($items as $item): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= htmlspecialchars($item['name']) ?> (x<?= (int)$item['qty'] ?>)</span>
            <strong>₹<?= number_format($item['price'] * $item['qty'], 2) ?></strong>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- Return Form -->
      <form action="process-return.php" method="POST">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="mb-4">
          <label for="reason" class="form-label fw-bold">
            <i class="fas fa-question-circle"></i> Reason for Return *
          </label>
          <select class="form-select" id="reason" name="reason" required>
            <option value="">-- Select a reason --</option>
            <option value="Defective Product">Defective Product</option>
            <option value="Wrong Item Received">Wrong Item Received</option>
            <option value="Product Not as Described">Product Not as Described</option>
            <option value="Quality Issues">Quality Issues</option>
            <option value="Changed Mind">Changed Mind</option>
            <option value="Better Price Available">Better Price Available</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="mb-4">
          <label for="description" class="form-label fw-bold">
            <i class="fas fa-comment"></i> Additional Details
          </label>
          <textarea 
            class="form-control" 
            id="description" 
            name="description" 
            rows="4" 
            placeholder="Please provide more details about the issue with the product..."
          ></textarea>
          <small class="text-muted">Optional - Help us understand the issue better</small>
        </div>

        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i> <strong>Return Policy:</strong>
          <ul class="mb-0 mt-2">
            <li>Returns are accepted within 7 days of delivery</li>
            <li>Product must be unused and in original packaging</li>
            <li>Refund will be processed within 5-7 business days</li>
            <li>Refund amount: ₹<?= number_format($order['total'], 2) ?></li>
          </ul>
        </div>

        <div class="d-flex gap-3 justify-content-between mt-4">
          <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
          </a>
          <button type="submit" class="btn btn-warning btn-lg">
            <i class="fas fa-check"></i> Submit Return Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<style>
  body {
    background: #f8f9fa;
  }
  .card {
    border-radius: 15px;
    border: none;
  }
  .card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
  }
  .form-label {
    color: #495057;
  }
  .form-control:focus, .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
  }
</style>
