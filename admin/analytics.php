<?php
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '_admin-header.php';
?>

<div class="container-fluid py-4">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="h3 mb-2">
            <i class="fas fa-chart-line me-2 text-primary"></i>Analytics Dashboard
          </h1>
          <p class="text-muted mb-0">
            <i class="fas fa-robot me-1"></i>AI-powered insights and data visualization
          </p>
        </div>
        <div>
          <button class="btn btn-primary" onclick="fetchAnalyticsData(30)">
            <i class="fas fa-sync-alt me-2"></i>Refresh Data
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="loading-overlay">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-3">Loading analytics data...</p>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4" id="summaryCards">
    <div class="col-xl-3 col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-sm rounded-circle bg-primary bg-soft text-primary">
                <i class="fas fa-rupee-sign fa-2x"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <p class="text-muted mb-1">Total Revenue</p>
              <h4 class="mb-0" id="totalRevenue">₹0</h4>
              <small class="text-success" id="revenueGrowth">+0%</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-sm rounded-circle bg-success bg-soft text-success">
                <i class="fas fa-shopping-cart fa-2x"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <p class="text-muted mb-1">Total Orders</p>
              <h4 class="mb-0" id="totalOrders">0</h4>
              <small class="text-muted" id="ordersPeriod">Last 30 days</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-sm rounded-circle bg-warning bg-soft text-warning">
                <i class="fas fa-chart-bar fa-2x"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <p class="text-muted mb-1">Avg Order Value</p>
              <h4 class="mb-0" id="avgOrderValue">₹0</h4>
              <small class="text-muted">Per order</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-sm rounded-circle bg-info bg-soft text-info">
                <i class="fas fa-users fa-2x"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <p class="text-muted mb-1">New Customers</p>
              <h4 class="mb-0" id="newCustomers">0</h4>
              <small class="text-muted" id="returningCustomers">0 returning</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row 1 -->
  <div class="row g-3 mb-4">
    <!-- Sales Trend Chart -->
    <div class="col-xl-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-chart-area me-2"></i>Sales Trend</h5>
        </div>
        <div class="card-body">
          <canvas id="salesTrendChart" height="100"></canvas>
        </div>
      </div>
    </div>

    <!-- Orders by Status -->
    <div class="col-xl-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-pie-chart me-2"></i>Orders by Status</h5>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <canvas id="orderStatusChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row 2 -->
  <div class="row g-3 mb-4">
    <!-- Top Products -->
    <div class="col-xl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-trophy me-2"></i>Top Selling Products</h5>
        </div>
        <div class="card-body">
          <canvas id="topProductsChart" height="120"></canvas>
        </div>
      </div>
    </div>

    <!-- Category Revenue -->
    <div class="col-xl-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i>Revenue by Category</h5>
        </div>
        <div class="card-body">
          <canvas id="categoryRevenueChart" height="120"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Tables Row -->
  <div class="row g-3 mb-4">
    <!-- Recent Orders -->
    <div class="col-xl-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Recent Orders</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="recentOrdersTable">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="5" class="text-center">Loading...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="col-xl-4">
      <div class="card border-0 shadow-sm border-warning">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Low Stock Alerts</h5>
        </div>
        <div class="card-body">
          <div id="lowStockList">
            <p class="text-center text-muted">Loading...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Category Ratings -->
  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h5 class="card-title mb-0"><i class="fas fa-star me-2"></i>Average Ratings by Category</h5>
        </div>
        <div class="card-body">
          <canvas id="categoryRatingsChart" height="60"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '_admin-footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Analytics Script -->
<script src="../assets/js/admin-analytics.js"></script>

<style>
/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  transition: opacity 0.3s ease;
}

.loading-overlay.hidden {
  opacity: 0;
  pointer-events: none;
}

/* Avatar Icons */
.avatar-sm {
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.bg-soft {
  opacity: 0.15;
}

/* Card Enhancements */
.card {
  transition: all 0.3s ease;
  border-radius: 12px !important;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  color: white !important;
  border: none !important;
  padding: 1rem 1.5rem;
}

.card-header h5 {
  color: white !important;
  margin: 0;
  font-weight: 600;
}

.card-body {
  padding: 1.5rem;
}

/* Summary Cards */
#summaryCards .card {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

#summaryCards h4 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

/* Low Stock Alerts */
.low-stock-item {
  padding: 0.75rem;
  border-left: 4px solid #ffc107;
  background: linear-gradient(90deg, #fff3cd 0%, #ffffff 100%);
  margin-bottom: 0.5rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.low-stock-item:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
}

.low-stock-item:last-child {
  margin-bottom: 0;
}

/* Table Enhancements */
.table {
  margin-bottom: 0;
}

.table thead th {
  background: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  color: #6c757d;
}

.table tbody tr {
  transition: all 0.2s ease;
}

.table tbody tr:hover {
  background: #f8f9fa;
  transform: scale(1.01);
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card {
  animation: fadeIn 0.5s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .avatar-sm {
    width: 50px;
    height: 50px;
    font-size: 20px;
  }
  
  #summaryCards h4 {
    font-size: 1.5rem;
  }
}

/* Chart Containers */
canvas {
  max-height: 400px;
}

/* Status Badges */
.badge {
  padding: 0.5rem 0.75rem;
  font-weight: 600;
  border-radius: 6px;
}

/* Refresh Button */
.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  padding: 0.5rem 1.5rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}
</style>
