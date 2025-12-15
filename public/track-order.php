<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/functions.php';
requireLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order details for the logged-in user
$st = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$st->execute([$order_id, $_SESSION['user']['id']]);
$order = $st->fetch();

include __DIR__ . '/../partials/header.php';

// Handle invalid order
if (!$order): ?>
  <div class="container mt-5 text-center">
    <h2 class="text-danger mb-3">❌ Order not found</h2>
    <p>The order you’re trying to view doesn’t exist or doesn’t belong to your account.</p>
    <a href="orders.php" class="btn btn-secondary mt-3">← Back to My Orders</a>
  </div>
  <?php include __DIR__ . '/../partials/footer.php'; exit; endif;

// Fetch ordered items
$itemsQuery = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE order_id = ?
");
$itemsQuery->execute([$order_id]);
$items = $itemsQuery->fetchAll();

// Simulate order progress based on status
$status = strtolower($order['status'] ?? 'pending');
$progress = match ($status) {
  'pending' => 25,
  'shipped' => 50,
  'out for delivery' => 75,
  'delivered' => 100,
  'completed' => 100,
  'cancelled' => 0,
  default => 10,
};
?>

<div class="container mt-5 fade-in">
  <div class="card shadow-sm p-4 mx-auto" style="max-width: 800px;">
    <h2 class="fw-bold text-primary mb-3 text-center">
      🚚 Track Order #<?= htmlspecialchars($order['id']) ?>
    </h2>

    <div class="text-center mb-4">
      <p class="mb-1">Placed On: <strong><?= htmlspecialchars(date('d M Y, h:i A', strtotime($order['created_at']))) ?></strong></p>
      <p class="mb-1">Total Amount: <strong>₹<?= number_format($order['total'], 2) ?></strong></p>
      <p>Status: 
        <span class="badge 
          <?= match($status) {
            'pending' => 'bg-warning text-dark',
            'shipped' => 'bg-info text-dark',
            'out for delivery' => 'bg-primary',
            'delivered' => 'bg-success',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary'
          }; ?>">
          <?= ucfirst($status) ?>
        </span>
      </p>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container mb-5">
      <?php if ($status === 'cancelled'): ?>
        <div class="alert alert-danger text-center mb-3">
          <i class="fas fa-times-circle"></i> <strong>Order Cancelled</strong>
          <p class="mb-0 small">This order has been cancelled and will not be processed.</p>
        </div>
      <?php else: ?>
        <div class="progress" style="height: 12px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated 
            <?= $progress < 100 ? 'bg-primary' : 'bg-success' ?>" 
            role="progressbar" style="width: <?= $progress ?>%">
          </div>
        </div>
        <div class="d-flex justify-content-between mt-2 text-muted small fw-semibold">
          <span>Ordered</span>
          <span>Shipped</span>
          <span>Out for Delivery</span>
          <span>Delivered</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Order Items -->
    <h4 class="fw-bold mb-3">🛍️ Items in this Order</h4>
    <?php if ($items): ?>
      <ul class="list-group mb-4">
        <?php foreach($items as $item): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= htmlspecialchars($item['name']) ?> (x<?= (int)$item['qty'] ?>)</span>
            <strong>₹<?= number_format($item['price'] * $item['qty'], 2) ?></strong>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">No items found for this order.</p>
    <?php endif; ?>

    <div class="d-flex gap-3 justify-content-center mt-4">
      <a href="orders.php" class="btn btn-outline-secondary">
        ← Back to My Orders
      </a>

      <?php if (in_array($status, ['pending', 'processing'])): ?>
        <button onclick="cancelOrder(<?= $order_id ?>)" class="btn btn-danger">
          <i class="fas fa-times-circle me-2"></i> Cancel Order
        </button>
      <?php endif; ?>
      
      <?php if (in_array($status, ['delivered', 'completed'])): ?>
        <a href="request-return.php?id=<?= $order_id ?>&type=return" class="btn btn-warning text-white">
          <i class="fas fa-undo me-2"></i> Return Order
        </a>
        <a href="request-return.php?id=<?= $order_id ?>&type=exchange" class="btn btn-info text-white">
          <i class="fas fa-exchange-alt me-2"></i> Exchange Order
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
async function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    try {
        const response = await fetch('api/cancel_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<!-- ✅ Inline CSS -->
<style>
  body {
    background: #f8f9fa;
    font-family: 'Poppins', sans-serif;
  }
  .card {
    border-radius: 12px;
    background: #fff;
  }
  .progress-container {
    position: relative;
  }
  .progress {
    border-radius: 6px;
    overflow: hidden;
  }
  .fade-in {
    animation: fadeIn 0.6s ease-in-out;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<!-- ✅ AOS Animation Init -->
<script>
  AOS.init({
    duration: 800,
    once: true,
    easing: 'ease-out-cubic'
  });
</script>
