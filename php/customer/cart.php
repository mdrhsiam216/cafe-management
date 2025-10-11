<?php
session_start();
require_once '../rdb.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connect_db();
    
    if (isset($_POST['update_cart'])) {
        $cartId = $_POST['cart_id'];
        $quantity = $_POST['quantity'];
        
        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND userId = ?");
            $stmt->bind_param("iii", $quantity, $cartId, $userId);
            if ($stmt->execute()) {
                $success = "Cart updated successfully!";
            } else {
                $error = "Error updating cart: " . $conn->error;
            }
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND userId = ?");
            $stmt->bind_param("ii", $cartId, $userId);
            if ($stmt->execute()) {
                $success = "Item removed from cart!";
            } else {
                $error = "Error removing item: " . $conn->error;
            }
        }
        $stmt->close();
    }
    
    if (isset($_POST['remove_item'])) {
        $cartId = $_POST['cart_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND userId = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        if ($stmt->execute()) {
            $success = "Item removed from cart!";
        } else {
            $error = "Error removing item: " . $conn->error;
        }
        $stmt->close();
    }
    
    $conn->close();
    
    // Redirect to avoid form resubmission
    header("Location: cart.php");
    exit();
}

// Fetch cart items
$conn = connect_db();
$stmt = $conn->prepare("
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, 
           (c.quantity * p.price) as subtotal
    FROM cart c 
    JOIN products p ON c.productId = p.id 
    WHERE c.userId = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $total = array_sum(array_column($cartItems, 'subtotal'));
} else {
    $cartItems = [];
    $total = 0;
    $error = "Error fetching cart: " . $conn->error;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php" class="active">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="welcome-box">
            <div class="hero-section">
                <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Your Cart</h1>
                <p>Review your order before checkout</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <section class="cart-section">
                <?php if (empty($cartItems)): ?>
                    <div class="empty-cart">
                        <h2>Your cart is empty</h2>
                        <p>Browse our menu to add some delicious items!</p>
                        <a href="menu.php" class="btn btn-primary">View Menu</a>
                    </div>
                <?php else: ?>
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="../../resources/<?php echo htmlspecialchars($item['image']); ?>" alt="photo">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="item-controls">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <div class="quantity-controls">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" max="10">
                                            <button type="submit" name="update_cart" class="btn btn-small">Update</button>
                                        </div>
                                    </form>
                                    <form method="POST" class="remove-form">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger btn-small">Remove</button>
                                    </form>
                                </div>
                                <div class="item-subtotal">
                                    <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="total-section">
                            <h3>Total: $<?php echo number_format($total, 2); ?></h3>
                            <div class="checkout-actions">
                                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
