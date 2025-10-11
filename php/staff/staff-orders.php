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
    // Resolve staff.id from staff table using session user id (to satisfy FK)
    $staffUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $staffIdToSave = null;
    if ($staffUserId !== null) {
      $tmp = $conn->prepare('SELECT id FROM staff WHERE userId = ? LIMIT 1');
      if ($tmp) {
        $tmp->bind_param('i', $staffUserId);
        $tmp->execute();
        $resTmp = $tmp->get_result();
        $rowTmp = $resTmp ? $resTmp->fetch_assoc() : null;
        if ($rowTmp) $staffIdToSave = (int)$rowTmp['id'];
        $tmp->close();
      }
    }

    foreach ($quantities as $pid => $qty) {
        if ($qty <= 0) continue;
        $paymentMethod = 'cash';

    if ($staffIdToSave === null) {
      // no staff mapping found: insert with NULL staffId
      $sql = "INSERT INTO orders (staffId, productId, quantity, status, payment_method) VALUES (NULL, ?, ?, 'pending', ?)";
      $st = $conn->prepare($sql);
      if (!$st) throw new Exception('Prepare failed: ' . $conn->error);
      $st->bind_param('iis', $pid, $qty, $paymentMethod);
    } else {
      // insert using resolved staff.id to satisfy FK
      $sql = "INSERT INTO orders (staffId, productId, quantity, status, payment_method) VALUES (?, ?, ?, 'pending', ?)";
      $st = $conn->prepare($sql);
      if (!$st) throw new Exception('Prepare failed: ' . $conn->error);
      $st->bind_param('iiis', $staffIdToSave, $pid, $qty, $paymentMethod);
    }

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
        <li><a href="staff-active-orders.php">Active Orders</a></li>
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

      
      
    </div>
  </div>
</body>

</html>