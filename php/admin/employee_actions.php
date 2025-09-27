<?php
require_once 'admin_functions.php';
validateAdminAccess();

$conn = connect_db();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_employee':
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    // Store plaintext password (minimal setup)
    $hashedPassword = $password;
        $dutyFrom = $_POST['dutyFrom'] ?? null;
        $dutyTo = $_POST['dutyTo'] ?? null;

        // Start transaction
        $conn->begin_transaction();

        try {
            // First create user account
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'staff')");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            $stmt->execute();
            $userId = $conn->insert_id;

            // Then create staff record
            $stmt = $conn->prepare("INSERT INTO staff (userId, dutyFrom, dutyTo) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userId, $dutyFrom, $dutyTo);
            $stmt->execute();

            $conn->commit();
            sendSuccess(['id' => $userId], 'Employee added successfully');
        } catch (Exception $e) {
            $conn->rollback();
            sendError($e->getMessage());
        }
        break;

    case 'update_employee':
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $dutyFrom = $_POST['dutyFrom'] ?? null;
        $dutyTo = $_POST['dutyTo'] ?? null;

        $conn->begin_transaction();

        try {
            // Update user info
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'staff'");
            $stmt->bind_param("ssi", $name, $email, $id);
            $stmt->execute();

            // Update staff info
            $stmt = $conn->prepare("UPDATE staff SET dutyFrom = ?, dutyTo = ? WHERE userId = ?");
            $stmt->bind_param("ssi", $dutyFrom, $dutyTo, $id);
            $stmt->execute();

            $conn->commit();
            sendSuccess(null, 'Employee updated successfully');
        } catch (Exception $e) {
            $conn->rollback();
            sendError($e->getMessage());
        }
        break;

    case 'delete_employee':
        $id = $_POST['id'] ?? 0;
        
        $conn->begin_transaction();

        try {
            // First delete from staff table
            $stmt = $conn->prepare("DELETE FROM staff WHERE userId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Then delete from users table
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $conn->commit();
            sendSuccess(null, 'Employee deleted successfully');
        } catch (Exception $e) {
            $conn->rollback();
            sendError($e->getMessage());
        }
        break;

    case 'get_employees':
        $stmt = $conn->prepare("
            SELECT u.id, u.name, u.email, s.dutyFrom, s.dutyTo 
            FROM users u 
            JOIN staff s ON u.id = s.userId 
            WHERE u.role = 'staff'
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        sendSuccess($employees);
        break;

    default:
        sendError('Invalid action');
}

$conn->close();
?>