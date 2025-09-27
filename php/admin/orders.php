<?php
require_once 'admin_functions.php';
session_start();
validateAdminAccess();

$conn = connect_db();
$query = "SELECT o.*, u.name as userName, p.name as productName, p.price 
          FROM orders o 
          LEFT JOIN users u ON o.userId = u.id 
          LEFT JOIN products p ON o.productId = p.id
          ORDER BY o.created_at DESC";
$result = $conn->query($query);
$orders = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin Dashboard</title>
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage-employees.php">Manage Employees</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-coupons.php">Manage Coupons</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        
        <div class="welcome-box">
            <div class="hero-section">
                <img src="../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Order Management</h1>
                <p>Track and manage customer orders</p>
            </div>
            
            <section class="management-section">
                <div class="section-header">
                    <h2>All Orders</h2>
                    <div class="filter-controls">
                        <select id="statusFilter" onchange="filterOrders()">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Staff ID</th>
                                <th>User ID</th>
                                <th>Product ID</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Coupon ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr data-status="<?php echo $order['status']; ?>">
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo $order['staffId'] ?? 'N/A'; ?></td>
                                <td><?php echo $order['userId']; ?></td>
                                <td><?php echo $order['productId']; ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $order['couponId'] ?? 'N/A'; ?></td>
                                <td class="action-buttons">
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button class="btn-small btn-success" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'delivered')">Mark Delivered</button>
                                    <?php endif; ?>
                                    <button class="btn-small btn-edit" onclick="editOrder(<?php echo $order['id']; ?>)">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        function filterOrders() {
            const filter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (filter === '' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function updateOrderStatus(orderId, status) {
            if (confirm(`Mark order #${orderId} as ${status}?`)) {
                // Handle status update
                alert(`Order #${orderId} marked as ${status}!`);
                location.reload();
            }
        }

        function editOrder(orderId) {
            // Handle order editing
            alert(`Edit order #${orderId}`);
        }
    </script>
</body>
</html>
