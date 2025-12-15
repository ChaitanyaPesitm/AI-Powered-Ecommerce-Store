<?php 
require_once __DIR__ . '/../config/functions.php'; 


// Get wishlist count if user is logged in
$wishlist_count = 0;
if (isLoggedIn()) {
    global $pdo;
    $user_id = $_SESSION['user']['id'];
    $st = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $st->execute([$user_id]);
    $wishlist_count = (int)$st->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>The Seventh Com</title>
  
  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/icons/apple-touch-icon.png') ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/icons/favicon-32x32.png') ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/icons/favicon-16x16.png') ?>">
  
  <!-- PWA Configuration -->
  <link rel="manifest" href="<?= base_url('manifest.json') ?>">
  <meta name="theme-color" content="#667eea">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Seventh Com">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- AOS Scroll Animation -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"/>

  <!-- Swiper (for carousels) -->
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>

  <!-- GSAP for Premium Animations -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

  <!-- Google Model Viewer (3D/AR) -->
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/voice-control.css') ?>">
  <script>const BASE_URL = '<?= base_url() ?>';</script>
  <script src="<?= base_url('assets/js/voice-control.js') ?>" defer></script>
  <script src="<?= base_url('assets/js/cart-ajax.js') ?>" defer></script>
  <script src="<?= base_url('assets/js/live-search.js') ?>" defer></script>
  
  <link rel="stylesheet" href="<?= base_url('assets/css/theme-toggle.css') ?>">
  
  <!-- Dark Mode Pages CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/dark-mode-pages.css') ?>">
  
  <!-- PWA Styles -->
  <link rel="stylesheet" href="<?= base_url('public/pwa-styles.css') ?>">
  
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--theme-bg-secondary);
      color: var(--theme-text-primary);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Enhanced Navbar */
    .navbar {
      background: var(--theme-navbar-bg);
      padding: 1rem 0;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
      backdrop-filter: blur(10px);
      transition: background 0.3s ease;
    }

    .navbar-brand {
      font-weight: 800;
      font-size: 1.5rem;
      color: #fff !important;
      letter-spacing: 1px;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transition: all 0.3s ease;
    }

    .navbar-brand:hover {
      transform: scale(1.05);
    }

    .navbar-brand img {
      transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
      transform: rotate(360deg);
    }

    .navbar .nav-link {
      color: var(--theme-navbar-link) !important;
      font-weight: 600;
      margin: 0 0.5rem;
      padding: 0.5rem 1rem !important;
      border-radius: var(--radius);
      transition: all 0.3s ease;
      position: relative;
    }

    .navbar .nav-link:hover {
      color: #fff !important;
      background: var(--theme-navbar-link-hover-bg);
      transform: translateY(-2px);
    }

    .navbar .nav-link i {
      margin-right: 0.5rem;
    }

    .navbar-toggler {
      border: 2px solid rgba(255,255,255,0.5);
      padding: 0.5rem;
    }

    .navbar-toggler:focus {
      box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
    }

    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* Enhanced Search Bar */
    .search-bar {
      max-width: 400px;
      min-width: 300px;
    }

    .search-bar input {
      border-radius: 50px 0 0 50px;
      border: none;
      padding: 0.65rem 1.25rem;
      font-size: 0.9rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .search-bar input:focus {
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      outline: none;
    }

    .search-bar button {
      border-radius: 0 50px 50px 0;
      border: none;
      padding: 0.65rem 1.25rem;
      background: #ffc107;
      color: #000;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .search-bar button:hover {
      background: #ffb300;
      transform: scale(1.05);
    }

    /* Badge Styling */
    .badge-custom {
      position: absolute;
      top: -5px;
      right: -5px;
      font-size: 0.7rem;
      background: #dc3545;
      color: #fff;
      border-radius: 50%;
      padding: 0.25rem 0.5rem;
      font-weight: 700;
      animation: pulse 2s infinite;
    }

    .nav-item {
      position: relative;
    }

    .nav-item .fa-heart {
      color: #ff6b6b;
    }

    .nav-item .fa-shopping-cart {
      color: #ffc107;
    }


    /* Logout Button */
    .nav-link.text-warning {
      background: rgba(255, 193, 7, 0.2);
      border-radius: var(--radius);
    }

    .nav-link.text-warning:hover {
      background: rgba(255, 193, 7, 0.3);
    }

    /* Responsive */
    @media (max-width: 991px) {
      .navbar-nav {
        text-align: center;
        margin-top: 1rem;
      }
      .search-bar {
        width: 100%;
        max-width: 100%;
        margin: 1rem 0;
      }
      .navbar .nav-link {
        margin: 0.25rem 0;
      }
    }
    
    @media (min-width: 992px) {
      .navbar-collapse {
        display: flex !important;
        align-items: center;
      }
      .navbar-nav {
        display: flex;
        flex-direction: row;
        align-items: center;
      }
    }
  </style>
  
  <script>
    // Simple Dark Mode Toggle
    function toggleDarkMode() {
      const html = document.documentElement;
      const current = html.getAttribute('data-theme') || 'light';
      const newTheme = current === 'light' ? 'dark' : 'light';
      
      html.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      
      // Update button text and icon
      const icon = document.querySelector('#darkModeToggle i');
      const text = document.getElementById('themeText');
      if (newTheme === 'dark') {
        icon.className = 'fas fa-sun';
        text.textContent = 'Light';
      } else {
        icon.className = 'fas fa-moon';
        text.textContent = 'Dark';
      }
    }
    
    // Load saved theme
    (function() {
      const saved = localStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', saved);
      
      // Update button on load
      window.addEventListener('DOMContentLoaded', function() {
        const icon = document.querySelector('#darkModeToggle i');
        const text = document.getElementById('themeText');
        if (icon && text) {
          if (saved === 'dark') {
            icon.className = 'fas fa-sun';
            text.textContent = 'Light';
          }
        }
      });
    })();
  </script>
</head>
<body>

<!-- Enhanced Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand" href="<?= base_url('public/index.php') ?>">
      <img src="<?= base_url('assets/icons/favicon-32x32.png') ?>" alt="Logo" style="height:40px;">
      The Seventh Com
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="mainNavbar">
      <!-- Center Search -->
      <form class="d-flex mx-auto search-bar" method="get" action="<?= base_url('public/products.php') ?>">
        <input class="form-control" type="search" name="q" placeholder="Search products..." aria-label="Search">
        <button class="btn" type="submit"><i class="fas fa-search"></i></button>
      </form>

      <!-- Right Side Links -->
      <ul class="navbar-nav ms-auto align-items-lg-center">
        
        <!-- Dark Mode Toggle -->
        <li class="nav-item">
          <a class="nav-link" href="javascript:void(0)" onclick="toggleDarkMode()" id="darkModeToggle">
            <i class="fas fa-moon"></i> <span id="themeText">Dark</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('public/products.php') ?>">
            <i class="fas fa-store"></i> Products
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('public/cart.php') ?>">
            <i class="fas fa-shopping-cart"></i> Cart
          </a>
        </li>

        <?php if (isLoggedIn()): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('public/wishlist.php') ?>">
              <i class="fas fa-heart"></i> Wishlist
              <?php if ($wishlist_count > 0): ?>
                <span class="badge-custom"><?= $wishlist_count ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('public/orders.php') ?>">
              <i class="fas fa-box"></i> Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-warning" href="<?= base_url('public/logout.php') ?>">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('public/login.php') ?>">
              <i class="fas fa-sign-in-alt"></i> Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('public/register.php') ?>">
              <i class="fas fa-user-plus"></i> Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Page Content Starts -->
