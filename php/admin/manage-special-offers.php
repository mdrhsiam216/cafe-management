<?php
// manage-special-offers.php
session_start();

require_once 'admin_functions.php';

// Validate admin access
validateAdminAccess();

require_once '../../db/db_connection.php';

// Handle form submissions for adding a new special offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_offer'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $genuine_price = (float) $conn->real_escape_string($_POST['genuine_price']);
    $discount = (float) $conn->real_escape_string($_POST['discount']);

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../resources/uploads/products/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = 'resources/uploads/products/' . basename($_FILES['image']['name']);
        } else {
            die('Error: Failed to upload image.');
        }
    } else {
        die('Error: No image uploaded or upload error.');
    }

    $query = "INSERT INTO special_offers (title, description, image, genuine_price, discount) VALUES ('$title', '$description', '$imagePath', $genuine_price, $discount)";
    if (!$conn->query($query)) {
        die('Error: ' . $conn->error);
    }
}

// Handle deletion of a special offer
if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    $query = "DELETE FROM special_offers WHERE id = $delete_id";
    if (!$conn->query($query)) {
        die('Error: ' . $conn->error);
    }
}

// Fetch all special offers
$special_offers = [];
$query = "SELECT *, (genuine_price - (genuine_price * discount / 100)) AS price_after_discount FROM special_offers";
if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $special_offers[] = $row;
    }
    $result->free();
} else {
    die('Error: ' . $conn->error);
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Special Offers</title>
    <link rel="stylesheet" href="../../css/admin/manage-special-offers.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="back-to-dashboard">
            <a href="dashboard.php" class="btn">&larr; Back to Dashboard</a>
        </div>

        <h1>Manage Special Offers</h1>

        <form method="POST" action="" enctype="multipart/form-data">
            <h2>Add New Special Offer</h2>
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <label for="genuine_price">Genuine Price:</label>
            <input type="number" name="genuine_price" id="genuine_price" step="0.01" required>

            <label for="discount">Discount (%):</label>
            <input type="number" name="discount" id="discount" step="0.01" required>

            <button type="submit" name="add_offer">Add Offer</button>
        </form>

        <h2>Existing Special Offers</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Genuine Price</th>
                    <th>Discount (%)</th>
                    <th>Price After Discount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($special_offers as $offer): ?>
                    <tr>
                        <td><?php echo $offer['id']; ?></td>
                        <td><?php echo $offer['title']; ?></td>
                        <td><?php echo $offer['description']; ?></td>
                        <td><img src="../../<?php echo $offer['image']; ?>" alt="Offer Image"></td>
                        <td><?php echo number_format($offer['genuine_price'], 2); ?></td>
                        <td><?php echo number_format($offer['discount'], 2); ?>%</td>
                        <td><?php echo number_format($offer['price_after_discount'], 2); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $offer['id']; ?>" onclick="return confirm('Are you sure you want to delete this offer?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>