<?php
// public/analytics.php - Admin Analytics Dashboard
require_once __DIR__ . '/../config/functions.php';
requireLogin();

// Basic Admin Check (You can enhance this later)
// if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
//     header('Location: index.php');
//     exit;
// }

// --- FETCH DATA ---

// 1. KPI Cards
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

// 2. Sales Trend (Last 30 Days)
$salesTrend = $pdo->query("
    SELECT DATE(created_at) as date, SUM(total) as daily_total 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Fill in missing dates with 0
$dates = [];
$sales = [];
for ($i = 89; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d M', strtotime($d));
    $sales[] = $salesTrend[$d] ?? 0;
}

// 3. Top Categories
$catStats = $pdo->query("
    SELECT c.name, COUNT(oi.id) as count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$catLabels = array_column($catStats, 'name');
$catData = array_column($catStats, 'count');

// 4. Order Status Distribution
$statusStats = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$statusLabels = array_map('ucfirst', array_keys($statusStats));
$statusData = array_values($statusStats);

// 5. Recent Orders
$recentOrders = $pdo->query("
    SELECT o.id, u.name, o.total, o.status, o.created_at 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">📊 Store Analytics</h2>
        <div class="text-muted">Last 30 Days Overview</div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase mb-1" style="font-size: 0.8rem;">Total Revenue</h6>
                            <h3 class="fw-bold mb-0">₹<?= number_format($totalRevenue) ?></h3>
                        </div>
                        <div class="fs-1 opacity-25"><i class="fas fa-rupee-sign"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase mb-1" style="font-size: 0.8rem;">Total Orders</h6>
                            <h3 class="fw-bold mb-0"><?= number_format($totalOrders) ?></h3>
                        </div>
                        <div class="fs-1 opacity-25"><i class="fas fa-shopping-bag"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase mb-1" style="font-size: 0.8rem;">Total Users</h6>
                            <h3 class="fw-bold mb-0"><?= number_format($totalUsers) ?></h3>
                        </div>
                        <div class="fs-1 opacity-25"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-black-50 text-uppercase mb-1" style="font-size: 0.8rem;">Avg. Order Value</h6>
                            <h3 class="fw-bold mb-0">₹<?= number_format($avgOrderValue) ?></h3>
                        </div>
                        <div class="fs-1 opacity-25"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Sales Trend -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">📈 Sales Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Order Status -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">📦 Order Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">🕒 Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recentOrders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $o['id'] ?></td>
                                <td><?= htmlspecialchars($o['name']) ?></td>
                                <td>₹<?= number_format($o['total'], 2) ?></td>
                                <td>
                                    <?php 
                                    $badge = match(strtolower($o['status'])) {
                                        'completed', 'delivered' => 'success',
                                        'pending' => 'warning',
                                        'cancelled' => 'danger',
                                        'shipped' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badge ?> bg-opacity-10 text-<?= $badge ?> px-3 py-2 rounded-pill">
                                        <?= ucfirst($o['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('d M, h:i A', strtotime($o['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">🏆 Top Categories</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Common Options
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Sales Trend Chart
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Revenue (₹)',
                data: <?= json_encode($sales) ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Order Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusData) ?>,
                backgroundColor: ['#fbbf24', '#3b82f6', '#22c55e', '#ef4444', '#94a3b8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 3. Category Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($catLabels) ?>,
            datasets: [{
                data: <?= json_encode($catData) ?>,
                backgroundColor: ['#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
