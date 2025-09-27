<?php
// Simple script to create an admin user. Run from browser or CLI.
require_once __DIR__ . '/../rdb.php';

$name = $argv[1] ?? ($_GET['name'] ?? 'Admin User');
$email = $argv[2] ?? ($_GET['email'] ?? 'admin@example.com');
$pass = $argv[3] ?? ($_GET['password'] ?? 'admin123');

$conn = connect_db();

// check exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    echo "User with email $email already exists\n";
    exit;
}

$stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "admin")');
$stmt->bind_param('sss', $name, $email, $pass);
if ($stmt->execute()) {
    echo "Admin user created: $email -- password: $pass\n";
} else {
    echo "Failed to create admin user: " . $conn->error . "\n";
}
$stmt->close();
$conn->close();

?>