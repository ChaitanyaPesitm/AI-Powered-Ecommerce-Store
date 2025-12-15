<?php
// Start output buffering to prevent any whitespace issues
ob_start();

require_once __DIR__ . '/../config/functions.php';

// --- Security / Access Control ---
// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- Helper: Update product rating summary ---
if (!function_exists('updateProductRatingSummary')) {
    function updateProductRatingSummary($product_id) {
        global $pdo;
        try {
            $avg = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE product_id = ? AND approved = 1");
            $avg->execute([$product_id]);
            $rating = round((float)$avg->fetchColumn(), 1);

            // Check if average_rating column exists, if not skip this update
            $pdo->prepare("UPDATE products SET average_rating = ? WHERE id = ?")
                ->execute([$rating, $product_id]);
        } catch (PDOException $e) {
            // If column doesn't exist, just skip the update
            // The approve will still work
            error_log("Could not update product rating: " . $e->getMessage());
        }
    }
}

// Handle approve/delete actions safely
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (in_array($action, ['approve', 'delete'], true) && $id > 0) {
    // Clear any output buffer before redirect
    if (ob_get_length()) ob_end_clean();
    
    try {
        if ($action === 'approve') {
            // First, approve the review
            $stmt = $pdo->prepare("UPDATE reviews SET approved = 1 WHERE id = ?");
            $stmt->execute([$id]);

            // Then update product rating summary
            $prod = $pdo->prepare("SELECT product_id FROM reviews WHERE id = ?");
            $prod->execute([$id]);
            $p = $prod->fetch();
            if ($p) {
                updateProductRatingSummary($p['product_id']);
            }

        } elseif ($action === 'delete') {
            // Record product before delete
            $prod = $pdo->prepare("SELECT product_id FROM reviews WHERE id = ?");
            $prod->execute([$id]);
            $p = $prod->fetch();

            // Delete the review
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$id]);
            
            // Update product rating
            if ($p) {
                updateProductRatingSummary($p['product_id']);
            }
        }
    } catch (Exception $e) {
        // Log error but still redirect
        error_log("Review action error: " . $e->getMessage());
    }

    header('Location: reviews.php');
    exit;
}

// Filter
$filter = $_GET['filter'] ?? 'all';
$whereClause = '';
if ($filter === 'pending') {
    $whereClause = 'WHERE r.approved = 0';
} elseif ($filter === 'approved') {
    $whereClause = 'WHERE r.approved = 1';
}

// --- Fetch all reviews with product & user info ---
$st = $pdo->query("
    SELECT r.*, 
           u.name AS user_name, 
           p.name AS product_name 
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    JOIN products p ON p.id = r.product_id
    $whereClause
    ORDER BY r.created_at DESC
");
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// Stats
$total = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM reviews WHERE approved = 0")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM reviews WHERE approved = 1")->fetchColumn();

// --- Include admin header (after all redirects) ---
require_once '_admin-header.php';
?>

<div class="admin-header" data-aos="fade-down">
  <h1 class="page-title">
    <i class="fas fa-star"></i>
    Product Reviews
  </h1>
  <div class="header-actions">
    <div style="display: flex; gap: 10px; align-items: center;">
      <select id="filterSelect" class="form-select" style="width: auto; padding: 10px 16px;" onchange="window.location.href='reviews.php?filter=' + this.value">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Reviews</option>
        <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
      </select>
    </div>
  </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
  <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
    <div class="stat-header">
      <div class="stat-label">Total Reviews</div>
      <div class="stat-icon">
        <i class="fas fa-comments"></i>
      </div>
    </div>
    <div class="stat-value"><?= $total ?></div>
  </div>

  <div class="stat-card orange" data-aos="fade-up" data-aos-delay="200">
    <div class="stat-header">
      <div class="stat-label">Pending Approval</div>
      <div class="stat-icon">
        <i class="fas fa-clock"></i>
      </div>
    </div>
    <div class="stat-value"><?= $pending ?></div>
  </div>

  <div class="stat-card green" data-aos="fade-up" data-aos-delay="300">
    <div class="stat-header">
      <div class="stat-label">Approved</div>
      <div class="stat-icon">
        <i class="fas fa-check-circle"></i>
      </div>
    </div>
    <div class="stat-value"><?= $approved ?></div>
  </div>
