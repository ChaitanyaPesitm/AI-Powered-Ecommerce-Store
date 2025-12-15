<?php
require_once __DIR__ . '/../config/functions.php';
requireLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'return'; // 'return' or 'exchange'

if (!$order_id) {
    redirect('public/orders.php');
}

// Fetch order to verify ownership and status
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user']['id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('public/orders.php');
}

// Check if already requested
$checkStmt = $pdo->prepare("SELECT * FROM returns WHERE order_id = ?");
$checkStmt->execute([$order_id]);
$existingRequest = $checkStmt->fetch();

if ($existingRequest) {
    // Redirect to track order if already requested
    redirect('public/track-order.php?id=' . $order_id);
}

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-gradient-primary text-white p-4 rounded-top-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas <?= $type === 'exchange' ? 'fa-exchange-alt' : 'fa-undo' ?> me-2"></i>
                        Request <?= ucfirst($type) ?>
                    </h4>
                    <p class="mb-0 opacity-75 small">Order #<?= $order_id ?></p>
                </div>
                <div class="card-body p-4">
                    <form id="returnRequestForm">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <input type="hidden" name="type" value="<?= $type ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Reason for <?= ucfirst($type) ?></label>
                            <select name="reason" class="form-select form-select-lg" required>
                                <option value="" selected disabled>Select a reason...</option>
                                <option value="Defective/Damaged">Defective or Damaged Product</option>
                                <option value="Wrong Item">Received Wrong Item</option>
                                <option value="Size/Fit Issue">Size or Fit Issue</option>
                                <option value="Quality Issue">Quality Not as Expected</option>
                                <option value="Changed Mind">Changed Mind</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="4" 
                                placeholder="Please provide more details about your request..." required></textarea>
                            <div class="form-text">Detailed information helps us process your request faster.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Submit Request
                            </button>
                            <a href="track-order.php?id=<?= $order_id ?>" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('returnRequestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';
    btn.disabled = true;

    try {
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        const response = await fetch('api/submit_return_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('Request submitted successfully!');
            window.location.href = 'track-order.php?id=<?= $order_id ?>';
        } else {
            alert(result.message || 'An error occurred');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred. Please try again.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});
</script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}
.rounded-top-4 {
    border-top-left-radius: 1rem !important;
    border-top-right-radius: 1rem !important;
}
.rounded-4 {
    border-radius: 1rem !important;
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
