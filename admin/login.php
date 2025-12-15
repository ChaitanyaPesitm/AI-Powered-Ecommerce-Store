<?php 
require_once __DIR__ . '/../config/functions.php'; 

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Fetch user by email
    $u = findUserByEmail($email);

    // Validate admin credentials
    if (!$u || $u['role'] !== 'admin' || !password_verify($pass, $u['password_hash'])) {
        $errors[] = "Invalid admin credentials.";
    }

    // If valid → login
    if (!$errors) {
        // ✅ Store admin user in separate session key
        $_SESSION['admin_user'] = [
            'id'    => $u['id'],
            'name'  => $u['name'],
            'email' => $u['email'],
            'role'  => $u['role']
        ];
        
        // Clear public user session if exists
        unset($_SESSION['user']);

        redirect('admin/index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login - The Seventh Com</title>
  
  <!-- Favicon -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='grad' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%23667eea;stop-opacity:1' /><stop offset='100%' style='stop-color:%23764ba2;stop-opacity:1' /></linearGradient></defs><rect width='100' height='100' rx='20' fill='url(%23grad)'/><text x='50' y='70' font-size='60' text-anchor='middle' fill='white' font-family='Arial'>🛒</text></svg>">
  
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    /* Animated Background */
    .animated-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      overflow: hidden;
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      animation: float 25s infinite ease-in-out;
    }

    .circle-1 {
      width: 400px;
      height: 400px;
      top: -100px;
      left: -100px;
      animation-delay: 0s;
    }

    .circle-2 {
      width: 300px;
      height: 300px;
      bottom: -80px;
      right: -80px;
      animation-delay: 5s;
    }

    .circle-3 {
      width: 200px;
      height: 200px;
      top: 50%;
      left: 50%;
      animation-delay: 10s;
    }

    @keyframes float {
      0%, 100% {
        transform: translate(0, 0) rotate(0deg);
      }
      33% {
        transform: translate(30px, -30px) rotate(120deg);
      }
      66% {
        transform: translate(-20px, 20px) rotate(240deg);
      }
    }

    /* Login Container */
    .login-container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 450px;
      padding: 20px;
    }

    .admin-login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 28px;
      padding: 50px 40px;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .admin-login-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 35px 90px rgba(0, 0, 0, 0.5);
    }

    /* Header */
    .admin-header {
      text-align: center;
      margin-bottom: 35px;
    }

    /* Logo */
    .admin-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 2px solid #e2e8f0;
    }

    .logo-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
      animation: float 3s ease-in-out infinite;
    }

    .logo-icon i {
      font-size: 30px;
      color: white;
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-10px);
      }
    }

    .logo-text {
      text-align: left;
    }

    .logo-title {
      font-size: 24px;
      font-weight: 700;
      color: #1e3c72;
      margin: 0;
      line-height: 1.2;
    }

    .logo-subtitle {
      font-size: 13px;
      color: #7e22ce;
      margin: 3px 0 0 0;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .admin-icon {
      width: 90px;
      height: 90px;
      margin: 0 auto 20px;
      background: linear-gradient(135deg, #1e3c72, #7e22ce);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: pulse 2.5s infinite;
      box-shadow: 0 10px 30px rgba(126, 34, 206, 0.4);
    }

    .admin-icon i {
      font-size: 45px;
      color: white;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 30px rgba(126, 34, 206, 0.4);
      }
      50% {
        transform: scale(1.08);
        box-shadow: 0 10px 40px rgba(126, 34, 206, 0.6);
      }
    }

    .admin-header h2 {
      color: #1a202c;
      font-weight: 700;
      font-size: 30px;
      margin-bottom: 10px;
    }

    .admin-header p {
      color: #718096;
      font-size: 15px;
    }

    /* Alert */
    .alert-error {
      background: linear-gradient(135deg, #fc8181, #f56565);
      color: white;
      padding: 14px 18px;
      border-radius: 14px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      animation: slideInDown 0.5s ease;
      box-shadow: 0 4px 15px rgba(245, 101, 101, 0.3);
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

    /* Form */
    .admin-form {
      margin-bottom: 25px;
    }

    .form-group {
      position: relative;
      margin-bottom: 28px;
    }

    .form-input {
      width: 100%;
      padding: 16px 16px 16px 50px;
      border: 2px solid #e2e8f0;
      border-radius: 14px;
      font-size: 15px;
      background: #f7fafc;
      transition: all 0.3s ease;
      outline: none;
      font-family: 'Poppins', sans-serif;
    }

    .form-input:focus {
      border-color: #7e22ce;
      background: white;
      box-shadow: 0 0 0 4px rgba(126, 34, 206, 0.1);
    }

    /* Handle browser autofill */
    .form-input:-webkit-autofill,
    .form-input:-webkit-autofill:hover,
    .form-input:-webkit-autofill:focus {
      -webkit-box-shadow: 0 0 0 1000px #f7fafc inset !important;
      -webkit-text-fill-color: #2d3748 !important;
      border-color: #e2e8f0 !important;
      transition: background-color 5000s ease-in-out 0s;
    }

    .form-input:focus + .floating-label,
    .form-input:not(:placeholder-shown) + .floating-label,
    .form-input:-webkit-autofill + .floating-label {
      transform: translateY(-34px) translateX(-12px) scale(0.85);
      color: #7e22ce;
      background: white;
      padding: 0 8px;
    }

    .floating-label {
      position: absolute;
      left: 50px;
      top: 16px;
      color: #a0aec0;
      font-size: 15px;
      pointer-events: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 18px;
      top: 17px;
      font-size: 18px;
      color: #a0aec0;
      transition: color 0.3s ease;
    }

    .form-input:focus ~ .input-icon {
      color: #7e22ce;
    }

    .input-border {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #1e3c72, #7e22ce);
      transition: width 0.4s ease;
    }

    .form-input:focus ~ .input-border {
      width: 100%;
    }

    /* Button */
    .btn-admin {
      width: 100%;
      padding: 16px 32px;
      border: none;
      border-radius: 14px;
      font-size: 17px;
      font-weight: 600;
      cursor: pointer;
      background: linear-gradient(135deg, #1e3c72 0%, #7e22ce 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      transition: all 0.3s ease;
      box-shadow: 0 10px 30px rgba(126, 34, 206, 0.4);
      font-family: 'Poppins', sans-serif;
    }

    .btn-admin:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(126, 34, 206, 0.5);
    }

    .btn-admin:active {
      transform: translateY(-1px);
    }

    .btn-icon {
      transition: transform 0.3s ease;
    }

    .btn-admin:hover .btn-icon {
      transform: translateX(5px);
    }

    /* Back Link */
    .back-link {
      text-align: center;
      margin-top: 25px;
    }

    .back-link a {
      color: #7e22ce;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
      position: relative;
    }

    .back-link a::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #1e3c72, #7e22ce);
      transition: width 0.3s ease;
    }

    .back-link a:hover {
      color: #1e3c72;
    }

    .back-link a:hover::after {
      width: 100%;
    }

    /* Responsive */
    @media (max-width: 576px) {
      .admin-login-card {
        padding: 40px 30px;
      }

      .admin-icon {
        width: 70px;
        height: 70px;
      }

      .admin-icon i {
        font-size: 35px;
      }

      .admin-header h2 {
        font-size: 26px;
      }
    }
  </style>
</head>
<body>
  <!-- Animated Background -->
  <div class="animated-bg">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
    <div class="circle circle-3"></div>
  </div>

  <!-- Login Container -->
  <div class="login-container">
    <div class="admin-login-card" data-aos="fade-up" data-aos-duration="800">
      <!-- Header -->
      <div class="admin-header">
        <div class="admin-logo">
          <div class="logo-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="logo-text">
            <h2 class="logo-title">The Seventh Com</h2>
            <p class="logo-subtitle">Admin Panel</p>
          </div>
        </div>
        <div class="admin-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1 class="admin-title">Admin Access</h1>
        <p class="admin-subtitle">Secure login for administrators</p>
      </div>

      <!-- Error Messages -->
      <?php foreach ($errors as $e): ?>
        <div class="alert-error" data-aos="shake">
          <i class="fas fa-exclamation-triangle"></i>
          <?= htmlspecialchars($e) ?>
        </div>
      <?php endforeach; ?>

      <!-- Login Form -->
      <form method="post" class="admin-form" autocomplete="off">
        <div class="form-group">
          <input type="email" name="email" id="email" class="form-input" placeholder=" " required autocomplete="off">
          <label for="email" class="floating-label">Admin Email</label>
          <i class="fas fa-envelope input-icon"></i>
          <div class="input-border"></div>
        </div>

        <div class="form-group">
          <input type="password" name="password" id="password" class="form-input" placeholder=" " required autocomplete="new-password">
          <label for="password" class="floating-label">Password</label>
          <i class="fas fa-lock input-icon"></i>
          <div class="input-border"></div>
        </div>

        <button type="submit" class="btn-admin">
          <span>Sign In</span>
          <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
        </button>
      </form>

      <div class="back-link">
        <a href="<?= base_url('public/index.php') ?>">
          <i class="fas fa-arrow-left"></i> Back to Store
        </a>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 800,
      once: true,
      offset: 100
    });
  </script>
</body>
</html>
