<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /cafe-management/php/login.php");
        exit();
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        switch($_SESSION['user_role']) {
            case 'admin':
                header("Location: /cafe-management/php/admin/dashboard.php");
                break;
            case 'staff':
                header("Location: /cafe-management/php/staff/staff-orders.php");
                break;
            case 'customer':
                header("Location: /cafe-management/php/customer/dashboard.php");
                break;
            default:
                header("Location: /cafe-management/index.php");
        }
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        switch($_SESSION['user_role']) {
            case 'admin':
                header("Location: /cafe-management/php/admin/dashboard.php");
                break;
            case 'staff':
                header("Location: /cafe-management/php/staff/staff-orders.php");
                break;
            case 'customer':
                header("Location: /cafe-management/php/customer/dashboard.php");
                break;
            default:
                header("Location: /cafe-management/index.php");
        }
        exit();
    }
}
?>