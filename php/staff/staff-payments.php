<?php
// Staff payments processing (persist to orders)
require_once '../auth_middleware.php';
requireRole(['staff']);
require_once '../rdb.php';
$paymentMsg = '';
$lastOrderId = '';
$lastMethod = '';
$processedOrderId = 0;
$processedMethod = '';

// helper to get staff id
function get_staff_id_for_session_payment($conn) {
    $uid = $_SESSION['user_id'] ?? null;
    if (!$uid) return null;
    $st = $conn->prepare('SELECT id FROM staff WHERE userId = ? LIMIT 1');
    if (!$st) return null;
    $st->bind_param('i', $uid);
    $st->execute();
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $st->close();
    return $row ? (int)$row['id'] : null;
}

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
  $conn = connect_db();
  $staffId = get_staff_id_for_session_payment($conn);
  $orderIdInt = (int)$orderId;
  $methodToSave = $lastMethod !== '' ? $lastMethod : null;

  // Update order: set status to 'delivered', record staffId and payment_method
    // Update order: set status to 'delivered'. If the DB has payment_method column, update it too.
    $statusToSet = 'delivered';
    $methodToSave = $methodToSave !== null ? $methodToSave : null;

    // detect whether payment_method column exists
    $hasPaymentMethod = false;
    $colCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
    if ($colCheck && $colCheck->num_rows > 0) {
      $hasPaymentMethod = true;
    } else {
      // Try to add the column automatically (best-effort). This helps testers who haven't migrated the DB.
      $addRes = $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER couponId");
      if ($addRes !== false) {
        $hasPaymentMethod = true;
      } else {
        // record an error for debugging but continue (we will do an update without payment_method)
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
          echo '<div style="background:#fee;padding:8px;border:1px solid #f00;margin:10px 0;">Failed to add payment_method column: ' . htmlspecialchars($conn->error, ENT_QUOTES) . '</div>';
        }
      }
    }

    // Build appropriate update depending on whether payment_method exists and staffId is null
    if ($hasPaymentMethod) {
      if ($staffId === null) {
        // staffId is NULL in DB
        $sql = "UPDATE orders SET status = ?, staffId = NULL, payment_method = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
          // status (s), payment_method (s), id (i)
          $stmt->bind_param('ssi', $statusToSet, $methodToSave, $orderIdInt);
        }
      } else {
        $sql = "UPDATE orders SET status = ?, staffId = ?, payment_method = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
          // status (s), staffId (i), payment_method (s), id (i)
          $stmt->bind_param('sisi', $statusToSet, $staffId, $methodToSave, $orderIdInt);
        }
      }
    } else {
      if ($staffId === null) {
        $sql = "UPDATE orders SET status = ?, staffId = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
          // status (s), id (i)
          $stmt->bind_param('si', $statusToSet, $orderIdInt);
        }
      } else {
        $sql = "UPDATE orders SET status = ?, staffId = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
          // status (s), staffId (i), id (i)
          $stmt->bind_param('sii', $statusToSet, $staffId, $orderIdInt);
        }
      }
    }

    if ($stmt) {
      if ($stmt->execute()) {
        // success: redirect to GET so the refreshed page shows updated DB values (Post-Redirect-Get)
        $stmt->close();
        $conn->close();
        $safeMethod = rawurlencode($methodToSave ?? '');
        header('Location: staff-payments.php?processed=' . (int)$orderIdInt . '&method=' . $safeMethod);
        exit();
      } else {
        $paymentMsg = 'Failed to update order: ' . $stmt->error;
      }
      $stmt->close();
    } else {
      $paymentMsg = 'Failed to prepare update: ' . $conn->error;
    }
  $conn->close();
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
    <div class="payments-box">
      <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo" />
      <h2>Process Payments</h2>
      <p>Manage customer payments for orders.</p>
      <?php
  // Fetch recent orders with product price to compute amounts
    $conn = connect_db();
  // read processed flash (if redirected after processing)
  $processedOrderId = isset($_GET['processed']) ? (int)$_GET['processed'] : 0;
  $processedMethod = isset($_GET['method']) ? rawurldecode($_GET['method']) : '';
    // First attempt: include payment_method (newer schema). If that fails (older DB), fall back to a query without it.
    $orders = [];
    $ordersQueryWithMethod = "SELECT o.id, o.quantity, o.status, o.payment_method, o.created_at, p.name AS product_name, p.price, u.name AS customer_name
            FROM orders o
            LEFT JOIN products p ON o.productId = p.id
            LEFT JOIN users u ON o.userId = u.id
            ORDER BY o.created_at DESC LIMIT 30";
    $ordersRes = $conn->query($ordersQueryWithMethod);
    if ($ordersRes === false) {
      // fallback for databases that don't yet have payment_method column
      $fallbackQuery = "SELECT o.id, o.quantity, o.status, o.created_at, p.name AS product_name, p.price, u.name AS customer_name
            FROM orders o
            LEFT JOIN products p ON o.productId = p.id
            LEFT JOIN users u ON o.userId = u.id
            ORDER BY o.created_at DESC LIMIT 30";
      $ordersRes = $conn->query($fallbackQuery);
      if ($ordersRes === false) {
        // optional debug output when requested
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
          echo '<div style="background:#fee;padding:8px;border:1px solid #f00;margin:10px 0;">DB error: ' . htmlspecialchars($conn->error, ENT_QUOTES) . '</div>';
        }
      } else {
        while ($or = $ordersRes->fetch_assoc()) {
          // ensure payment_method key exists for downstream code
          $or['payment_method'] = null;
          $orders[] = $or;
        }
      }
    } else {
      while ($or = $ordersRes->fetch_assoc()) {
        $orders[] = $or;
      }
    }
    $conn->close();

    if (empty($orders)) {
      echo '<p>No orders found.</p>';
    } else {
      // show a global processed banner when redirected after successful processing
      if ($processedOrderId > 0) {
        $bannerMethod = $processedMethod ? htmlspecialchars($processedMethod, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'Unknown';
        echo '<div class="processed-banner">Payment done for Order ' . sprintf('%03d', $processedOrderId) . ' using ' . $bannerMethod . '.</div>';
      }

      echo '<div class="payment-cards-row">';
      foreach ($orders as $ord) {
        $oid = (int)$ord['id'];
        $amount = number_format((float)$ord['price'] * (int)$ord['quantity'], 2);
        $statusRaw = $ord['status'] ?? 'pending';
        $status = htmlspecialchars($statusRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cust = htmlspecialchars($ord['customer_name'] ?? 'Guest', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $pmRaw = isset($ord['payment_method']) && $ord['payment_method'] !== null ? $ord['payment_method'] : null;
        $pm = $pmRaw !== null ? htmlspecialchars($pmRaw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : null;

        // Determine paid/unpaid: consider an order paid if status is 'delivered' and payment_method is set
        $isPaid = (strtolower((string)$statusRaw) === 'delivered' && $pmRaw !== null && $pmRaw !== '');

        echo '<div class="payment-card">';
        echo '<form method="post" action="">';
        echo "<span>Order ID: " . sprintf('%03d', $oid) . "</span>";
        echo "<span>Customer: $cust</span>";
        echo "<span>Amount: $amount</span>";
        // show existing method if present
        if ($pm !== null) {
          echo "<span>Method: $pm</span>";
        } else {
          echo "<span>Method: Not set</span>";
        }
        echo "<span>Status: $status</span>";

        // show paid/unpaid badge
        if ($isPaid) {
          echo '<span class="badge paid">Paid</span>';
        } else {
          echo '<span class="badge unpaid">Unpaid</span>';
        }

        echo '<div class="payment-methods">';
        echo '<label class="radio-icon"><input type="radio" name="method" value="cash"' . ($pmRaw === 'cash' ? ' checked' : '') . '>Cash</label>';
        echo '<label class="radio-icon"><input type="radio" name="method" value="bkash"' . ($pmRaw === 'bkash' ? ' checked' : '') . '>Bkash</label>';
        echo '<label class="radio-icon"><input type="radio" name="method" value="card"' . ($pmRaw === 'card' ? ' checked' : '') . '>Card</label>';
        echo '</div>';

        echo "<input type=\"hidden\" name=\"order_id\" value=\"$oid\" />";
        // Allow processing even when status is 'delivered'. Show 'Reprocess' label in that case.
        $btnLabel = (strtolower((string)$statusRaw) === 'delivered') ? 'Reprocess' : 'Process';
        echo "<button type=\"submit\" class=\"btn process-btn\">$btnLabel</button>";

        // show per-order message if applicable
        if ($lastOrderId && (string)$lastOrderId === (string)$oid && $paymentMsg) {
          echo '<p class="error">' . htmlspecialchars($paymentMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }

        echo '</form>';
        echo '</div>';
      }
      echo '</div>';
    }
      ?>
        <!-- static sample cards removed; page now only displays orders from DB -->
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