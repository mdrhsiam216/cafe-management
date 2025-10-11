<?php
// Staff active orders with persistent status updates
require_once '../auth_middleware.php';
requireRole(['staff']);
require_once '../rdb.php';
$activeMsg = '';
$updatedStatuses = []; // map order_id => new status (session-local for immediate UI)

// Helper: find staff.id by session user id
function get_staff_id_for_session($conn) {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return null;
    $stmt = $conn->prepare('SELECT id FROM staff WHERE userId = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ? (int)$row['id'] : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $orderId = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
  $status = isset($_POST['status']) ? trim($_POST['status']) : '';
  if ($orderId === '' || !ctype_digit($orderId)) {
    $activeMsg = 'Invalid order id.';
  } elseif ($status === '') {
    $activeMsg = 'Please select a status.';
  } else {
    $orderIdInt = (int)$orderId;
    $conn = connect_db();
    $staffId = get_staff_id_for_session($conn);

    // Update order status and staffId
    $stmt = $conn->prepare('UPDATE orders SET status = ?, staffId = ? WHERE id = ?');
    if ($stmt) {
        // allow null for staffId
        if ($staffId === null) {
            // bind as null integer using 'i' and pass null variable
            $nullStaff = null;
            $stmt->bind_param('sii', $status, $nullStaff, $orderIdInt);
        } else {
            $stmt->bind_param('sii', $status, $staffId, $orderIdInt);
        }
        if ($stmt->execute()) {
            $activeMsg = "Order #" . htmlspecialchars($orderIdInt, ENT_QUOTES) . " updated to " . htmlspecialchars($status, ENT_QUOTES) . ".";
            $updatedStatuses[(string)$orderIdInt] = $status;
        } else {
            $activeMsg = 'Failed to update order: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $activeMsg = 'Failed to prepare update: ' . $conn->error;
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
  <title>Active Orders - Skyline Coffee Shop</title>
  <link rel="stylesheet" href="../../css/staff/staff-active-orders.css" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../css/staff/staff-common.css" />
</head>



<body>
  <?php
  // Optional debug banner: add ?debug=1 to the URL to see total orders in DB
  if (isset($_GET['debug']) && $_GET['debug'] === '1') {
      require_once '../rdb.php';
      $dbgConn = connect_db();
      $cntRes = $dbgConn->query("SELECT COUNT(*) AS cnt FROM orders");
      $cnt = 0;
      if ($cntRes) {
          $row = $cntRes->fetch_assoc();
          $cnt = (int)$row['cnt'];
      }
      $dbgConn->close();
      echo "<div style=\"position:fixed;left:10px;top:10px;background:#fff7e6;padding:8px;border:1px solid #f0a500;z-index:9999;font-size:14px;\">Orders in DB: <strong>" . $cnt . "</strong></div>";
  }
  ?>
  <div class="container">
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="staff-orders.php">Order</a></li>
        <li><a href="staff-active-orders.php">Active Orders</a></li>
        <li><a href="staff-profile.php">Profile</a></li>
        <li><a href="../logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
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
              <th>Customer</th>
              <th>Quantity</th>
              <th>Placed At</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Load orders dynamically from the DB (simple, minimal joins)
            require_once '../rdb.php';
            $conn = connect_db();
            $orders = [];
      $sql = "SELECT o.id, o.quantity, o.status, o.created_at, p.name AS product_name, u.name AS customer_name, s.title AS special_title, o.is_special_offer
        FROM orders o
        LEFT JOIN products p ON o.productId = p.id
        LEFT JOIN users u ON o.userId = u.id
        LEFT JOIN special_offers s ON o.specialOfferId = s.id
        ORDER BY o.created_at DESC";
            $res = $conn->query($sql);
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $orders[] = $r;
                }
            }

            if (empty($orders)) {
                echo '<tr><td colspan="5">No active orders.</td></tr>';
            } else {
                foreach ($orders as $o) {
                    $oid = (int)$o['id'];
                    $okey = (string)$oid; // for $updatedStatuses which stores string keys
                    $itemName = $o['is_special_offer'] ? ($o['special_title'] ?? 'Special Offer') : ($o['product_name'] ?? 'Unknown');
                    $item = htmlspecialchars($itemName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $customer = htmlspecialchars($o['customer_name'] ?? 'Guest', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $qty = (int)($o['quantity'] ?? 0);
                    $placed = isset($o['created_at']) ? htmlspecialchars($o['created_at'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                    $currentStatus = isset($updatedStatuses[$okey]) ? $updatedStatuses[$okey] : ($o['status'] ?? 'pending');
                    // render row
                    echo "<tr>";
                    echo "<td>" . sprintf('%03d', $oid) . "</td>";
                    // show only product name (no thumbnail)
                    echo "<td>" . $item . "</td>";
                    echo "<td>" . $customer . "</td>";
                    echo "<td>" . $qty . "</td>";
                    echo "<td>" . $placed . "</td>";
                    echo "<td>" . htmlspecialchars($currentStatus, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</td>";
                    echo "<td>";
                    echo "<form method=\"post\" action=\"\">";
                    echo "<input type=\"hidden\" name=\"order_id\" value=\"$oid\" />";
                    echo "<select name=\"status\">";
                    // normalize to DB enum values: pending/delivered
                    $options = ['pending' => 'Pending', 'delivered' => 'Delivered'];
                    foreach ($options as $val => $label) {
                        $sel = ($val === $currentStatus) ? ' selected' : '';
                        echo "<option value=\"$val\"$sel>$label</option>";
                    }
                    echo "</select>";
                    echo "<button type=\"submit\" class=\"btn update-btn\">Update</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            }
            $conn->close();
            ?>
          </tbody>
        </table>
      </div>
      <?php if ($activeMsg): ?>
        <p class="error"><?php echo htmlspecialchars($activeMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>