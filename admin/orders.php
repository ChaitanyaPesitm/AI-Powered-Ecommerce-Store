<?php 
ob_start();
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'], $_POST['status'])) {
  $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$_POST['status'], (int)$_POST['id']]);
  header('Location: orders.php');
  exit;
}

require_once __DIR__ . '/_admin-header.php';
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>

<div class="admin-header" data-aos="fade-down">
  <h1 class="page-title">
    <i class="fas fa-shopping-cart"></i>
    Orders
  </h1>
</div>

<div class="admin-content" data-aos="fade-up" data-aos-delay="100">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer Name</th>
          <th>Email</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><strong>#<?= $o['id'] ?></strong></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td><?= htmlspecialchars($o['customer_email']) ?></td>
          <td><strong>₹<?= number_format($o['total'],2) ?></strong></td>
          <td>
            <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; 
              <?php 
                if ($o['status'] === 'completed') echo 'background: #c6f6d5; color: #22543d;';
                elseif ($o['status'] === 'processing') echo 'background: #bee3f8; color: #2c5282;';
                elseif ($o['status'] === 'shipped') echo 'background: #feebc8; color: #7c2d12;';
                elseif ($o['status'] === 'cancelled') echo 'background: #fed7d7; color: #742a2a;';
                else echo 'background: #e2e8f0; color: #2d3748;';
              ?>">
              <?= ucfirst($o['status']) ?>
            </span>
          </td>
          <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
          <td>
            <form method="post" style="display: flex; gap: 10px; align-items: center;">
              <input type="hidden" name="id" value="<?= $o['id'] ?>">
              <select name="status" class="form-select" style="width: 160px; padding: 8px 12px; font-size: 13px;">
                <?php foreach (['placed','processing','shipped','out for delivery','delivered','completed','cancelled','returned'] as $s): ?>
                  <option <?= $o['status']===$s?'selected':'' ?> value="<?= $s ?>"><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-save" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-check"></i> Update
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_admin-footer.php'; ?>
