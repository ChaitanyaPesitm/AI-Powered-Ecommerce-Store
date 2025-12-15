<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/functions.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$errors = [];

// --- Handle login form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $errors[] = "All fields are required.";
  } else {
    $user = findUserByEmail($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
      $errors[] = "Invalid email or password.";
    } else {
      // ✅ Store minimal session info
      $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'] ?? 'user'
      ];

      // ✅ Clean output buffer before redirect (prevents header issues)
      if (ob_get_length()) ob_end_clean();

      // ✅ Redirect to home page
      header("Location: " . base_url('public/index.php'));
      exit;
    }
  }
}
?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<!-- ✅ Login Page Layout -->
<div class="login-wrapper">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="login-card" data-aos="fade-up" data-aos-duration="800">
          <div class="login-header">
            <div class="icon-wrapper">
              <i class="fas fa-user-circle"></i>
            </div>
            <h2 class="text-center fw-bold mb-2">Welcome Back</h2>
            <p class="text-center text-muted">Sign in to continue</p>
          </div>

          <!-- Error messages -->
          <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger alert-modern" data-aos="shake">
              <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($e) ?>
            </div>
          <?php endforeach; ?>

          <!-- Login form -->
          <form method="post" novalidate class="login-form">
            <div class="form-group-modern mb-4">
              <input type="email" name="email" id="email" class="form-control-modern" placeholder="" required>
              <label for="email" class="floating-label">
                <i class="fas fa-envelope me-2"></i>Email Address
              </label>
              <div class="input-border"></div>
            </div>

            <div class="form-group-modern mb-4">
              <input type="password" name="password" id="password" class="form-control-modern" placeholder="" required>
              <label for="password" class="floating-label">
                <i class="fas fa-lock me-2"></i>Password
              </label>
              <div class="input-border"></div>
            </div>

            <button type="submit" class="btn-modern btn-primary-modern w-100">
              <span class="btn-text">Sign In</span>
              <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
            </button>

            <div class="text-center mt-4">
              <p class="mb-0 text-muted">Don't have an account?
                <a href="<?= base_url('public/register.php') ?>" class="link-modern">
                  Register here
                </a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Animated Background -->
<div class="animated-bg">
  <div class="circle circle-1"></div>
  <div class="circle circle-2"></div>
  <div class="circle circle-3"></div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- ✅ Enhanced Modern Styles with Animations -->
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
  }

  .login-wrapper {
    position: relative;
    z-index: 10;
    padding: 40px 0;
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
    background: rgba(255, 255, 255, 0.1);
    animation: float 20s infinite ease-in-out;
  }

  .circle-1 {
    width: 300px;
    height: 300px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
  }

  .circle-2 {
    width: 200px;
    height: 200px;
    top: 60%;
    right: 15%;
    animation-delay: 3s;
  }

  .circle-3 {
    width: 150px;
    height: 150px;
    bottom: 20%;
    left: 50%;
    animation-delay: 6s;
  }

  @keyframes float {
    0%, 100% {
      transform: translateY(0) rotate(0deg);
    }
    50% {
      transform: translateY(-30px) rotate(180deg);
    }
  }

  /* Login Card */
  .login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .login-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
  }

  /* Header Section */
  .login-header {
    text-align: center;
    margin-bottom: 30px;
  }

  .icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
  }

  .icon-wrapper i {
    font-size: 40px;
    color: white;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
      box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
    }
    50% {
      transform: scale(1.05);
      box-shadow: 0 0 0 20px rgba(102, 126, 234, 0);
    }
  }

  .login-header h2 {
    color: #2d3748;
    font-weight: 700;
    font-size: 28px;
    margin-bottom: 8px;
  }

  .login-header p {
    color: #718096;
    font-size: 14px;
  }

  /* Modern Form Groups with Floating Labels */
  .form-group-modern {
    position: relative;
    margin-bottom: 25px;
  }

  .form-control-modern {
    width: 100%;
    padding: 16px 16px 16px 45px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    background: #f7fafc;
    transition: all 0.3s ease;
    outline: none;
  }

  .form-control-modern:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  .form-control-modern:focus + .floating-label,
  .form-control-modern:not(:placeholder-shown) + .floating-label {
    transform: translateY(-32px) translateX(-10px) scale(0.85);
    color: #667eea;
    background: white;
    padding: 0 8px;
  }

  .floating-label {
    position: absolute;
    left: 45px;
    top: 16px;
    color: #a0aec0;
    font-size: 15px;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
  }

  .floating-label i {
    position: absolute;
    left: -30px;
    font-size: 18px;
  }

  .input-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.4s ease;
  }

  .form-control-modern:focus ~ .input-border {
    width: 100%;
  }

  /* Modern Button */
  .btn-modern {
    position: relative;
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
  }

  .btn-primary-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
  }

  .btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
  }

  .btn-primary-modern:active {
    transform: translateY(0);
  }

  .btn-icon {
    transition: transform 0.3s ease;
  }

  .btn-modern:hover .btn-icon {
    transform: translateX(5px);
  }

  /* Alert Modern */
  .alert-modern {
    border-radius: 12px;
    padding: 12px 16px;
    border: none;
    background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
    color: white;
    font-size: 14px;
    display: flex;
    align-items: center;
    animation: slideInDown 0.5s ease;
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

  /* Link Modern */
  .link-modern {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    position: relative;
    transition: color 0.3s ease;
  }

  .link-modern::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
  }

  .link-modern:hover {
    color: #764ba2;
  }

  .link-modern:hover::after {
    width: 100%;
  }

  /* Responsive */
  @media (max-width: 576px) {
    .login-card {
      padding: 30px 20px;
      margin: 20px;
    }

    .icon-wrapper {
      width: 60px;
      height: 60px;
    }

    .icon-wrapper i {
      font-size: 30px;
    }

    .login-header h2 {
      font-size: 24px;
    }
  }
</style>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true,
    offset: 100
  });
</script>
