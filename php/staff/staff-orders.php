<?php
require_once '../auth_middleware.php';
requireRole(['staff']);
$orderMsg = '';
// Minimum total items required to place an order; change as needed
$minItems = 1; // set to 1 to require at least one item in the whole order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Accept empty or zero quantities per-item, but require total >= $minItems
  $quantities = [];
  $totalItems = 0;
  $invalidField = false;
  foreach ($_POST as $k => $v) {
    if (strpos($k, 'quantity_') === 0) {
      $idx = substr($k, 9);
      $val = trim($v);
      if ($val === '') {
        // treat empty as zero
        $qty = 0;
      } elseif (!ctype_digit($val)) {
        $invalidField = true;
        break;
      } else {
        $qty = (int)$val;
        if ($qty < 0) {
          $invalidField = true;
          break;
        }
      }
      $quantities[$idx] = $qty;
      $totalItems += $qty;
    }
  }
  if ($invalidField) {
    $orderMsg = 'Please enter valid whole-number quantities (0 or greater).';
  } elseif ($totalItems < $minItems) {
    $orderMsg = 'Please order at least ' . $minItems . ' item' . ($minItems > 1 ? 's' : '') . '.';
  } else {
    // Process the order here (demo)
    $orderMsg = 'Order submitted (demo). Total items: ' . $totalItems . '.';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Take Orders - Skyline Coffee Shop</title>
  <link rel="stylesheet" href="../CSS/staff-orders.css" />
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
          <a href="../logout.php" class="logout-icon" title="Log out" aria-label="Log out" onclick="return confirm('Are you sure you want to logout?');">
            <!-- simple logout SVG icon -->
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
    <div class="orders-box">
      <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo" />
      <h2>Orders</h2>
      <p></p>
      <h3>Menu</h3>
      <div class="menu-section">
        <form method="post" action="">
        <table id="menu-table">
          <thead>
            <tr>
              <th>Photo</th>
              <th>Item</th>
              <th>Description</th>
              <th>Price (BDT)</th>
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <img src="../../resources/coffee.png" alt="Coffee" class="menu-img" />
              </td>
              <td>Coffee</td>
              <td>Rich, aromatic espresso blend</td>
              <td>180</td>
                  <td>
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input type="number" id="quantity_1" name="quantity_1" min="0" value="0" />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <img src="../../resources/Tea.png" alt="Tea" class="menu-img" />
              </td>
              <td>Tea</td>
              <td>Classic milk tea</td>
              <td>120</td>
                  <td>
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input type="number" id="quantity_2" name="quantity_2" min="0" value="0" />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <img src="../../resources/Latte.png" alt="Latte" class="menu-img" />
              </td>
              <td>Latte</td>
              <td>Creamy espresso with steamed milk</td>
              <td>250</td>
                  <td>
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input type="number" id="quantity_3" name="quantity_3" min="0" value="0" />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <img src="../../resources/Croissant.png" alt="Croissant" class="menu-img" />
              </td>
              <td>Croissant</td>
              <td>Freshly baked croissants</td>
              <td>220</td>
                  <td>
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input type="number" id="quantity_4" name="quantity_4" min="0" value="0" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

  <button type="submit" class="btn">Submit Order</button>
  </form>
  <?php if ($orderMsg): ?>
    <p class="error"><?php echo htmlspecialchars($orderMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
  <?php endif; ?>

      
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