<?php
require_once 'admin_functions.php';
session_start();
validateAdminAccess();

$conn = connect_db();
$query = "SELECT * FROM coupons ORDER BY id DESC";
$result = $conn->query($query);
$coupons = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Coupons - Admin Dashboard</title>
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage-employees.php">Manage Employees</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-coupons.php" class="active">Manage Coupons</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        
        <div class="welcome-box">
            <div class="hero-section">
                <img src="../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Manage Coupons</h1>
                <p>Create and manage discount coupons</p>
            </div>
            
            <section class="management-section">
                <div class="section-header">
                    <h2>Coupon List</h2>
                    <button class="btn" onclick="showAddCouponModal()">Add New Coupon</button>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Coupon Code</th>
                                <th>Discount %</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><?php echo $coupon['id']; ?></td>
                                <td><code><?php echo htmlspecialchars($coupon['couponString']); ?></code></td>
                                <td><?php echo number_format($coupon['percentage'], 2); ?>%</td>
                                <td>
                                    <span class="status-badge <?php echo $coupon['active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $coupon['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn-small btn-edit" onclick="editCoupon(<?php echo $coupon['id']; ?>, '<?php echo htmlspecialchars($coupon['couponString']); ?>', <?php echo $coupon['percentage']; ?>, <?php echo $coupon['active']; ?>)">Edit</button>
                                    <button class="btn-small btn-delete" onclick="deleteCoupon(<?php echo $coupon['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Add/Edit Coupon Modal -->
    <div id="couponModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCouponModal()">&times;</span>
            <h3 id="couponModalTitle">Add New Coupon</h3>
            <form id="couponForm">
                <div class="form-group">
                    <label for="couponString">Coupon Code</label>
                    <input type="text" id="couponString" name="couponString" required placeholder="e.g., SAVE20">
                </div>
                <div class="form-group">
                    <label for="percentage">Discount Percentage</label>
                    <input type="number" id="percentage" name="percentage" required min="0" max="100" step="0.01" placeholder="e.g., 20.00">
                </div>
                <div class="form-group">
                    <label for="couponActive">Status</label>
                    <select id="couponActive" name="active" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Save Coupon</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCouponModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentCouponId = null;

        function showAddCouponModal() {
            currentCouponId = null;
            document.getElementById('couponModalTitle').textContent = 'Add New Coupon';
            document.getElementById('couponForm').reset();
            document.getElementById('couponModal').style.display = 'block';
        }

        function editCoupon(id, couponString, percentage, active) {
            currentCouponId = id;
            document.getElementById('couponModalTitle').textContent = 'Edit Coupon';
            
            // Populate the form
            document.getElementById('couponString').value = couponString;
            document.getElementById('percentage').value = percentage;
            document.getElementById('couponActive').value = active ? '1' : '0';
            
            document.getElementById('couponModal').style.display = 'block';
        }

        function deleteCoupon(id) {
            if (confirm('Are you sure you want to delete this coupon? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete_coupon');
                formData.append('id', id);

                fetch('coupon_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to delete coupon');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete coupon');
                });
            }
        }

        function closeCouponModal() {
            document.getElementById('couponModal').style.display = 'none';
        }

        // Handle form submission
        document.getElementById('couponForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            if (currentCouponId) {
                formData.append('action', 'update_coupon');
                formData.append('id', currentCouponId);
            } else {
                formData.append('action', 'add_coupon');
            }

            fetch('coupon_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Operation failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Operation failed');
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('couponModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
