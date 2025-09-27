<?php
require_once 'admin_functions.php';
require_once 'response_functions.php';
validateAdminAccess();

$conn = connect_db();
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'update_order_status':
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $staffId = $_SESSION['user_id']; // Current admin user

        if (!in_array($status, ['pending', 'delivered'])) {
            sendError('Invalid status');
        }

        $stmt = $conn->prepare("UPDATE orders SET status = ?, staffId = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $staffId, $id);

        if ($stmt->execute()) {
            sendSuccess(null, 'Order status updated successfully');
        } else {
            sendError($stmt->error);
        }
        break;

    case 'get_orders':
        $status = $_GET['status'] ?? '';
        $query = "
            SELECT o.*, u.name as userName, p.name as productName, p.price 
            FROM orders o 
            LEFT JOIN users u ON o.userId = u.id 
            LEFT JOIN products p ON o.productId = p.id
        ";
        
        if ($status) {
            $query .= " WHERE o.status = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $status);
        } else {
            $stmt = $conn->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        
        // Calculate total amount for each order
        foreach ($orders as &$order) {
            $order['total'] = $order['price'] * $order['quantity'];
            // Add coupon discount logic here if needed
        }

        sendSuccess($orders);
        break;

    case 'get_order_details':
        $id = $_GET['id'] ?? 0;
        
        $stmt = $conn->prepare("
            SELECT o.*, u.name as userName, p.name as productName, p.price,
                   s.name as staffName, c.couponString
            FROM orders o 
            LEFT JOIN users u ON o.userId = u.id 
            LEFT JOIN products p ON o.productId = p.id
            LEFT JOIN users s ON o.staffId = s.id
            LEFT JOIN coupons c ON o.couponId = c.id
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $id);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if ($order) {
            $order['total'] = $order['price'] * $order['quantity'];
            sendSuccess($order);
        } else {
            sendError('Order not found');
        }
        break;

    default:
        sendError('Invalid action');
}

$conn->close();
?>