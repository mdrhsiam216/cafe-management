<?php
require_once 'admin_functions.php';
session_start();
validateAdminAccess();

$conn = connect_db();
$query = "SELECT id, name, email, role, photo FROM users WHERE role = 'customer' ORDER BY id DESC";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
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
                <li><a href="manage-users.php" class="active">Manage Users</a></li>
                <li><a href="manage-coupons.php">Manage Coupons</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        
        <div class="welcome-box">
            <div class="hero-section">
                <img src="../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Manage Users</h1>
                <p>View and manage customer accounts</p>
            </div>
            
            <section class="management-section">
                <div class="section-header">
                    <h2>User List</h2>
                    <div class="search-controls">
                        <input type="text" id="searchUsers" placeholder="Search users..." onkeyup="searchUsers()">
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="action-buttons">
                                    <button class="btn-small btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
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
        function searchUsers() {
            const input = document.getElementById('searchUsers');
            const filter = input.value.toLowerCase();
            const tbody = document.getElementById('usersTableBody');
            const rows = tbody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const nameCell = rows[i].getElementsByTagName('td')[1];
                const emailCell = rows[i].getElementsByTagName('td')[2];
                
                if (nameCell && emailCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const emailText = emailCell.textContent || emailCell.innerText;
                    
                    if (nameText.toLowerCase().indexOf(filter) > -1 || 
                        emailText.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user? This will also delete all their orders and cart items.')) {
                fetch('user_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_user&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully!');
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to delete user');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete user');
                });
            }
        }
    </script>
</body>
</html>
