<?php
// Demo server-side validation for payment processing actions
session_start();
$paymentMsg = '';
$lastOrderId = '';
$lastMethod = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $orderId = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
  $method = isset($_POST['method']) ? trim($_POST['method']) : '';
  $lastOrderId = $orderId;
  $lastMethod = $method !== '' ? strtolower($method) : '';

  if ($orderId === '' || !ctype_digit($orderId)) {
    $paymentMsg = 'Invalid order ID.';
  } elseif ($lastMethod === '' || !in_array($lastMethod, ['cash', 'bkash', 'card'], true)) {
    $paymentMsg = 'Please select a payment method (Cash, Bkash or Card).';
  } else {
    $paymentMsg = "Payment for order #" . htmlspecialchars($orderId, ENT_QUOTES) . " processed (demo).";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Process Payments - Skyline Coffee Shop</title>
  <link rel="stylesheet" href="../../css/staff/staff-payments.css" />
</head>

<body>
  <div class="container">
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="staff-orders.php">Order</a></li>
        <li><a href="staff-payments.php">Payments</a></li>
        <li><a href="staff-active-orders.php">Active Orders</a></li>
        <li><a href="#about-section">About</a></li>
        <li><a href="#contact-section">Contact</a></li>
        <li><a href="staff-profile.php">Profile</a></li>
        <li>
          <a href="staff-login.php" class="logout-icon" title="Log out" aria-label="Log out">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
              aria-hidden="true">
              <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
              <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
              <path d="M13 19H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h7" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </a>
        </li>
      </ul>
    </nav>
    <div class="payments-box">
      <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo" />
      <h2>Process Payments</h2>
      <p>Manage customer payments for orders.</p>
      <div class="payment-cards-row">
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 001</span>
            <span>Amount: 360 BDT</span>
            <span>Status: Pending</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="001" />
            <button type="submit" class="btn process-btn">Process</button>
            <?php if ($lastOrderId === '001' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 002</span>
            <span>Amount: 250 BDT</span>
            <span>Status: Completed</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="002" />
            <button type="submit" class="btn process-btn" disabled>Processed</button>
            <?php if ($lastOrderId === '002' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 003</span>
            <span>Amount: 440 BDT</span>
            <span>Status: Pending</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="003" />
            <button type="submit" class="btn process-btn">Process</button>
            <?php if ($lastOrderId === '003' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
      </div>
      <div class="payment-cards-row">
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 004</span>
            <span>Amount: 180 BDT</span>
            <span>Status: Pending</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="004" />
            <button type="submit" class="btn process-btn">Process</button>
            <?php if ($lastOrderId === '004' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 005</span>
            <span>Amount: 520 BDT</span>
            <span>Status: Completed</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="005" />
            <button type="submit" class="btn process-btn" disabled>Processed</button>
            <?php if ($lastOrderId === '005' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 006</span>
            <span>Amount: 210 BDT</span>
            <span>Status: Pending</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="006" />
            <button type="submit" class="btn process-btn">Process</button>
            <?php if ($lastOrderId === '006' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
      </div>
      <div class="payment-cards-row">
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 007</span>
            <span>Amount: 390 BDT</span>
            <span>Status: Completed</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="007" />
            <button type="submit" class="btn process-btn" disabled>Processed</button>
            <?php if ($lastOrderId === '007' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 008</span>
            <span>Amount: 310 BDT</span>
            <span>Status: Pending</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="008" />
            <button type="submit" class="btn process-btn">Process</button>
            <?php if ($lastOrderId === '008' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <div class="payment-card">
          <form method="post" action="">
            <span>Order ID: 009</span>
            <span>Amount: 470 BDT</span>
            <span>Status: Pending</span>
            <div class="payment-methods">
              <label class="radio-icon">
                <input type="radio" name="method" value="cash">
                <img src="../../resources/Cash.png" alt="Cash" title="Cash" onerror="this.style.display='none'">
                Cash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="bkash">
                <img src="../../resources/BKash.png" alt="Bkash" title="Bkash" onerror="this.style.display='none'">
                Bkash
              </label>
              <label class="radio-icon">
                <input type="radio" name="method" value="card">
                <img src="../../resources/atm-card.png" alt="Card" title="Card" onerror="this.style.display='none'">
                Card
              </label>
            </div>
            <input type="hidden" name="order_id" value="009" />
            <button type="submit" class="btn process-btn">Process</button>
            <?php if ($lastOrderId === '009' && $paymentMsg): ?>
              <p class="error"><?php echo htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <?php endif; ?>
          </form>
        </div>
        <!-- per-order messages displayed inside each payment card -->
      </div>
      <footer class="footer">
        <div class="footer-content">
          <div class="footer-section" id="contact-section">
            <h3>Contact Us</h3>
            <p>
              Email:
              <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a>
            </p>
            <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
            <p>Address: 123 Skyline Avenue, Dhaka</p>
          </div>
          <div class="footer-section" id="about-section">
            <h3>About Us</h3>
            <p>
              We are passionate about serving the finest coffee, crafted with
              love and expertise. Join us for a unique coffee experience!
            </p>
          </div>
          <div class="footer-section">
            <h3>Newsletter</h3>
            <p>Subscribe for exclusive offers!</p>
            <input type="email" placeholder="Enter your email" class="newsletter-input" />
            <button class="btn newsletter-btn">Subscribe</button>
          </div>
          <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-links">
              <a href="https://facebook.com" class="social-icon" aria-label="Facebook">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo"
                  class="social-logo" />
              </a>
              <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo"
                  class="social-logo" />
              </a>
              <a href="https://x.com" class="social-icon" aria-label="X">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo" />
              </a>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <p>Skyline Coffee Shop - Where Every Sip Tells a Story</p>
          <p>&copy; 2025 Skyline Coffee Shop. All rights reserved.</p>
        </div>
      </footer>
    </div>
  </div>
</body>

</html>