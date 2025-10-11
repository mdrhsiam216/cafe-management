<?php
session_start();
require_once '../rdb.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['offer_id'])) {
    header('Location: ../../index.php');
    exit();
}

$offerId = (int) $_POST['offer_id'];
$conn = connect_db();

// Fetch special offer
$stmt = $conn->prepare("SELECT * FROM special_offers WHERE id = ?");
$stmt->bind_param('i', $offerId);
$stmt->execute();
$res = $stmt->get_result();
$offer = $res->fetch_assoc();
$stmt->close();

if (!$offer) {
    // Offer not found
    $_SESSION['flash_error'] = 'Special offer not found.';
    header('Location: ../../index.php');
    exit();
}

// Insert into orders table using specialOfferId and mark is_special_offer
$quantity = 1;
$stmt = $conn->prepare("INSERT INTO orders (staffId, userId, productId, quantity, couponId, payment_method, status, specialOfferId, is_special_offer) VALUES (NULL, ?, NULL, ?, NULL, NULL, 'pending', ?, TRUE)");
$stmt->bind_param('iii', $userId, $quantity, $offerId);
if (!$stmt->execute()) {
    $_SESSION['flash_error'] = 'Failed to create order: ' . $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: ../../index.php');
    exit();
}

$orderId = $conn->insert_id;
$stmt->close();
$conn->close();

// Success: redirect to order-success or dashboard
header('Location: ../customer/order-success.php?order_id=' . $orderId);
exit();

?>
