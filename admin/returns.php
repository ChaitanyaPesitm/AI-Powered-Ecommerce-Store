<?php
// admin/returns.php - Admin Returns Management
require_once __DIR__ . '/../config/functions.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/_admin-header.php';

// Fetch all returns with order and user details
try {
    $returnsQuery = $pdo->query("
        SELECT 
            r.*,
            o.total as order_total,
            o.created_at as order_date,
            u.name as customer_name,
            u.email as customer_email
        FROM returns r
        JOIN orders o ON o.id = r.order_id
        JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC
    ");
    $returns = $returnsQuery->fetchAll();
} catch (PDOException $e) {
    // Table doesn't exist yet
    $returns = [];
}
?>

<div class="container-fluid">
        <div class="content-header">
            <h1><i class="fas fa-undo"></i> Returns Management</h1>
            <p class="text-muted">Manage customer return and refund requests</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

    <?php if (count($returns) > 0): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> All Return Requests 
                    <span class="badge bg-primary"><?= count($returns) ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Return ID</th>
                                <th>Order ID</th>
                                <th>Type</th>
                                <th>Customer</th>
                                <th>Reason</th>
                                <th>Refund Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returns as $return): ?>
                                <tr>
                                    <td class="fw-bold">#<?= htmlspecialchars($return['id']) ?></td>
                                    <td>
                                        <a href="orders.php" class="text-primary">
                                            #<?= htmlspecialchars($return['order_id']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?= $return['type'] === 'exchange' ? 'bg-info' : 'bg-warning text-dark' ?>">
                                            <?= ucfirst(htmlspecialchars($return['type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($return['customer_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($return['customer_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($return['reason']) ?></span>
                                        <?php if (!empty($return['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($return['description'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success">₹<?= number_format($return['refund_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = match(strtolower($return['status'])) {
                                                'pending' => 'bg-warning text-dark',
                                                'approved' => 'bg-info',
                                                'refunded' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst(htmlspecialchars($return['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('d M Y', strtotime($return['created_at'])) ?></small><br>
                                        <small class="text-muted"><?= date('h:i A', strtotime($return['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewDetails(<?= $return['id'] ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Return Requests</h4>
                <p class="text-muted">There are no return requests from customers yet.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Return Details Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Return Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="returnDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/_admin-footer.php'; ?>

<style>
    .admin-main {
        margin-left: 260px;
        padding: 30px;
        min-height: 100vh;
        background: #f5f7fa;
    }

    .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .content-header {
        margin-bottom: 30px;
    }

    .content-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        padding: 15px 20px;
    }

    .table th {
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
    }

    .table td {
        vertical-align: middle;
        padding: 15px;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 6px 12px;
        font-weight: 500;
        font-size: 12px;
    }

    .btn-sm {
        padding: 5px 12px;
        font-size: 13px;
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
    }

    .btn-close {
        background: transparent;
        border: none;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.7;
    }

    .btn-close:hover {
        opacity: 1;
    }

    /* Ensure modals are visible */
    .modal {
        display: none;
    }

    .modal.show {
        display: block !important;
    }

    .modal-backdrop {
        display: none;
    }

    .modal-backdrop.show {
        display: block !important;
    }

    /* Ensure modal is properly positioned */
    .modal {
        z-index: 10000 !important;
    }

    .modal-backdrop {
        z-index: 9999 !important;
    }

    /* Modal styling */
    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 160px);
        overflow-y: auto;
    }

    .modal-body {
        padding: 25px;
    }

    /* Custom scrollbar for modal */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }

    /* Modal sizes */
    .modal-xl {
        max-width: 1140px;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 20px 25px;
        border: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .admin-main {
            margin-left: 0;
            padding: 15px;
        }

        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 120px);
        }

        .modal-body {
            padding: 15px;
        }

        .modal-xl {
            max-width: 100%;
            margin: 10px;
        }

        .modal-dialog-centered {
            min-height: calc(100% - 20px);
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Remove any stray error messages on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Clean up any error messages outside the main content
        const body = document.body;
        const textNodes = [];
        const walk = document.createTreeWalker(body, NodeFilter.SHOW_TEXT, null, false);
        let node;
        while(node = walk.nextNode()) {
            if (node.nodeValue.trim() === 'Error loading return details.') {
                node.parentNode.removeChild(node);
            }
        }
    });

    function viewDetails(returnId) {
        // Show loading message
        document.getElementById('returnDetails').innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Loading details...</p></div>';
        
        // Show modal immediately
        const modal = new bootstrap.Modal(document.getElementById('returnModal'));
        modal.show();
        
        // Fetch and display return details
        fetch(`return-details.php?id=${returnId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('returnDetails').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('returnDetails').innerHTML = 
                    '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error loading return details. Please try again.</div>';
            });
    }

    function processReturn(id, action) {
        if (!confirm('Are you sure you want to ' + action + ' this request?')) return;

        // Find the button that was clicked - tricky since it's in a modal
        // We can rely on event.target if called directly from onclick
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        fetch('api/process_return.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
