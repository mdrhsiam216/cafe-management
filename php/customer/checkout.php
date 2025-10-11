<?php
session_start();
require_once '../rdb.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch cart items
$conn = connect_db();
$stmt = $conn->prepare("
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, 
           (c.quantity * p.price) as subtotal
    FROM cart c 
    JOIN products p ON c.productId = p.id 
    WHERE c.userId = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    if (empty($cartItems)) {
        header('Location: cart.php');
        exit();
    }
    $subtotal = array_sum(array_column($cartItems, 'subtotal'));
} else {
    $error = "Error fetching cart: " . $conn->error;
    $cartItems = [];
    $subtotal = 0;
}
$stmt->close();

// Fetch active coupons
$result = $conn->query("SELECT * FROM coupons WHERE active = 1");
if ($result) {
    $coupons = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $coupons = [];
}

$discount = 0;
$couponId = null;
$total = $subtotal;

// Handle coupon application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = $_POST['coupon_code'];
    
    foreach ($coupons as $coupon) {
        if ($coupon['couponString'] === $couponCode) {
            $discount = ($subtotal * $coupon['percentage']) / 100;
            $couponId = $coupon['id'];
            $total = $subtotal - $discount;
            $success = "Coupon applied successfully!";
            break;
        }
    }
    
    if (!$couponId) {
        $error = "Invalid coupon code!";
    }
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // prepare a debug log directory
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/order_debug.log';

    // sanitize and validate payment method from form
    $paymentMethod = '';
    if (isset($_POST['payment_method']) && $_POST['payment_method'] !== '') {
        $paymentMethod = trim($_POST['payment_method']);
    } elseif (isset($_POST['payment'])) {
        $paymentMethod = trim($_POST['payment']);
    }
    $allowedMethods = ['cash', 'bkash', 'card'];
    if (!in_array($paymentMethod, $allowedMethods, true)) {
        $paymentMethod = 'cash';
    }

    $conn->begin_transaction();
    try {
        // Create orders for each cart item
        // Handle nullable coupon: prepare two statements depending on whether coupon applied
        if ($couponId) {
            $insertSql = "INSERT INTO orders (userId, productId, quantity, couponId, payment_method, status) VALUES (?, ?, ?, ?, ?, 'pending')";
            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) throw new Exception('Prepare failed: ' . $conn->error);
            foreach ($cartItems as $item) {
                // types: userId (i), productId (i), quantity (i), couponId (i), payment_method (s)
                $ok = $insertStmt->bind_param("iiiis", $userId, $item['product_id'], $item['quantity'], $couponId, $paymentMethod);
                if ($ok === false) throw new Exception('Bind failed: ' . $insertStmt->error);
                if (!$insertStmt->execute()) throw new Exception('Execute failed: ' . $insertStmt->error);
            }
            $insertStmt->close();
        } else {
            $insertSql = "INSERT INTO orders (userId, productId, quantity, payment_method, status) VALUES (?, ?, ?, ?, 'pending')";
            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) throw new Exception('Prepare failed: ' . $conn->error);
            foreach ($cartItems as $item) {
                // types: userId (i), productId (i), quantity (i), payment_method (s)
                $ok = $insertStmt->bind_param("iiis", $userId, $item['product_id'], $item['quantity'], $paymentMethod);
                if ($ok === false) throw new Exception('Bind failed: ' . $insertStmt->error);
                if (!$insertStmt->execute()) throw new Exception('Execute failed: ' . $insertStmt->error);
            }
            $insertStmt->close();
        }

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE userId = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
        $stmt->close();

        $conn->commit();

        // Debug log success
        $entry = [
            'ts' => date('c'),
            'userId' => $userId,
            'couponId' => $couponId,
            'cartItems' => $cartItems,
            'result' => 'success'
        ];
        @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);

        header('Location: order-success.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error placing order: " . $e->getMessage();
        // Debug log error
        $entry = [
            'ts' => date('c'),
            'userId' => $userId,
            'couponId' => $couponId,
            'cartItems' => $cartItems,
            'result' => 'error',
            'message' => $e->getMessage()
        ];
        @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="welcome-box">
            <div class="hero-section">
                <img src="resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Checkout</h1>
                <p>Complete your order</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <section class="checkout-section">
                <div class="checkout-container">
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item">
                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                                    <span class="item-price">$<?php echo number_format($item['subtotal'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-totals">
                            <div class="total-line">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <?php if ($discount > 0): ?>
                                <div class="total-line discount">
                                    <span>Discount:</span>
                                    <span>-$<?php echo number_format($discount, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="total-line final-total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-section">
                        <h2>Apply Coupon</h2>
                        <form method="POST" class="coupon-form">
                            <div class="form-group">
                                <input type="text" name="coupon_code" placeholder="Enter coupon code" value="<?php echo isset($_POST['coupon_code']) ? htmlspecialchars($_POST['coupon_code']) : ''; ?>">
                                <button type="submit" name="apply_coupon" class="btn btn-secondary">Apply</button>
                            </div>
                        </form>

                        <h2>Payment Method</h2>
                        <div class="payment-method">
                            <div class="payment-option">
                                <input type="radio" id="cash" name="payment" value="cash" checked>
                                <label for="cash">Cash Payment</label>
                                <p class="payment-note">Pay when you pick up your order</p>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="bkash" name="payment" value="bkash">
                                <label for="bkash">Bkash</label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="card" name="payment" value="card">
                                <label for="card">Card</label>
                            </div>
                        </div>

                        <form method="POST" class="place-order-form">
                            <?php if ($couponId): ?>
                                <input type="hidden" name="coupon_id" value="<?php echo $couponId; ?>">
                            <?php endif; ?>
                            <!-- Mirror selected method into payment_method for server-side processing -->
                            <input type="hidden" name="payment_method" value="">
                            <button type="submit" name="place_order" class="btn btn-primary btn-large">Place Order</button>
                        </form>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <div class="footer-content">
                    <div class="footer-section">
                        <h3>Contact Us</h3>
                        <p>Email: <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a></p>
                        <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                        <p>Address: 123 Skyline Avenue, Dhaka</p>
                    </div>
                    <div class="footer-section">
                        <h3>About Us</h3>
                        <p>We are passionate about serving the finest coffee, crafted with love and expertise. Join us for a unique coffee experience!</p>
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
