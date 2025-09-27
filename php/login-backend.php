<?php
session_start();
require_once 'rdb.php';

function sanitize($v) {
    return trim(htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if ($email === '') {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if ($password === '') {
    $errors[] = 'Password is required';
}

if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    header('Location: login.php');
    exit;
}

$conn = connect_db();

$stmt = $conn->prepare('SELECT id, name, email, role, password FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $stored = $user['password'];

    // Plaintext password comparison (simple, as requested)
    $ok = false;
    if (hash_equals((string)$stored, (string)$password)) {
        $ok = true;
    }

    if ($ok) {
        // set minimal session info required by admin functions
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['last_regenerated'] = time();

        // redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($user['role'] === 'staff') {
            header('Location: staff/staff-orders.php');
        } else {
            header('Location: customer/dashboard.php');
        }
        $stmt->close();
        $conn->close();
        exit;
    }
}

// failure
$_SESSION['login_errors'] = ['Invalid email or password'];
header('Location: login.php');
exit;
?>