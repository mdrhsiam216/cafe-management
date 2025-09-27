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
    
    // Check for session hijacking
    if (!isset($_SESSION['last_ip']) || $_SESSION['last_ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header('Location: ../login.php?error=security');
        exit();
    }
    
    // Check admin role
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