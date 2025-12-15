<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/functions.php';
requireLogin();

$user_id = $_SESSION['user']['id'];
$orders = getUserOrdersWithThumbnail($user_id); // ✅ use new helper

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-5 fade-in">
  <h2 class="fw-bold text-primary mb-4 text-center">📦 My Orders</h2>

  <?php if (count($orders) > 0): ?>
    <div class="table-responsive" data-aos="fade-up">
      <table class="table table-hover align-middle shadow-sm">
        <thead class="table-primary">
          <tr>
            <th>Product</th>
            <th>Order ID</th>
            <th>Total</th>
            <th>Status</th>
            <th>Placed On</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr class="order-row">
              <td>
                <img src="<?= $o['product_image'] 
                    ? base_url('assets/uploads/' . $o['product_image'])
                    : 'https://via.placeholder.com/60x60?text=No+Image' ?>"
                    width="60" height="60" class="rounded shadow-sm me-2" alt="Product">
              </td>

              <td class="fw-semibold">#<?= htmlspecialchars($o['id']) ?></td>
              <td class="text-success fw-bold">₹<?= number_format($o['total'], 2) ?></td>

              <td>
                <?php 
                  $status = ucfirst(htmlspecialchars($o['status'] ?? 'Pending'));
                  $badgeClass = match(strtolower($status)) {
                    'pending' => 'bg-warning text-dark',
                    'processing' => 'bg-info text-dark',
                    'shipped' => 'bg-info text-dark',
                    'completed', 'delivered' => 'bg-success',
                    'cancelled' => 'bg-danger',
                    'returned' => 'bg-info',
                    default => 'bg-secondary'
                  };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
              </td>

              <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($o['created_at']))) ?></td>

              <td>
                <div class="d-flex gap-2">
                  <a href="track-order.php?id=<?= htmlspecialchars($o['id']) ?>" 
                     class="btn btn-dark btn-sm">
                    <i class="fas fa-truck"></i> Track
                  </a>

                  <?php /* Cancel button removed as per request */ ?>

                    <?php /* Return button removed as per request */ ?>
                  <?php if (strtolower($o['status']) === 'returned'): ?>
                    <span class="badge bg-info">
                      <i class="fas fa-check-circle"></i> Returned
                    </span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="text-center py-5" data-aos="fade-up">
      <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" alt="No Orders" width="120" class="mb-3">
      <h5 class="text-muted">You have no orders yet.</h5>
      <a href="products.php" class="btn btn-primary mt-3">
        <i class="fas fa-shopping-bag"></i> Start Shopping
      </a>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- ✅ Custom Styling -->
<style>
  body {
    background: var(--theme-bg-secondary);
    font-family: 'Poppins', sans-serif;
    color: var(--theme-text-primary);
    transition: background 0.3s ease, color 0.3s ease;
  }

  .table {
    border-radius: 12px;
    overflow: hidden;
    background: var(--theme-bg-card);
    color: var(--theme-text-primary);
  }

  .table thead {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    color: #fff;
  }

  .table tbody tr {
    background: var(--theme-bg-card);
    color: var(--theme-text-primary);
    border-color: var(--theme-border);
  }

  .table tbody tr:hover {
    background: rgba(13,110,253,0.1);
    transition: all 0.3s ease;
  }

  .order-row td img {
    object-fit: cover;
  }

  .btn {
    border-radius: 8px;
  }

  .fade-in {
    animation: fadeIn 0.7s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
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
