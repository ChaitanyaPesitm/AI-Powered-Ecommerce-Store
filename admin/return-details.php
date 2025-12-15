<?php
// admin/return-details.php - Return Details for Modal
// admin/return-details.php - Return Details for Modal

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    echo '<div class="alert alert-danger">';
    echo '<i class="fas fa-exclamation-triangle"></i> Unauthorized access. Please log in as admin.';
    echo '</div>';
    exit;
}

$return_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($return_id === 0) {
    echo '<div class="alert alert-warning">Invalid return ID.</div>';
    exit;
}

try {
    // Check if returns table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'returns'");
    if ($tableCheck->rowCount() === 0) {
        echo '<div class="alert alert-info">';
        echo '<i class="fas fa-info-circle"></i> No returns table found. The returns system has not been initialized yet.';
        echo '</div>';
        exit;
    }

    // Fetch return details
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            o.total as order_total,
            o.status as order_status,
            o.created_at as order_date,
            u.name as customer_name,
            u.email as customer_email
        FROM returns r
        JOIN orders o ON o.id = r.order_id
        JOIN users u ON u.id = r.user_id
        WHERE r.id = ?
    ");
    $stmt->execute([$return_id]);
    $return = $stmt->fetch();

    if (!$return) {
        echo '<p class="text-danger">Return request not found.</p>';
        exit;
    }

    // Fetch order items
    $itemsStmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$return['order_id']]);
    $items = $itemsStmt->fetchAll();

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">';
    echo '<i class="fas fa-exclamation-triangle"></i> <strong>Error loading return details.</strong><br>';
    echo '<small>Error: ' . htmlspecialchars($e->getMessage()) . '</small>';
    echo '</div>';
    exit;
}
?>

