<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../partials/header.php';

if (!isset($_GET['id'])) redirect('public/index.php');
$orderId = (int)$_GET['id'];
?>

<div class="container mt-5 fade-in">
  <div class="row justify-content-center">
    <div class="col-lg-6" data-aos="zoom-in">
      <div class="card shadow-lg border-0 p-4 text-center">

        <!-- ✅ Success Animation -->
        <div class="success-animation mb-3">
          <div class="checkmark">
            <svg viewBox="0 0 52 52">
              <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
              <path class="checkmark__check" fill="none" d="M14 27l7 7 16-16"/>
            </svg>
          </div>
        </div>

        <h2 class="text-success fw-bold mb-2">🎉 Order Placed Successfully!</h2>
        <p class="text-muted mb-4">Your order <b>#<?= $orderId ?></b> has been placed successfully.<br>We’ll contact you soon for delivery details.</p>

        <a href="<?= base_url('public/index.php') ?>" class="btn btn-primary w-100">
          <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- ✅ Styles -->
<style>
  body {
    background: #f8f9fa;
    font-family: 'Poppins', sans-serif;
  }
  .card {
    border-radius: 15px;
    background: #fff;
  }
  .btn-primary {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    border: none;
    font-weight: 600;
  }
  .btn-primary:hover {
    opacity: 0.9;
  }

  /* ✅ Success Checkmark Animation */
  .checkmark {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #4caf50;
    stroke-miterlimit: 10;
    margin: 10px auto;
    box-shadow: inset 0px 0px 0px #4caf50;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
  }
  .checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #4caf50;
    fill: none;
    animation: stroke 0.6s cubic-bezier(.65,0,.45,1) forwards;
  }
  .checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(.65,0,.45,1) 0.6s forwards;
  }
  @keyframes stroke {
    100% { stroke-dashoffset: 0; }
  }
  @keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
  }
  @keyframes fill {
    100% { box-shadow: inset 0px 0px 0px 30px #4caf50; }
  }
</style>

<!-- ✅ Animation -->
<script>
  AOS.init({
    duration: 800,
    once: true,
    easing: 'ease-out-cubic'
  });
</script>
