<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-5 mb-5 fade-in">
  <div class="card shadow-sm p-4">
    <h2 class="text-primary mb-4 text-center">📜 Terms, Privacy & Policies</h2>
    <p class="text-muted text-center mb-5">Last updated: <?= date('F j, Y'); ?></p>

    <!-- 🔹 TERMS AND CONDITIONS -->
    <h4 class="mb-3 text-dark">1. Terms & Conditions</h4>
    <p>
      By accessing and using <strong>The Seventh Com</strong> website, you agree to the following terms.
      If you do not agree with any part, please discontinue use of our services.
    </p>

    <h5 class="mt-3 text-secondary">1.1 Products & Pricing</h5>
    <ul>
      <li>All prices are displayed in Indian Rupees (₹) and include applicable taxes.</li>
      <li>We reserve the right to modify prices, discounts, and product details at any time without prior notice.</li>
    </ul>

    <h5 class="mt-3 text-secondary">1.2 Orders & Payments</h5>
    <ul>
      <li>Orders will be processed only upon confirmation of payment (for prepaid orders).</li>
      <li>We reserve the right to cancel or reject any order due to stock issues, pricing errors, or suspected fraud.</li>
    </ul>

    <h5 class="mt-3 text-secondary">1.3 Liability</h5>
    <p>
      We strive for accuracy in product descriptions, but <strong>The Seventh Com</strong> is not responsible for
      typographical errors or discrepancies. We are not liable for any indirect or consequential damages.
    </p>

    <hr class="my-4">

    <!-- 🔹 PRIVACY POLICY -->
    <h4 class="mb-3 text-dark">2. Privacy Policy</h4>
    <p>
      We value your privacy and are committed to protecting your personal information.
      This section outlines how we collect, use, and safeguard your data.
    </p>

    <ul>
      <li>We collect information like your name, email, phone, and address for orders and support.</li>
      <li>We never sell or share your data with third parties without your consent.</li>
      <li>Data is stored securely and accessed only by authorized personnel.</li>
      <li>You can request deletion or correction of your data anytime by contacting us.</li>
    </ul>

    <p>For privacy-related queries, reach us at 
      <a href="mailto:support@theseventhcom.com" class="text-primary">support@theseventhcom.com</a>.
    </p>

    <hr class="my-4">

    <!-- 🔹 CASH ON DELIVERY POLICY -->
    <h4 class="mb-3 text-dark">3. Cash on Delivery (COD) Policy</h4>
    <ul>
      <li>COD is available on select products and locations as shown during checkout.</li>
      <li>All COD orders are confirmed via call/SMS before dispatch.</li>
      <li>Refusal of multiple COD deliveries may lead to account suspension.</li>
      <li>Refunds for COD orders (on returns) are processed via bank transfer within 5–7 working days.</li>
    </ul>

    <hr class="my-4">

    <!-- 🔹 RETURNS & REFUNDS -->
    <h4 class="mb-3 text-dark">4. Returns & Refunds</h4>
    <ul>
      <li>Returns are accepted within <strong>7 days</strong> of delivery for unused and undamaged items.</li>
      <li>Refunds are processed within 5–7 business days after product inspection.</li>
      <li>Shipping costs are non-refundable unless the return is due to a damaged or incorrect item.</li>
    </ul>

    <p>Contact us for return assistance: 
      <a href="mailto:returns@theseventhcom.com" class="text-primary">returns@theseventhcom.com</a>.
    </p>

    <hr class="my-4">

    <!-- 🔹 SHIPPING POLICY -->
    <h4 class="mb-3 text-dark">5. Shipping & Delivery</h4>
    <ul>
      <li>Orders are usually processed within 1–2 working days and delivered within 5–7 days depending on your location.</li>
      <li>Tracking details will be shared via email/SMS after dispatch.</li>
      <li>Delays may occur due to weather, courier issues, or unforeseen circumstances.</li>
    </ul>

    <hr class="my-4">

    <!-- 🔹 CONTACT & GOVERNING LAW -->
    <h4 class="mb-3 text-dark">6. Contact & Governing Law</h4>
    <p>
      All disputes are subject to the jurisdiction of courts in <strong>Hyderabad, Telangana, India</strong>.
      For queries, email us at <a href="mailto:support@theseventhcom.com" class="text-primary">support@theseventhcom.com</a>.
    </p>
  </div>
</div>

<style>
  .card {
    border-radius: 10px;
    line-height: 1.7;
  }
  h4 {
    border-left: 4px solid #0d6efd;
    padding-left: 8px;
  }
  ul {
    margin-left: 20px;
  }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
