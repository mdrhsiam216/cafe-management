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

// Get all orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get cart count for navigation
$stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE userId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_count = $result->fetch_assoc()['cart_count'] ?: 0;

$stmt = $conn->prepare("
    SELECT o.id, o.quantity, o.created_at, o.status, o.payment_method,
        p.name as product_name, p.price, p.image,
        s.title AS special_title, s.genuine_price AS special_genuine_price, s.discount AS special_discount,
        o.is_special_offer
    FROM orders o 
    LEFT JOIN products p ON o.productId = p.id 
    LEFT JOIN special_offers s ON o.specialOfferId = s.id
    WHERE o.userId = ? 
    ORDER BY o.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Get total count for pagination
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE userId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_orders = $result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/customer/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart (<?php echo $cart_count; ?>)</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <div class="dashboard-container">
            <div class="hero-section">
                <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Order History</h1>
                <p>Track all your coffee adventures</p>
            </div>

            <div class="recent-orders">
                <h2>All Orders (<?php echo $total_orders; ?> total)</h2>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <p>No orders found. <a href="../menu.php">Start ordering now!</a></p>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <h4>Order #<?php echo $order['id']; ?> - <?php echo htmlspecialchars($order['is_special_offer'] ? ($order['special_title'] ?? 'Special Offer') : ($order['product_name'] ?? 'Product')); ?></h4>
                                    <?php if ($order['is_special_offer']): ?>
                                        <?php $price = isset($order['special_genuine_price']) && isset($order['special_discount']) ? ($order['special_genuine_price'] - ($order['special_genuine_price'] * $order['special_discount'] / 100)) : 0; ?>
                                        <p>Quantity: <?php echo $order['quantity']; ?> | Total: $<?php echo number_format($price * $order['quantity'], 2); ?></p>
                                    <?php else: ?>
                                        <p>Quantity: <?php echo $order['quantity']; ?> | Total: $<?php echo number_format(($order['price'] ?? 0) * $order['quantity'], 2); ?></p>
                                    <?php endif; ?>
                                    <small>Ordered on: <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></small>
                                </div>
                                <div class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="btn">Previous</a>
                            <?php endif; ?>
                            
                            <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="btn">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</body>
</html>