</div>

<!-- Reviews List -->
<div class="admin-content" data-aos="fade-up" data-aos-delay="400">
  <?php if (empty($rows)): ?>
    <div style="text-align: center; padding: 60px 20px; color: #718096;">
      <i class="fas fa-inbox" style="font-size: 80px; color: #cbd5e0; margin-bottom: 20px;"></i>
      <p style="font-size: 18px;">No reviews found.</p>
    </div>
  <?php else: ?>
    <div style="display: grid; gap: 20px;">
      <?php foreach ($rows as $r): ?>
        <div class="review-card" data-aos="fade-up" style="background: #f7fafc; border-radius: 16px; padding: 25px; border: 2px solid #e2e8f0; transition: all 0.3s ease;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div style="flex: 1;">
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 18px;">
                  <?= strtoupper(substr($r['user_name'], 0, 1)) ?>
                </div>
                <div>
                  <div style="font-weight: 600; color: #2d3748; font-size: 16px;">
                    <?= htmlspecialchars($r['user_name']) ?>
                  </div>
                  <div style="font-size: 13px; color: #718096;">
                    <?= date('M d, Y', strtotime($r['created_at'])) ?>
                  </div>
                </div>
              </div>
              
              <div style="margin-bottom: 12px;">
                <span style="background: #edf2f7; padding: 6px 12px; border-radius: 8px; font-size: 13px; color: #4a5568; font-weight: 500;">
                  <i class="fas fa-box" style="color: #667eea;"></i> <?= htmlspecialchars($r['product_name']) ?>
                </span>
              </div>

              <div style="margin-bottom: 12px;">
                <?php for($i = 1; $i <= 5; $i++): ?>
                  <i class="fas fa-star" style="color: <?= $i <= $r['rating'] ? '#f6ad55' : '#e2e8f0' ?>; font-size: 18px;"></i>
                <?php endfor; ?>
                <span style="margin-left: 8px; font-weight: 600; color: #2d3748;"><?= $r['rating'] ?>/5</span>
              </div>

              <div style="color: #4a5568; line-height: 1.6; font-size: 15px;">
                <?= nl2br(htmlspecialchars($r['comment'])) ?>
              </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; margin-left: 20px;">
              <?php if ($r['approved']): ?>
                <span style="padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #c6f6d5; color: #22543d; text-align: center; white-space: nowrap;">
                  <i class="fas fa-check-circle"></i> Approved
                </span>
              <?php else: ?>
                <span style="padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #feebc8; color: #7c2d12; text-align: center; white-space: nowrap;">
                  <i class="fas fa-clock"></i> Pending
                </span>
              <?php endif; ?>
            </div>
          </div>

          <div style="display: flex; gap: 10px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
            <?php if (!$r['approved']): ?>
              <a href="reviews.php?action=approve&id=<?= (int)$r['id'] ?>" 
                 class="btn" 
                 style="background: linear-gradient(135deg, #48bb78, #38a169); color: white; font-size: 14px; padding: 10px 20px;">
                <i class="fas fa-check"></i> Approve
              </a>
            <?php endif; ?>
            <a href="reviews.php?action=delete&id=<?= (int)$r['id'] ?>" 
               class="btn btn-delete" 
               style="font-size: 14px; padding: 10px 20px;"
               onclick="return confirm('Are you sure you want to delete this review?')">
              <i class="fas fa-trash"></i> Delete
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<style>
  .review-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e0;
  }
</style>

<?php include '_admin-footer.php'; ?>
