<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/customer.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
               <li><a href="dashboard.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="welcome-box">
            <div class="hero-section">
                <img src="resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Order Placed Successfully!</h1>
                <p>Thank you for your order</p>
            </div>

            <section class="success-section">
                <div class="success-message">
                    <div class="success-icon">✓</div>
                    <h2>Your order has been placed successfully!</h2>
                    <p>We've received your order and will prepare it shortly. You can pay with cash when you pick up your order.</p>
                    
                    <div class="next-steps">
                        <h3>What's Next?</h3>
                        <ul>
                            <li>We'll prepare your order fresh</li>
                            <li>You'll receive a notification when it's ready</li>
                            <li>Come to our shop to pick up and pay</li>
                        </ul>
                    </div>

                    <div class="action-buttons">
                        <a href="menu.php" class="btn btn-primary">Order More</a>
                        <a href="customer/profile.php" class="btn btn-secondary">View Profile</a>
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
