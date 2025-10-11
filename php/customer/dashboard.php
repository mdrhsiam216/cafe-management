<?php
session_start();
require_once '../rdb.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = connect_db();

// Get user information
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get recent orders
$stmt = $conn->prepare("
    SELECT o.id, o.quantity, o.created_at, p.name as product_name, p.price,
           COALESCE(o.status, 'pending') as status
    FROM orders o 
    JOIN products p ON o.productId = p.id 
    WHERE o.userId = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recent_orders = $result->fetch_all(MYSQLI_ASSOC);

// Get cart count
$stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE userId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_data = $result->fetch_assoc();
$cart_count = $cart_data['cart_count'] ?: 0;

// Get total orders count
$stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE userId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order_data = $result->fetch_assoc();
$total_orders = $order_data['total_orders'];

// Calculate total spent
$stmt = $conn->prepare("
    SELECT SUM(o.quantity * p.price) as total_spent 
    FROM orders o 
    JOIN products p ON o.productId = p.id 
    WHERE o.userId = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_data = $result->fetch_assoc();
$total_spent = $total_data['total_spent'] ?: 0;

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../../index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart (<?php echo $cart_count; ?>)</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="dashboard-container">
            <section class="hero-section">
                <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Your personal coffee journey continues here</p>
            </section>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $total_orders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3><?php echo $cart_count; ?></h3>
                        <p>Items in Cart</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                        <p>Total Spent</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-content">
                <aside class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-buttons">
                        <a href="menu.php" class="action-btn">
                            <div class="action-icon">☕</div>
                            <span>Browse Menu</span>
                        </a>
                        <a href="cart.php" class="action-btn">
                            <div class="action-icon">🛒</div>
                            <span>View Cart</span>
                        </a>
                        <a href="profile.php" class="action-btn">
                            <div class="action-icon">👤</div>
                            <span>Edit Profile</span>
                        </a>
                    </div>
                </aside>

                <div class="recent-orders">
                    <h2>Recent Orders</h2>
                    <?php if (empty($recent_orders)): ?>
                        <div class="empty-state">
                            <p>No orders yet. <a href="../menu.php">Start ordering now!</a></p>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-details">
                                        <div class="order-info">
                                            <h4><?php echo htmlspecialchars($order['product_name']); ?></h4>
                                            <p>Quantity: <?php echo $order['quantity']; ?> | Total: $<?php echo number_format($order['price'] * $order['quantity'], 2); ?></p>
                                            <small>Ordered on: <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></small>
                                        </div>
                                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a></p>
                    <p><a href="tel:+8801234567890">+880 123 456 7890</a></p>
                    <p>123 Skyline Avenue, Dhaka</p>
                </div>
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>We are passionate about serving the finest coffee, crafted with love and expertise. Join us for a unique coffee experience!</p>
                </div>
                
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="https://facebook.com" class="social-icon" aria-label="Facebook">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo" class="social-logo">
                        </a>
                        <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo" class="social-logo">
                        </a>
                        <a href="https://x.com" class="social-icon" aria-label="X">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo">
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add active class to current nav item
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-links a');
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });

        // Handle newsletter form submission
        const newsletterForm = document.querySelector('.newsletter-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                if (emailInput.value) {
                    alert('Thank you for subscribing to our newsletter!');
                    emailInput.value = '';
                }
            });
        }

        // Add hover animation to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    </script>
</body>
</html>
