<?php
// admin/create_admin.php - Create Admin User
require_once __DIR__ . '/../config/functions.php';

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $existing = findUserByEmail($email);
        if ($existing) {
            $errors[] = "Email already registered.";
        }
    }

    // Create admin user
    if (empty($errors)) {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$name, $email, $hashed]);
            $success = "Admin account created successfully! You can now login.";
        } catch (PDOException $e) {
            $errors[] = "Failed to create admin account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create Admin - The Seventh Com</title>
  
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
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
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
    }

    .circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 20s infinite ease-in-out;
    }

    .circle-1 {
      width: 300px;
      height: 300px;
      top: -100px;
      right: -100px;
    }

    .circle-2 {
      width: 250px;
      height: 250px;
      bottom: -80px;
      left: -80px;
      animation-delay: 5s;
    }

    .circle-3 {
      width: 180px;
      height: 180px;
      top: 50%;
      left: 20%;
      animation-delay: 10s;
    }

    @keyframes float {
      0%, 100% {
        transform: translate(0, 0) rotate(0deg);
      }
      50% {
        transform: translate(30px, -30px) rotate(180deg);
      }
    }

    .container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 500px;
    }

    .card {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 20px;
    }

    .logo-icon {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .logo-icon i {
      font-size: 24px;
      color: white;
    }

    .logo-text h1 {
      font-size: 22px;
      color: #2d3748;
      margin: 0;
    }

    .logo-text p {
      font-size: 12px;
      color: #667eea;
      margin: 0;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .icon-box {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
      animation: pulse 2s infinite;
    }

    .icon-box i {
      font-size: 40px;
      color: white;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.05);
      }
    }

    h2 {
      color: #2d3748;
      font-size: 26px;
      margin-bottom: 8px;
    }

    .subtitle {
      color: #718096;
      font-size: 14px;
      margin-bottom: 25px;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 14px;
      animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .alert-success {
      background: #c6f6d5;
      color: #22543d;
      border-left: 4px solid #38a169;
    }

    .alert-error {
      background: #fed7d7;
      color: #742a2a;
      border-left: 4px solid #e53e3e;
    }

    .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    label {
      display: block;
      color: #4a5568;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 14px;
    }

    label i {
      margin-right: 5px;
      color: #667eea;
    }

    input {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 15px;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
    }

    input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(102, 126, 234, 0.5);
    }

    .btn:active {
      transform: translateY(0);
    }

    .links {
      text-align: center;
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid #e2e8f0;
    }

    .links a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.3s ease;
    }

    .links a:hover {
      color: #764ba2;
      gap: 8px;
    }

    @media (max-width: 576px) {
      .card {
        padding: 30px 25px;
      }

      h2 {
        font-size: 22px;
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

  <div class="container">
    <div class="card" data-aos="fade-up">
      <div class="header">
        <div class="logo">
          <div class="logo-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="logo-text">
            <h1>The Seventh Com</h1>
            <p>Admin Panel</p>
          </div>
        </div>
        
        <div class="icon-box">
          <i class="fas fa-user-shield"></i>
        </div>
        <h2>Create Admin Account</h2>
        <p class="subtitle">Set up a new administrator account</p>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php foreach ($errors as $error): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-triangle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endforeach; ?>

      <?php if (!$success): ?>
        <form method="post">
          <div class="form-group">
            <label><i class="fas fa-user"></i> Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" name="email" placeholder="admin@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" placeholder="Minimum 6 characters" required>
          </div>

          <div class="form-group">
            <label><i class="fas fa-lock"></i> Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
          </div>

          <button type="submit" class="btn">
            <i class="fas fa-user-plus"></i>
            <span>Create Admin Account</span>
          </button>
        </form>
      <?php endif; ?>

      <div class="links">
        <a href="login.php">
          <i class="fas fa-arrow-left"></i>
          Back to Login
        </a>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 600,
      once: true
    });
  </script>
</body>
</html>
