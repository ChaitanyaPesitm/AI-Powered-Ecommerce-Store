<?php
// partials/footer.php
?>
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-container">

      <div class="footer-col brand">
        <div class="footer-logo-wrapper">
          <img src="<?= base_url('assets/icons/favicon-32x32.png') ?>" alt="Logo" class="footer-logo-img">
          <h2 class="footer-logo">The Seventh Com</h2>
        </div>
        <p class="footer-tagline">Your one-stop AI-powered shopping experience with intelligent recommendations.</p>
        <div class="social-links">
          <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>

      <div class="footer-col">
        <h3><i class="fas fa-link me-2"></i>Quick Links</h3>
        <ul>
          <li><a href="<?= base_url('public/index.php') ?>"><i class="fas fa-chevron-right"></i> Home</a></li>
          <li><a href="<?= base_url('public/products.php') ?>"><i class="fas fa-chevron-right"></i> Products</a></li>
          <li><a href="<?= base_url('public/cart.php') ?>"><i class="fas fa-chevron-right"></i> Cart</a></li>
          <li><a href="<?= base_url('public/wishlist.php') ?>"><i class="fas fa-chevron-right"></i> Wishlist</a></li>
          <li><a href="<?= base_url('public/orders.php') ?>"><i class="fas fa-chevron-right"></i> Orders</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3><i class="fas fa-headset me-2"></i>Customer Support</h3>
        <ul>
          <li><a href="<?= base_url('public/support.php') ?>"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
          <li><a href="<?= base_url('public/privacy.php') ?>"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
          <li><a href="<?= base_url('public/terms.php') ?>"><i class="fas fa-chevron-right"></i> Terms & Conditions</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> FAQs</a></li>
          <li><a href="#"><i class="fas fa-chevron-right"></i> Shipping Info</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h3><i class="fas fa-envelope me-2"></i>Contact Us</h3>
        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <a href="mailto:support@theseventhcom.com">support@theseventhcom.com</a>
        </div>
        <div class="contact-item">
          <i class="fas fa-phone"></i>
          <a href="tel:+919876543210">+91 98765 43210</a>
        </div>
        <div class="contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>Hyderabad, India</span>
        </div>
        <div class="newsletter-signup mt-3">
          <h4>Newsletter</h4>
          <form class="newsletter-form">
            <input type="email" placeholder="Your email" required>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>

    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start">
          <p class="mb-0">  <span id="year"></span> The Seventh Com. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> in India</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== STYLES ========== -->
  <style>
    .footer {
      width: 100vw;
      position: relative;
      left: 50%;
      right: 50%;
      margin-left: -50vw;
      margin-right: -50vw;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: 'Poppins', sans-serif;
      margin-top: 80px;
      color: white;
    }

    .footer-inner {
      width: 100%;
    }

    .footer-container {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      padding: 60px 80px;
      max-width: 1300px;
      margin: auto;
      gap: 2rem;
    }

    .footer-col {
      flex: 1 1 250px;
      min-width: 220px;
    }

    .footer-logo-wrapper {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .footer-logo-img {
      width: 40px;
      height: 40px;
    }

    .footer-logo {
      color: white;
      font-weight: 800;
      font-size: 1.5rem;
      margin: 0;
    }

    .footer-tagline {
      color: rgba(255,255,255,0.8);
      font-size: 0.95rem;
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }

    /* Social Links */
    .social-links {
      display: flex;
      gap: 1rem;
    }

    .social-icon {
      width: 40px;
      height: 40px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    }

    .social-icon:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-3px);
      color: #ffc107;
    }

    .footer-col h3 {
      color: white;
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
    }

    .footer-col ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-col ul li {
      margin: 0.75rem 0;
    }

    .footer-col ul li a {
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .footer-col ul li a i {
      font-size: 0.7rem;
      transition: transform 0.3s ease;
    }

    .footer-col ul li a:hover {
      color: #ffc107;
      padding-left: 0.5rem;
    }

    .footer-col ul li a:hover i {
      transform: translateX(5px);
    }

    /* Contact Items */
    .contact-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin: 1rem 0;
      color: rgba(255,255,255,0.8);
    }

    .contact-item i {
      width: 35px;
      height: 35px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .contact-item a {
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .contact-item a:hover {
      color: #ffc107;
    }

    /* Newsletter */
    .newsletter-signup h4 {
      color: white;
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }

    .newsletter-form {
      display: flex;
      gap: 0.5rem;
    }

    .newsletter-form input {
      flex: 1;
      padding: 0.75rem 1rem;
      border: none;
      border-radius: 50px;
      background: rgba(255,255,255,0.1);
      color: white;
      backdrop-filter: blur(10px);
    }

    .newsletter-form input::placeholder {
      color: rgba(255,255,255,0.6);
    }

    .newsletter-form input:focus {
      outline: none;
      background: rgba(255,255,255,0.2);
    }

    .newsletter-form button {
      width: 45px;
      height: 45px;
      border: none;
      border-radius: 50%;
      background: #ffc107;
      color: #000;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .newsletter-form button:hover {
      transform: scale(1.1);
      background: #ffb300;
    }

    /* Footer Bottom */
    .footer-bottom {
      background: rgba(0,0,0,0.2);
      border-top: 1px solid rgba(255,255,255,0.1);
      padding: 1.5rem 0;
      font-size: 0.9rem;
      color: rgba(255,255,255,0.8);
    }

    .footer-bottom .fa-heart {
      animation: pulse 1.5s ease-in-out infinite;
    }

    /* Dark Mode Footer */
    [data-theme="dark"] .footer {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    }

    [data-theme="dark"] .footer-bottom {
      background: rgba(0,0,0,0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .footer-container {
        flex-direction: column;
        text-align: center;
        padding: 40px 20px;
      }
      
      .footer-logo-wrapper {
        justify-content: center;
      }
      
      .social-links {
        justify-content: center;
      }
      
      .footer-col ul li a {
        justify-content: center;
      }
      
      .contact-item {
        justify-content: center;
      }
    }
  </style>

  <!-- ========== PWA INSTALL BUTTON ========== -->
  <button id="pwaInstallBtn" onclick="installPWA()" style="display: none;">
    <i class="fas fa-download"></i> Install App
  </button>

  <!-- ========== AI CHAT WIDGET ========== -->
  <!-- Floating Chat Launcher Button - Redirects to Support Page -->
  <a href="<?= base_url('public/support.php') ?>" class="ai-chat-launcher" title="Chat with The Seventh Com AI Support" style="position: fixed; bottom: 2rem; right: 2rem; width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); z-index: 9998; text-decoration: none;">
    <i class="fas fa-comments"></i>
    <span class="ai-chat-badge" style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.4rem; border-radius: 10px;">AI</span>
  </a>

  <!-- ========== SCRIPTS ========== -->
  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>

  <!-- Animation Libraries -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.0/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.6/lottie.min.js"></script>
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

  <script>
    AOS.init({
      once: true,
      duration: 600,
      easing: 'ease-out-cubic'
    });

    window.addEventListener('load', () => {
      document.querySelectorAll('.fade-in').forEach(el => el.classList.add('ready'));
    });
  </script>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- PWA Initialization -->
  <script src="<?= base_url('public/pwa-init.js') ?>"></script>
  
</body>
</html>