<div class="return-details">
    <!-- Customer Information -->
    <div class="detail-section">
        <h6 class="section-title"><i class="fas fa-user-circle"></i> Customer Information</h6>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><i class="fas fa-user"></i> Name</span>
                <span class="info-value"><?= htmlspecialchars($return['customer_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                <span class="info-value"><?= htmlspecialchars($return['customer_email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-id-badge"></i> User ID</span>
                <span class="info-value">#<?= htmlspecialchars($return['user_id']) ?></span>
            </div>
        </div>
    </div>

    <!-- Return Information -->
    <div class="detail-section">
        <h6 class="section-title"><i class="fas fa-undo-alt"></i> Return Information</h6>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><i class="fas fa-hashtag"></i> Return ID</span>
                <span class="info-value">#<?= htmlspecialchars($return['id']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-shopping-bag"></i> Order ID</span>
                <span class="info-value">#<?= htmlspecialchars($return['order_id']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-tag"></i> Type</span>
                <span class="info-value">
                    <span class="badge <?= $return['type'] === 'exchange' ? 'bg-info' : 'bg-warning text-dark' ?>">
                        <?= ucfirst(htmlspecialchars($return['type'])) ?>
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-question-circle"></i> Reason</span>
                <span class="info-value"><span class="badge bg-info"><?= htmlspecialchars($return['reason']) ?></span></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-money-bill-wave"></i> Refund Amount</span>
                <span class="info-value refund-amount">₹<?= number_format($return['refund_amount'], 2) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-info-circle"></i> Status</span>
                <span class="info-value">
                    <?php
                        $statusClass = match(strtolower($return['status'])) {
                            'pending' => 'bg-warning text-dark',
                            'approved' => 'bg-info',
                            'refunded' => 'bg-success',
                            'rejected' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($return['status'])) ?></span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Requested On</span>
                <span class="info-value"><?= date('d M Y, h:i A', strtotime($return['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Customer Comments -->
    <?php if (!empty($return['description'])): ?>
        <div class="detail-section">
            <h6 class="section-title"><i class="fas fa-comment-dots"></i> Customer Comments</h6>
            <div class="comment-box">
                <i class="fas fa-quote-left quote-icon"></i>
                <p class="comment-text"><?= nl2br(htmlspecialchars($return['description'])) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Order Items -->
    <div class="detail-section">
        <h6 class="section-title"><i class="fas fa-box-open"></i> Order Items</h6>
        <div class="table-responsive">
            <table class="items-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Product</th>
                        <th><i class="fas fa-sort-numeric-up"></i> Quantity</th>
                        <th><i class="fas fa-rupee-sign"></i> Price</th>
                        <th><i class="fas fa-calculator"></i> Total</th>
                    </tr>
                </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['qty']) ?></td>
                        <td>₹<?= number_format($item['price'], 2) ?></td>
                        <td>₹<?= number_format($item['price'] * $item['qty'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3"><strong><i class="fas fa-receipt"></i> Order Total:</strong></td>
                        <td><strong class="total-amount">₹<?= number_format($return['order_total'], 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Order Information -->
    <div class="detail-section mb-0">
        <h6 class="section-title"><i class="fas fa-clipboard-list"></i> Order Information</h6>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label"><i class="fas fa-calendar-check"></i> Order Date</span>
                <span class="info-value"><?= date('d M Y, h:i A', strtotime($return['order_date'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-flag"></i> Order Status</span>
                <span class="info-value"><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($return['order_status'])) ?></span></span>
            </div>
        </div>
    </div>

    <!-- Action Buttons (Only for Pending) -->
    <?php if ($return['status'] === 'pending'): ?>
    <div class="detail-section mb-0">
        <h6 class="section-title"><i class="fas fa-cogs"></i> Actions</h6>
        <div class="d-flex gap-3">
            <button onclick="processReturn(<?= $return['id'] ?>, 'approve')" class="btn btn-success flex-grow-1">
                <i class="fas fa-check-circle me-2"></i> Approve Request
            </button>
            <button onclick="processReturn(<?= $return['id'] ?>, 'reject')" class="btn btn-danger flex-grow-1">
                <i class="fas fa-times-circle me-2"></i> Reject Request
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>



<style>
    .return-details {
        padding: 0;
        font-family: 'Poppins', sans-serif;
        width: 100%;
    }

    .detail-section {
        margin-bottom: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #667eea;
        width: 100%;
    }

    .detail-section.mb-0 {
        margin-bottom: 0;
    }

    .section-title {
        color: #667eea;
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Info Grid Layout */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
        width: 100%;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 12px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        border-color: #667eea;
    }

    .info-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-label i {
        color: #667eea;
        font-size: 14px;
    }

    .info-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
    }

    .refund-amount {
        color: #10b981;
        font-size: 18px;
        font-weight: 700;
    }

    /* Comment Box */
    .comment-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .quote-icon {
        position: absolute;
        top: 15px;
        left: 15px;
        font-size: 24px;
        color: #667eea;
        opacity: 0.2;
    }

    .comment-text {
        margin: 0;
        padding-left: 30px;
        font-size: 14px;
        line-height: 1.6;
        color: #495057;
        font-style: italic;
    }

    /* Items Table */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .items-table {
        width: 100%;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        margin: 0;
    }

    .items-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .items-table th {
        padding: 12px;
        font-weight: 600;
        font-size: 13px;
        text-align: left;
        border: none;
    }

    .items-table th i {
        margin-right: 5px;
    }

    .items-table tbody tr {
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s ease;
    }

    .items-table tbody tr:hover {
        background: #f8f9fa;
    }

    .items-table tbody td {
        padding: 12px;
        font-size: 14px;
        color: #495057;
    }

    .items-table tfoot {
        background: #f8f9fa;
        border-top: 2px solid #667eea;
    }

    .items-table tfoot td {
        padding: 15px 12px;
        font-size: 15px;
    }

    .total-amount {
        color: #10b981;
        font-size: 18px;
    }

    /* Badges */
    .return-details .badge {
        padding: 6px 12px;
        font-weight: 600;
        font-size: 12px;
        border-radius: 6px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .detail-section {
            padding: 15px;
        }
    }
</style>
