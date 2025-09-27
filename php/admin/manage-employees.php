<?php
require_once 'admin_functions.php';
session_start();
validateAdminAccess();

$conn = connect_db();
$query = "SELECT u.id, u.name, u.email, s.dutyFrom, s.dutyTo 
          FROM users u 
          JOIN staff s ON u.id = s.userId 
          WHERE u.role = 'staff'";
$result = $conn->query($query);
$employees = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - Admin Dashboard</title>
    <link rel="stylesheet" href="../../css/admin/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage-employees.php" class="active">Manage Employees</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="manage-users.php">Manage Users</a></li>
                <li><a href="manage-coupons.php">Manage Coupons</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        
        <div class="welcome-box">
            <div class="hero-section">
                <img src="../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Manage Employees</h1>
                <p>Manage staff schedules and information</p>
            </div>
            
            <section class="management-section">
                <div class="section-header">
                    <h2>Employee List</h2>
                    <button class="btn" onclick="showAddEmployeeModal()">Add New Employee</button>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Duty From</th>
                                <th>Duty To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo $employee['dutyFrom']; ?></td>
                                <td><?php echo $employee['dutyTo']; ?></td>
                                <td class="action-buttons">
                                    <button class="btn-small btn-edit" onclick="editEmployee(<?php echo $employee['id']; ?>)">Edit</button>
                                    <button class="btn-small btn-delete" onclick="deleteEmployee(<?php echo $employee['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <!-- Add/Edit Employee Modal -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">Add New Employee</h3>
            <form id="employeeForm">
                <div class="form-group">
                    <label for="employeeName">Name</label>
                    <input type="text" id="employeeName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="employeeEmail">Email</label>
                    <input type="email" id="employeeEmail" name="email" required>
                </div>
                <div class="form-group password-group">
                    <label for="employeePassword">Password</label>
                    <input type="password" id="employeePassword" name="password" required>
                </div>
                <div class="form-group">
                    <label for="dutyFrom">Duty From</label>
                    <input type="time" id="dutyFrom" name="dutyFrom">
                </div>
                <div class="form-group">
                    <label for="dutyTo">Duty To</label>
                    <input type="time" id="dutyTo" name="dutyTo">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Save Employee</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentEmployeeId = null;

        function showAddEmployeeModal() {
            currentEmployeeId = null;
            document.getElementById('modalTitle').textContent = 'Add New Employee';
            document.getElementById('employeeForm').reset();
            document.getElementById('employeePassword').required = true;
            document.querySelector('.password-group').style.display = 'block';
            document.getElementById('employeeModal').style.display = 'block';
        }

        function editEmployee(id) {
            currentEmployeeId = id;
            document.getElementById('modalTitle').textContent = 'Edit Employee';
            document.getElementById('employeePassword').required = false;
            document.querySelector('.password-group').style.display = 'none';
            
            // Fetch employee data
            fetch('employee_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_employees`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const employee = data.data.find(emp => emp.id == id);
                    if (employee) {
                        document.getElementById('employeeName').value = employee.name;
                        document.getElementById('employeeEmail').value = employee.email;
                        document.getElementById('dutyFrom').value = employee.dutyFrom;
                        document.getElementById('dutyTo').value = employee.dutyTo;
                    }
                }
                document.getElementById('employeeModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to fetch employee data');
            });
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure you want to delete this employee?')) {
                fetch('employee_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_employee&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to delete employee');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete employee');
                });
            }
        }

        function closeModal() {
            document.getElementById('employeeModal').style.display = 'none';
        }

        // Handle form submission
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            if (currentEmployeeId) {
                formData.append('action', 'update_employee');
                formData.append('id', currentEmployeeId);
            } else {
                formData.append('action', 'add_employee');
            }

            fetch('employee_actions.php', {
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
            const modal = document.getElementById('employeeModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
