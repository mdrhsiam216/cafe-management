<?php
require_once 'admin_functions.php';
validateAdminAccess();

$conn = connect_db();
$stats = getDBStats($conn);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Skyline Coffee Shop</title>
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="manage-employees.php">Manage Employees</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="adminprofile.php">Profile</a></li>
                <li><a href="manage-products.php">Manage Products</a></li>
                <li><a href="manage-coupons.php">Manage Coupons</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        
        <div class="welcome-box">
            <div class="hero-section">
                <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            
            <section class="dashboard-section">
                <h2>Quick Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Employees</h3>
                        <div class="stat-number">
                            <?php echo $stats['total_employees']; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Orders</h3>
                        <div class="stat-number">
                            <?php echo $stats['pending_orders']; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="stat-number">
                            <?php echo $stats['total_users']; ?>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h3>Active Coupons</h3>
                        <div class="stat-number">
                            <?php echo $stats['active_coupons']; ?>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="manage-employees.php" class="btn">Manage Employees</a>
                        <a href="orders.php" class="btn">View Orders</a>
                        <a href="manage-users.php" class="btn">Manage Users</a>
                        <a href="manage-products.php" class="btn">Manage Products</a>
                        <a href="manage-coupons.php" class="btn">Manage Coupons</a>
                        <a href="manage-special-offers.php" class="btn">Manage Special Offers</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
