<?php
require_once 'admin_functions.php';
require_once 'response_functions.php';
validateAdminAccess();

$conn = connect_db();
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_users':
        $stmt = $conn->prepare("
            SELECT id, name, email, role, photo 
            FROM users 
            WHERE role = 'customer'
            ORDER BY id DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        sendSuccess($users);
        break;

    case 'delete_user':
        $id = $_POST['id'] ?? 0;
        
        $conn->begin_transaction();
        
        try {
            // First delete from cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE userId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Then delete from orders
            $stmt = $conn->prepare("DELETE FROM orders WHERE userId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Finally delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $conn->commit();
            sendSuccess(null, 'User deleted successfully');
        } catch (Exception $e) {
            $conn->rollback();
            sendError($e->getMessage());
        }
        break;

    case 'get_user_orders':
        $userId = $_GET['userId'] ?? 0;
        
        $stmt = $conn->prepare("
            SELECT o.*, p.name as productName, p.price
            FROM orders o 
            LEFT JOIN products p ON o.productId = p.id
            WHERE o.userId = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->bind_param("i", $userId);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        sendSuccess($orders);
        break;

    default:
        sendError('Invalid action');
}

$conn->close();
?>