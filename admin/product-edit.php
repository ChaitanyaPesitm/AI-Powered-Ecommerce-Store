<?php 
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    redirect('admin/products.php');
    exit;
}

$id = (int)$_GET['id'];
$p = getProduct($id); 

if (!$p) {
    redirect('admin/products.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? ''); 
  $cat = (int)($_POST['category_id'] ?? 0);
  $price = (float)($_POST['price'] ?? 0); 
  $stock = (int)($_POST['stock'] ?? 0);
  $desc = trim($_POST['description'] ?? ''); 
  $specs = trim($_POST['specifications'] ?? '');
  $img = $p['image'];
  $model_glb = $p['model_glb'] ?? null;

  // handle image upload
  if (!empty($_FILES['image']['name'])) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $img = uniqid('p_') . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $img);
  }

  // handle model upload
  if (!empty($_FILES['model_glb']['name'])) {
    $ext = pathinfo($_FILES['model_glb']['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) === 'glb') {
        $model_glb = uniqid('m_') . '.' . $ext;
        move_uploaded_file($_FILES['model_glb']['tmp_name'], __DIR__ . '/../assets/uploads/' . $model_glb);
    }
  }

  if (!$name || $cat <= 0) $errors[] = "Name & Category are required.";
  if ($price <= 0) $errors[] = "Price must be greater than 0.";

  if (!$errors) {
    $st = $pdo->prepare("UPDATE products 
                         SET category_id=?, name=?, description=?, specifications=?, price=?, stock=?, image=?, model_glb=?
                         WHERE id=?");
    $st->execute([$cat, $name, $desc, $specs, $price, $stock, $img, $model_glb, $id]);
    redirect('admin/products.php');
    exit;
  }
}

$cats = getCategories(); 
require_once __DIR__ . '/_admin-header.php';
?>

<div class="admin-header" data-aos="fade-down">
  <h1 class="page-title">
    <i class="fas fa-edit"></i>
    Edit Product #<?= $p['id'] ?>
  </h1>
  <div class="header-actions">
    <a href="<?= base_url('admin/products.php') ?>" class="btn-header btn-secondary">
      <i class="fas fa-arrow-left"></i> Back to Products
    </a>
  </div>
</div>

<div class="admin-content" data-aos="fade-up" data-aos-delay="100">
  <!-- Error Messages -->
  <?php foreach ($errors as $e): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-triangle"></i>
      <?= htmlspecialchars($e) ?>
    </div>
  <?php endforeach; ?>

  <form method="post" enctype="multipart/form-data">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
      <!-- Left Column -->
      <div>
        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-tag"></i> Product Name *
          </label>
          <input type="text" name="name" class="form-input" placeholder="Enter product name" required value="<?= htmlspecialchars($p['name']) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-list"></i> Category *
          </label>
          <select name="category_id" class="form-select" required>
            <?php foreach ($cats as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $p['category_id'] == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-rupee-sign"></i> Price *
          </label>
          <input type="number" name="price" class="form-input" placeholder="0.00" step="0.01" min="0" required value="<?= $p['price'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-boxes"></i> Stock Quantity
          </label>
          <input type="number" name="stock" class="form-input" placeholder="0" min="0" value="<?= $p['stock'] ?>">
        </div>
      </div>

      <!-- Right Column -->
      <div>
        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-align-left"></i> Description
          </label>
          <textarea name="description" class="form-textarea" placeholder="Enter product description" rows="4"><?= htmlspecialchars($p['description']) ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-info-circle"></i> Specifications
          </label>
          <textarea name="specifications" class="form-textarea" placeholder="Enter product specifications (e.g., Color: Red, Size: M)" rows="4"><?= htmlspecialchars($p['specifications'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-image"></i> Product Image
          </label>
          <div style="position: relative;">
            <input type="file" name="image" accept="image/*" class="form-input" id="imageInput" style="padding: 10px;">
            <div id="imagePreview" style="margin-top: 15px;">
              <?php if ($p['image']): ?>
                <img id="previewImg" src="<?= base_url('assets/uploads/' . $p['image']) ?>" style="max-width: 200px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
              <?php else: ?>
                <img id="previewImg" src="" style="max-width: 200px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: none;">
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="form-group">
          <label class="form-label">
            <i class="fas fa-cube"></i> 3D Model (.glb)
          </label>
          <input type="file" name="model_glb" accept=".glb" class="form-input" style="padding: 10px;">
          <small class="text-muted">Upload a .glb file to replace existing model</small>
          <?php if (!empty($p['model_glb'])): ?>
            <div class="mt-2 text-success">
                <i class="fas fa-check-circle"></i> Current model: <?= htmlspecialchars($p['model_glb']) ?>
            </div>
          <?php endif; ?>
      </div>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 12px; margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
      <button type="submit" class="btn btn-save">
        <i class="fas fa-save"></i> Update Product
      </button>
      <a href="<?= base_url('admin/products.php') ?>" class="btn" style="background: #e2e8f0; color: #4a5568;">
        <i class="fas fa-times"></i> Cancel
      </a>
    </div>
  </form>
</div>

<script>
  // Image preview functionality
  document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.getElementById('previewImg');
        img.src = e.target.result;
        img.style.display = 'block';
      }
      reader.readAsDataURL(file);
    }
  });
</script>

<?php require_once __DIR__ . '/_admin-footer.php'; ?>
