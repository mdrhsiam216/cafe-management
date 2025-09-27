<?php
require_once 'admin_functions.php';
require_once 'response_functions.php';
validateAdminAccess();

$conn = connect_db();

// Set up error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError($errstr, ['file' => $errfile, 'line' => $errline]);
    return true;
});

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_product"])) {
    $name = trim($_POST["product_name"] ?? "");
    $description = trim($_POST["product_description"] ?? "");
    $price = floatval($_POST["product_price"] ?? 0);
    $category = trim($_POST["product_category"] ?? "");
    $errors = [];

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    if (empty($description)) {
        $errors[] = "Product description is required.";
    }
    if ($price <= 0) {
        $errors[] = "Valid price is required.";
    }
    if (empty($category)) {
        $errors[] = "Product category is required.";
    }
    if (!isset($_FILES["product_image"]) || $_FILES["product_image"]["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Product image is required.";
    }

    if (empty($errors)) {
        // Handle image upload
        $image = $_FILES["product_image"];
        $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($imageFileType, $allowedTypes)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            $targetDir = "../../resources/uploads/products/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = uniqid() . "." . $imageFileType;
            $targetPath = $targetDir . $fileName;

            if (move_uploaded_file($image["tmp_name"], $targetPath)) {
                // Insert into database
                $sql = "INSERT INTO products (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdss", $name, $description, $price, $category, $fileName);

                if ($stmt->execute()) {
                    header("Location: adminproduct.php?success=1");
                    exit;
                } else {
                    $errors[] = "Error adding product: " . $conn->error;
                    // Clean up uploaded file if database insert fails
                    unlink($targetPath);
                }
                $stmt->close();
            } else {
                $errors[] = "Error uploading image.";
            }
        }
    }

    if (!empty($errors)) {
        $errorString = implode("\\n", $errors);
        header("Location: adminproduct.php?error=" . urlencode($errorString));
        exit;
    }
}

// Handle Delete Product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_product"])) {
    $productId = intval($_POST["product_id"] ?? 0);
    
    if ($productId > 0) {
        // Get the image filename before deleting the record
        $sql = "SELECT image FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $imagePath = "../../resources/products/" . $row["image"];
            
            // Delete the record from database
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productId);
            
            if ($stmt->execute()) {
                // Delete the image file if exists
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                header("Location: adminproduct.php?success=2");
                exit;
            } else {
                header("Location: adminproduct.php?error=" . urlencode("Error deleting product."));
                exit;
            }
        } else {
            header("Location: adminproduct.php?error=" . urlencode("Product not found."));
            exit;
        }
        $stmt->close();
    } else {
        header("Location: adminproduct.php?error=" . urlencode("Invalid product ID."));
        exit;
    }
}

// Redirect back if no valid action
header("Location: adminproduct.php");
exit;