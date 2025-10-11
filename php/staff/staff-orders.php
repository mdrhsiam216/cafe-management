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
    // Persist order into orders table
    require_once '../rdb.php';
    $conn = connect_db();
    $conn->begin_transaction();
    try {
      $insert = $conn->prepare("INSERT INTO orders (userId, productId, quantity, status, payment_method) VALUES (?, ?, ?, 'pending', ?) ");
      if (!$insert) throw new Exception('Prepare failed: ' . $conn->error);
      // staff-created orders have no userId (NULL)
      $nullUser = null;
      foreach ($quantities as $pid => $qty) {
        if ($qty <= 0) continue;
        $paymentMethod = 'cash';
        // bind: userId (i) as null -> use 'i' with NULL via bind_param requires workaround: use s and pass null string? We'll pass null using bind_param with 'isss' and set first param to null via null coalescing
        // Simpler: set userId to NULL by using explicit NULL in query when binding is awkward
        $sql = "INSERT INTO orders (userId, productId, quantity, status, payment_method) VALUES (NULL, ?, ?, 'pending', ?)";
        $st = $conn->prepare($sql);
        if (!$st) throw new Exception('Prepare failed: ' . $conn->error);
        $st->bind_param('iis', $pid, $qty, $paymentMethod);
        if (!$st->execute()) throw new Exception('Execute failed: ' . $st->error);
        $st->close();
      }
      $conn->commit();
      $conn->close();
      header('Location: staff-active-orders.php');
      exit();
    } catch (Exception $e) {
      $conn->rollback();
      $orderMsg = 'Failed to place order: ' . $e->getMessage();
      $conn->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Take Orders - Skyline Coffee Shop</title>
  <link rel="stylesheet" href="../../css/staff/staff-orders.css" />
  <link rel="stylesheet" href="../../css/staff/staff-common.css" />
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
        <li><a href="../logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
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
              <th>Item</th>
              <th>Description</th>
              <th>Price (BDT)</th>
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Fetch products from DB and render rows
            require_once '../rdb.php';
            $conn = connect_db();
            $products = [];
            $res = $conn->query("SELECT id, name, price, image FROM products ORDER BY id ASC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $products[] = $row;
                }
            }

      if (empty($products)) {
        echo '<tr><td colspan="4">No products available.</td></tr>';
      } else {
        foreach ($products as $p) {
          $pid = (int)$p['id'];
          $pname = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
          $price = htmlspecialchars($p['price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
          $imageRel = $p['image'] ? $p['image'] : null; // stored like 'uploads/products/xxx.jpg'
          // Build path under resources. Check server-side if file exists; if not, use a fallback image.
          $candidate = $imageRel ? __DIR__ . '/../../resources/' . $imageRel : null;
          if ($candidate && file_exists($candidate)) {
            $imgUrl = '../../resources/' . $imageRel;
          } else {
            // fallback product image
            $imgUrl = '../../resources/coffee.png';
          }
          $escapedImg = htmlspecialchars($imgUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
          echo "<tr>";
          echo "<td>$pname</td>";
          echo "<td>&nbsp;</td>"; // description column left empty for now
          echo "<td>$price</td>";
          echo "<td>\n<div class=\"form-group\">\n<label for=\"quantity_$pid\">Quantity</label>\n";
          echo "<input type=\"number\" id=\"quantity_$pid\" name=\"quantity_$pid\" min=\"0\" value=\"0\" />\n</div>\n</td>";
          echo "</tr>";
        }
            }
            $conn->close();
            ?>
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