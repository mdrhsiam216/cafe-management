<?php
require_once '../rdb.php';
require_once 'admin_functions.php';
require_once 'response_functions.php';
validateAdminAccess();

// Database connection
$conn = connect_db();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $price = floatval($_POST['price']);
                // Only store name, price and image (simplified schema)
                
                // Validate inputs
                if (empty($name) || $price <= 0) {
                    $error_message = "Please provide product name and a valid price.";
                    break;
                }
                
                // Handle photo upload
                $photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../resources/uploads/products/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Validate file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                        $error_message = "Only JPG, PNG & GIF files are allowed.";
                        break;
                    }
                    
                    // Validate file size (5MB max)
                    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                        $error_message = "File size must be less than 5MB.";
                        break;
                    }
                    
                    $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                        $photo = 'uploads/products/' . $fileName;
                    } else {
                        $error_message = "Failed to upload image.";
                        break;
                    }
                }
                
                try {
                    $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
                    $stmt->bind_param("sds", $name, $price, $photo);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to add product");
                    }
                    $success_message = "Product added successfully!";
                                    $success_message = "Product added successfully!";
                } catch (Exception $e) {
                    $error_message = "Failed to add product. Please try again. " . $e->getMessage();
                    // Clean up uploaded file if database insert fails
                    if (isset($uploadPath) && file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $price = floatval($_POST['price']);
                $description = trim($_POST['description']);
                $category = trim($_POST['category']);
                
                // Validate inputs
                if ($id <= 0 || empty($name) || $price <= 0 || empty($category)) {
                    $error_message = "Please fill all required fields with valid values.";
                    break;
                }
                
                try {
                    // Handle photo upload for update
                        $photo = $_POST['current_photo'] ?? null;
                        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../../resources/uploads/products/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        // Validate file type
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
                            throw new Exception("Only JPG, PNG & GIF files are allowed.");
                        }
                        
                        // Validate file size (5MB max)
                        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                            throw new Exception("File size must be less than 5MB.");
                        }
                        
                        $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                        $fileName = uniqid() . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                            // Delete old photo if exists
                            if ($photo && file_exists('../../resources/' . $photo)) {
                                unlink('../../resources/' . $photo);
                            }
                            $photo = 'uploads/products/' . $fileName;
                        } else {
                            throw new Exception("Failed to upload image.");
                        }
                    }
                    
                    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ? WHERE id = ?");
                    $stmt->bind_param("sdsi", $name, $price, $photo, $id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update product");
                    }
                    $success_message = "Product updated successfully!";
                    $success_message = "Product updated successfully!";
                } catch (Exception $e) {
                    $error_message = "Failed to update product. Please try again. " . $e->getMessage();
                    // Clean up uploaded file if database update fails
                    if (isset($uploadPath) && file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                if ($id <= 0) {
                    $error_message = "Invalid product ID.";
                    break;
                }
                
                try {
                    // Get photo path before deleting
                    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    
                    // Delete the product
                    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to delete product");
                    }
                    
                    // Delete photo file if exists
                    if ($product && $product['image'] && file_exists('../../resources/' . $product['image'])) {
                        unlink('../../resources/' . $product['image']);
                    }
                    
                    $success_message = "Product deleted successfully!";
                } catch (Exception $e) {
                    $error_message = "Failed to delete product. Please try again. " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch all products
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');

try {
    $query = "SELECT * FROM products WHERE 1=1";
    $types = "";
    $params = array();
    
    if ($search) {
        $query .= " AND name LIKE ?";
        $types .= "s";
        $params[] = "%$search%";
    }
    
    if ($category_filter) {
        $query .= " AND category = ?";
        $types .= "s";
        $params[] = $category_filter;
    }
    
    $query .= " ORDER BY id DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = "Failed to fetch products. Please try again. " . $e->getMessage();
}
// Get unique categories for filter
// Use defensive checks in case the query fails and returns false
$categories = array();
$categories_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category";
$stmt = $conn->query($categories_query);
if ($stmt === false) {
    // Log or expose the error for debugging in development.
    // In production, consider logging this to a file instead of echoing.
    $error_message = isset($error_message) ? $error_message : null;
    $query_error = $conn->error;
    if ($query_error) {
        // Append to any existing error_message for visibility
        $error_message = ($error_message ? $error_message . ' ' : '') . "Category query failed: " . $query_error;
    }
    // Keep categories as empty array
    $categories = array();
} else {
    while ($row = $stmt->fetch_array(MYSQLI_NUM)) {
        $categories[] = $row[0];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Skyline Coffee Shop</title>
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
                <li><a href="adminprofile.php">Profile</a></li>
                <li><a href="manage-products.php" class="active">Manage Products</a></li>
                <li><a href="manage-coupons.php">Manage Coupons</a></li>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        
        <div class="welcome-box">
            <div class="hero-section">
                <img src="../../resources/Brown Modern Circle Coffee Shop Logo.png" alt="Cafe Logo" class="logo">
                <h1>Manage Products</h1>
                <p>Add, edit, and manage your coffee shop products</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <section class="management-section">
                <div class="section-header">
                    <h2>Product Management</h2>
                    <button class="btn" onclick="openAddModal()">Add New Product</button>
                </div>
                    
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img style="height: 200px;" src="../../resources/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                                <div class="product-actions">
                                    <button class="btn btn-edit btn-small" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">Edit</button>
                                    <button class="btn btn-delete btn-small" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2>Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                
                <!-- simplified: only name, price, image are stored -->
                
                <div class="form-group">
                    <label for="photo">Product Photo:</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <small>Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                    <button type="submit" class="btn">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2>Edit Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" id="edit_current_photo" name="current_photo">
                
                <div class="form-group">
                    <label for="edit_name">Product Name:</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_price">Price ($):</label>
                    <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                </div>
                
                <!-- simplified: only name, price, image are stored -->
                
                <div class="form-group">
                    <label for="edit_photo">Product Photo:</label>
                    <input type="file" id="edit_photo" name="photo" accept="image/*">
                    <small>Leave empty to keep current photo</small>
                    <div id="current_photo_preview"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete "<span id="delete_product_name"></span>"?</p>
            <p><strong>This action cannot be undone.</strong></p>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_id" name="id">
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_current_photo').value = product.image || '';
            
            // Show current photo preview
            const preview = document.getElementById('current_photo_preview');
            if (product.image) {
                preview.innerHTML = `<img src="../../resources/${product.image}" alt="Current photo" style="max-width: 100px; margin-top: 10px; border-radius: 4px;">`;
            } else {
                preview.innerHTML = '<p style="margin-top: 10px; color: #666;">No current photo</p>';
            }
            
            document.getElementById('editModal').style.display = 'block';
        }

        function confirmDelete(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_product_name').textContent = name;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['addModal', 'editModal', 'deleteModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Auto-hide success message
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
