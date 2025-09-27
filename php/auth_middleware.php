<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /diganto-cafe/php/login.php");
        exit();
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        switch($_SESSION['user_role']) {
            case 'admin':
                header("Location: /diganto-cafe/php/admin/dashboard.php");
                break;
            case 'staff':
                header("Location: /diganto-cafe/php/staff/staff-orders.php");
                break;
            case 'customer':
                header("Location: /diganto-cafe/php/customer/dashboard.php");
                break;
            default:
                header("Location: /diganto-cafe/index.php");
        }
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        switch($_SESSION['user_role']) {
            case 'admin':
                header("Location: /diganto-cafe/php/admin/dashboard.php");
                break;
            case 'staff':
                header("Location: /diganto-cafe/php/staff/staff-orders.php");
                break;
            case 'customer':
                header("Location: /diganto-cafe/php/customer/dashboard.php");
                break;
            default:
                header("Location: /diganto-cafe/index.php");
        }
        exit();
    }
}
?>