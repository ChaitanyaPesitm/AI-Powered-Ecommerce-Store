<?php 
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/_admin-header.php';
$ps = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC")->fetchAll();
?>

<div class="admin-header" data-aos="fade-down">
  <h1 class="page-title">
    <i class="fas fa-box"></i>
    Products
  </h1>
  <div class="header-actions">
    <a href="<?= base_url('admin/product-add.php') ?>" class="btn-header btn-primary">
      <i class="fas fa-plus"></i> Add Product
    </a>
  </div>
</div>

<div class="admin-content" data-aos="fade-up" data-aos-delay="100">
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ps as $p): ?>
        <tr>
          <td><strong>#<?= $p['id'] ?></strong></td>
          <td>
            <?php if ($p['image']): ?>
              <img src="<?= base_url('assets/uploads/'.$p['image']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <?php else: ?>
              <div style="width: 60px; height: 60px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-image" style="color: #a0aec0;"></i>
              </div>
            <?php endif; ?>
          </td>
          <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
          <td>
            <span style="padding: 6px 12px; background: #edf2f7; border-radius: 8px; font-size: 13px; color: #4a5568;">
              <?= htmlspecialchars($p['category_name']) ?>
            </span>
          </td>
          <td><strong>₹<?= number_format($p['price'],2) ?></strong></td>
          <td>
            <span style="padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; 
              <?= $p['stock'] > 10 ? 'background: #c6f6d5; color: #22543d;' : ($p['stock'] > 0 ? 'background: #feebc8; color: #7c2d12;' : 'background: #fed7d7; color: #742a2a;') ?>">
              <?= (int)$p['stock'] ?>
            </span>
          </td>
          <td>
            <div style="display: flex; gap: 8px;">
              <a href="<?= base_url('admin/product-edit.php?id='.$p['id']) ?>" class="btn btn-edit" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a href="<?= base_url('admin/product-delete.php?id='.$p['id']) ?>" class="btn btn-delete" style="font-size: 13px; padding: 8px 16px;" onclick="return confirm('Are you sure you want to delete this product?')">
                <i class="fas fa-trash"></i> Delete
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_admin-footer.php'; ?>
