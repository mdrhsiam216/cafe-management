<?php
require_once __DIR__ . '/../rdb.php';

// Common function to get database stats
function getDBStats($conn) {
    $stats = [
        'total_employees' => 0,
        'pending_orders' => 0,
        'total_users' => 0,
        'active_coupons' => 0
    ];

    // Get total employees (staff)
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
    if ($result) {
        $stats['total_employees'] = $result->fetch_assoc()['count'];
    }

    // Get pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    if ($result) {
        $stats['pending_orders'] = $result->fetch_assoc()['count'];
    }

    // Get total users (excluding admin)
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
    if ($result) {
        $stats['total_users'] = $result->fetch_assoc()['count'];
    }

    // Get active coupons
    $result = $conn->query("SELECT COUNT(*) as count FROM coupons WHERE active = 1");
    if ($result) {
        $stats['active_coupons'] = $result->fetch_assoc()['count'];
    }

    return $stats;
}

// Validate admin access
function validateAdminAccess() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Normalize IP addresses for comparison
    $session_ip = ($_SESSION['last_ip'] === '::1') ? '127.0.0.1' : $_SESSION['last_ip'];
    $current_ip = ($_SERVER['REMOTE_ADDR'] === '::1') ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];

    if (!isset($_SESSION['last_ip']) || $session_ip !== $current_ip) {
        session_unset();
        session_destroy();
        header('Location: ../login.php?error=security');
        exit();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../login.php');
        exit();
    }

    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regenerated']) || (time() - $_SESSION['last_regenerated']) > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regenerated'] = time();
    }
}

function addSpecialOfferAsProduct($name, $price, $image = null) {
    global $conn; // Assuming $conn is the database connection

    $stmt = $conn->prepare("INSERT INTO products (name, price, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $name, $price, $image);

    if ($stmt->execute()) {
        return $conn->insert_id; // Return the ID of the inserted product
    } else {
        error_log("Error adding special offer as product: " . $stmt->error);
        return false;
    }
}