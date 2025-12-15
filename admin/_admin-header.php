<?php 
require_once __DIR__ . '/../config/functions.php'; 
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Panel - The Seventh Com</title>
  
  <!-- Favicon using Font Awesome icon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🛒</text></svg>">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
    }

    /* Sidebar */
    .admin-sidebar {
      position: fixed;
      left: 0;
      top: 0;
      width: 260px;
      height: 100vh;
      background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      transition: all 0.3s ease;
    }

    .sidebar-header {
      padding: 25px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
    }

    .sidebar-logo {
      width: 70px;
      height: 70px;
      margin: 0 auto 15px;
      background: linear-gradient(135deg, #ffffff, #f0f0f0);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: pulse 2s infinite;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
      border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .sidebar-logo i {
      font-size: 36px;
      color: #667eea;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .sidebar-title {
      color: white;
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .sidebar-subtitle {
      color: rgba(255, 255, 255, 0.7);
      font-size: 12px;
    }

    .sidebar-nav {
      padding: 20px 0;
    }

    .nav-item {
      display: flex;
      align-items: center;
      padding: 14px 25px;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
      gap: 15px;
      font-size: 15px;
    }

    .nav-item:hover {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border-left-color: #fff;
    }

    .nav-item.active {
      background: rgba(255, 255, 255, 0.15);
      color: white;
      border-left-color: #fff;
    }

    .nav-item i {
      font-size: 18px;
      width: 24px;
    }

    .sidebar-footer {
      position: absolute;
      bottom: 0;
      width: 100%;
      padding: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 12px;
      color: white;
      padding: 12px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }

    .user-details {
      flex: 1;
    }

    .user-name {
      font-size: 14px;
      font-weight: 600;
    }

    .user-role {
      font-size: 11px;
      color: rgba(255, 255, 255, 0.7);
    }

    /* Main Content */
    .admin-main {
      margin-left: 260px;
      padding: 30px;
      min-height: 100vh;
    }

    .admin-header {
      background: white;
      padding: 20px 30px;
      border-radius: 16px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .page-title {
      font-size: 28px;
      font-weight: 700;
      color: #1a202c;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .page-title i {
      color: #667eea;
    }

    .header-actions {
      display: flex;
      gap: 12px;
    }

    .btn-header {
      padding: 10px 20px;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
      background: white;
      color: #4a5568;
      border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
      background: #f7fafc;
    }

    /* Content Container */
    .admin-content {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 25px;
      border-radius: 16px;
      color: white;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .stat-card.green {
      background: linear-gradient(135deg, #11998e, #38ef7d);
      box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
    }

    .stat-card.orange {
      background: linear-gradient(135deg, #f093fb, #f5576c);
      box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .stat-value {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .stat-label {
      font-size: 14px;
      opacity: 0.9;
    }

    /* Tables */
    .table-container {
      overflow-x: auto;
      margin-top: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: linear-gradient(135deg, #f7fafc, #edf2f7);
    }

    th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      color: #2d3748;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    td {
      padding: 15px;
      border-bottom: 1px solid #e2e8f0;
      color: #4a5568;
    }

    tr:hover {
      background: #f7fafc;
    }

    /* Forms */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #2d3748;
      font-size: 14px;
    }

    .form-input, .form-select, .form-textarea {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
      background: #f7fafc;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
      outline: none;
      border-color: #667eea;
      background: white;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-textarea {
      resize: vertical;
      min-height: 100px;
    }

    /* Buttons */
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-family: 'Poppins', sans-serif;
    }

    .btn-save {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-delete {
      background: linear-gradient(135deg, #fc8181, #f56565);
      color: white;
      box-shadow: 0 4px 15px rgba(252, 129, 129, 0.3);
    }

    .btn-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(252, 129, 129, 0.4);
    }

    .btn-edit {
      background: linear-gradient(135deg, #4299e1, #3182ce);
      color: white;
      box-shadow: 0 4px 15px rgba(66, 153, 225, 0.3);
    }

    .btn-edit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
    }

    /* Alerts */
    .alert {
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      animation: slideInDown 0.5s ease;
    }

    .alert-error {
      background: linear-gradient(135deg, #fc8181, #f56565);
      color: white;
    }

    .alert-success {
      background: linear-gradient(135deg, #68d391, #48bb78);
      color: white;
    }

    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.05);
      }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .admin-sidebar {
        transform: translateX(-260px);
      }

      .admin-main {
        margin-left: 0;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="admin-sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <i class="fas fa-store"></i>
      </div>
      <div class="sidebar-title">The Seventh Com</div>
      <div class="sidebar-subtitle">Admin Panel</div>
    </div>

    <nav class="sidebar-nav">
      <a href="<?= base_url('admin/index.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard</span>
      </a>
      <a href="<?= base_url('admin/analytics.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-bar"></i>
        <span>Analytics</span>
      </a>
      <a href="<?= base_url('admin/products.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
        <i class="fas fa-box"></i>
        <span>Products</span>
      </a>
      <a href="<?= base_url('admin/orders.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
        <i class="fas fa-shopping-cart"></i>
        <span>Orders</span>
      </a>
      <a href="<?= base_url('admin/returns.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'returns.php' ? 'active' : '' ?>">
        <i class="fas fa-undo"></i>
        <span>Returns</span>
      </a>
      <a href="<?= base_url('admin/reviews.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
        <i class="fas fa-star"></i>
        <span>Reviews</span>
      </a>
      <a href="<?= base_url('admin/stories.php') ?>" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'stories.php' ? 'active' : '' ?>">
        <i class="fas fa-photo-video"></i>
        <span>Stories</span>
      </a>
      <a href="<?= base_url('public/index.php') ?>" class="nav-item">
        <i class="fas fa-store"></i>
        <span>View Store</span>
      </a>
      <a href="<?= base_url('admin/logout.php') ?>" class="nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </div>
  </div>

  <!-- Main Content -->
  <div class="admin-main">
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
      AOS.init({
        duration: 600,
        once: true
      });
    </script>
