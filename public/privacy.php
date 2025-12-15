<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
 require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-5 mb-5 fade-in">
  <div class="card shadow-sm p-4">
    <h2 class="text-primary mb-3">🔒 Privacy Policy</h2>
    <p>Last updated: <?= date('F j, Y'); ?></p>

    <h4>1. Introduction</h4>
    <p>
      At <strong>The Seventh Com</strong>, we value your privacy. This policy explains how we collect,
      use, and protect your personal information when you visit our website or make a purchase.
    </p>

    <h4>2. Information We Collect</h4>
    <ul>
      <li>Personal details like your name, email, phone, and address during checkout or registration.</li>
      <li>Order and payment details for processing your transactions.</li>
      <li>Device, browser, and analytics data to improve your experience.</li>
    </ul>

    <h4>3. How We Use Your Data</h4>
    <ul>
      <li>To process orders and payments.</li>
      <li>To send order updates and promotional offers (optional).</li>
      <li>To enhance user experience and site security.</li>
    </ul>

    <h4>4. Data Security</h4>
    <p>
      We use industry-standard encryption and secure servers to protect your data.
      However, no method of transmission over the Internet is 100% secure.
    </p>

    <h4>5. Your Rights</h4>
    <p>
      You can request correction or deletion of your data anytime by contacting us at
      <a href="mailto:support@theseventhcom.com">support@theseventhcom.com</a>.
    </p>

    <h4>6. Policy Updates</h4>
    <p>
      We may update this policy periodically. Changes will be posted here with a new "Last updated" date.
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
