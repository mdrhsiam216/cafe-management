<?php
require_once 'admin_functions.php';
require_once 'response_functions.php';
validateAdminAccess();

$conn = connect_db();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_coupon':
        $couponString = $_POST['couponString'] ?? '';
        $percentage = $_POST['percentage'] ?? 0;
        $active = $_POST['active'] ?? true;

        $stmt = $conn->prepare("INSERT INTO coupons (couponString, percentage, active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $couponString, $percentage, $active);

        if ($stmt->execute()) {
            sendSuccess(['id' => $conn->insert_id], 'Coupon added successfully');
        } else {
            sendError($stmt->error);
        }
        break;

    case 'update_coupon':
        $id = $_POST['id'] ?? 0;
        $couponString = $_POST['couponString'] ?? '';
        $percentage = $_POST['percentage'] ?? 0;
        $active = $_POST['active'] ?? true;

        $stmt = $conn->prepare("UPDATE coupons SET couponString = ?, percentage = ?, active = ? WHERE id = ?");
        $stmt->bind_param("sdii", $couponString, $percentage, $active, $id);

        if ($stmt->execute()) {
            sendSuccess(null, 'Coupon updated successfully');
        } else {
            sendError($stmt->error);
        }
        break;

    case 'delete_coupon':
        $id = $_POST['id'] ?? 0;

        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            sendSuccess(null, 'Coupon deleted successfully');
        } else {
            sendError($stmt->error);
        }
        break;

    case 'toggle_coupon':
        $id = $_POST['id'] ?? 0;
        $active = $_POST['active'] ?? true;

        $stmt = $conn->prepare("UPDATE coupons SET active = ? WHERE id = ?");
        $stmt->bind_param("ii", $active, $id);

        if ($stmt->execute()) {
            sendSuccess(null, 'Coupon status updated successfully');
        } else {
            sendError($stmt->error);
        }
        break;

    case 'get_coupons':
        $stmt = $conn->prepare("SELECT * FROM coupons");
        $stmt->execute();
        $result = $stmt->get_result();
        $coupons = $result->fetch_all(MYSQLI_ASSOC);
        sendSuccess($coupons);
        break;

    default:
        sendError('Invalid action');
}

$conn->close();
?>