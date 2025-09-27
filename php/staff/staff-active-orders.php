<?php
// Demo server-side handling for updating order status
session_start();
$activeMsg = '';
$updatedStatuses = []; // map order_id => new status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $orderId = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
  $status = isset($_POST['status']) ? trim($_POST['status']) : '';
  if ($orderId === '' || !ctype_digit($orderId)) {
    $activeMsg = 'Invalid order id.';
  } elseif ($status === '') {
    $activeMsg = 'Please select a status.';
  } else {
    $safeStatus = htmlspecialchars($status, ENT_QUOTES);
    $activeMsg = "Order #" . htmlspecialchars($orderId, ENT_QUOTES) . " updated to " . $safeStatus . " (demo).";
    $updatedStatuses[$orderId] = $status;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Active Orders - Skyline Coffee Shop</title>
  <link rel="stylesheet" href="../CSS/staff-active-orders.css" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
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
    <div class="orders-box">
      <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo" />
      <h2>Active Orders</h2>
      <p>View and manage active customer orders.</p>
      <div class="order-list">
        <table id="order-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Item</th>
              <th>Quantity</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>001</td>
              <td>Tea</td>
              <td>3</td>
              <td><?php echo isset($updatedStatuses['1']) ? htmlspecialchars($updatedStatuses['1'], ENT_QUOTES) : 'Delivered'; ?></td>
              <td>
                <form method="post" action="">
                  <input type="hidden" name="order_id" value="1" />
                  <select name="status">
                    <option value="Delivered">Delivered</option>
                    <option value="Preparing">Preparing</option>
                    <option value="Ready">Ready</option>
                  </select>
                  <button type="submit" class="btn update-btn">Update</button>
                </form>
              </td>
            </tr>
            <tr>
              <td>002</td>
              <td>Coffee</td>
              <td>2</td>
              <td><?php echo isset($updatedStatuses['2']) ? htmlspecialchars($updatedStatuses['2'], ENT_QUOTES) : 'Preparing'; ?></td>
              <td>
                <form method="post" action="">
                  <input type="hidden" name="order_id" value="2" />
                  <select name="status">
                    <option value="Preparing">Preparing</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Ready">Ready</option>
                  </select>
                  <button type="submit" class="btn update-btn">Update</button>
                </form>
              </td>
            </tr>
            <tr>
              <td>003</td>
              <td>Latte</td>
              <td>1</td>
              <td><?php echo isset($updatedStatuses['3']) ? htmlspecialchars($updatedStatuses['3'], ENT_QUOTES) : 'Ready'; ?></td>
              <td>
                <form method="post" action="">
                  <input type="hidden" name="order_id" value="3" />
                  <select name="status">
                    <option value="Ready">Ready</option>
                    <option value="Preparing">Preparing</option>
                    <option value="Delivered">Delivered</option>
                  </select>
                  <button type="submit" class="btn update-btn">Update</button>
                </form>
              </td>
            </tr>
            <tr>
              <td>004</td>
              <td>Croissant</td>
              <td>2</td>
              <td><?php echo isset($updatedStatuses['4']) ? htmlspecialchars($updatedStatuses['4'], ENT_QUOTES) : 'Preparing'; ?></td>
              <td>
                <form method="post" action="">
                  <input type="hidden" name="order_id" value="4" />
                  <select name="status">
                    <option value="Preparing">Preparing</option>
                    <option value="Ready">Ready</option>
                    <option value="Delivered">Delivered</option>
                  </select>
                  <button type="submit" class="btn update-btn">Update</button>
                </form>
              </td>
            </tr>

          </tbody>
        </table>
      </div>
      <?php if ($activeMsg): ?>
        <p class="error"><?php echo htmlspecialchars($activeMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
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