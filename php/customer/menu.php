<?php
session_start();
require_once '../rdb.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all products
$conn = connect_db();
$result = $conn->query("SELECT * FROM products ORDER BY name");
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $products = [];
    $error = "Error fetching products: " . $conn->error;
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $userId = $_SESSION['user_id'];
    
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE userId = ? AND productId = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingItem = $result->fetch_assoc();
    
    if ($existingItem) {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE userId = ? AND productId = ?");
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        if ($stmt->execute()) {
            $success = "Item added to cart successfully!";
        } else {
            $error = "Error updating cart: " . $conn->error;
        }
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO cart (userId, productId, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        if ($stmt->execute()) {
            $success = "Item added to cart successfully!";
        } else {
            $error = "Error adding to cart: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="menu.php" class="active">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="welcome-box">
            <div class="hero-section">
                <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Our Menu</h1>
                <p>Discover our delicious coffee and treats!</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <section class="menu-section">
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="../../resources/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                <form method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="quantity-selector">
                                        <label for="quantity_<?php echo $product['id']; ?>">Quantity:</label>
                                        <input type="number" id="quantity_<?php echo $product['id']; ?>" name="quantity" value="1" min="1" max="10">
                                    </div>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
