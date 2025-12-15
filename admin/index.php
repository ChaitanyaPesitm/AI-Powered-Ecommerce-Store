<?php 
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/_admin-header.php';
global $pdo;
$tot_products = $pdo->query("SELECT COUNT(*) c FROM products")->fetch()['c'];
$tot_orders   = $pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$tot_users    = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch()['c'];
$latest = $pdo->query("SELECT id, customer_name, total, status, created_at FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="admin-header" data-aos="fade-down">
  <h1 class="page-title">
    <i class="fas fa-chart-line"></i>
    Dashboard
  </h1>
  <div class="header-actions">
    <a href="<?= base_url('admin/product-add.php') ?>" class="btn-header btn-primary">
      <i class="fas fa-plus"></i> Add Product
    </a>
  </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
  <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
    <div class="stat-header">
      <div class="stat-label">Total Products</div>
      <div class="stat-icon">
        <i class="fas fa-box"></i>
      </div>
    </div>
    <div class="stat-value"><?= $tot_products ?></div>
  </div>

  <div class="stat-card green" data-aos="fade-up" data-aos-delay="200">
    <div class="stat-header">
      <div class="stat-label">Total Orders</div>
      <div class="stat-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
    </div>
    <div class="stat-value"><?= $tot_orders ?></div>
  </div>

  <div class="stat-card orange" data-aos="fade-up" data-aos-delay="300">
    <div class="stat-header">
      <div class="stat-label">Total Customers</div>
      <div class="stat-icon">
        <i class="fas fa-users"></i>
      </div>
    </div>
    <div class="stat-value"><?= $tot_users ?></div>
  </div>
</div>

<!-- Recent Orders -->
<div class="admin-content" data-aos="fade-up" data-aos-delay="400">
  <h3 style="margin-bottom: 20px; color: #2d3748; font-size: 20px;">
    <i class="fas fa-clock" style="color: #667eea;"></i> Recent Orders
  </h3>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer Name</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($latest as $o): ?>
        <tr>
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td><strong>₹<?= number_format($o['total'],2) ?></strong></td>
          <td>
            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; 
              <?php 
                if ($o['status'] === 'completed') echo 'background: #c6f6d5; color: #22543d;';
                elseif ($o['status'] === 'processing') echo 'background: #bee3f8; color: #2c5282;';
                elseif ($o['status'] === 'shipped') echo 'background: #feebc8; color: #7c2d12;';
                else echo 'background: #e2e8f0; color: #2d3748;';
              ?>">
              <?= ucfirst($o['status']) ?>
            </span>
          </td>
          <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_admin-footer.php'; ?>
