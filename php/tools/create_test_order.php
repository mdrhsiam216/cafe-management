<?php
// Simple test helper to insert an order for debugging purposes.
// Usage (dev only): http://localhost/cafe-management/php/tools/create_test_order.php?userId=3&productId=7&qty=1
// It will insert one order row and output JSON {success: true, id: <inserted id>} or an error.

require_once __DIR__ . '/../rdb.php';
header('Content-Type: application/json; charset=utf-8');

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
$productId = isset($_GET['productId']) ? intval($_GET['productId']) : 0;
$qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;

if ($userId <= 0 || $productId <= 0 || $qty <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters (userId, productId, qty required and must be >0)']);
    exit;
}

try {
    $conn = connect_db();
    $stmt = $conn->prepare('INSERT INTO orders (userId, productId, quantity, status) VALUES (?, ?, ?, "pending")');
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('iii', $userId, $productId, $qty);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
    $insertId = $conn->insert_id;
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'insertId' => $insertId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>