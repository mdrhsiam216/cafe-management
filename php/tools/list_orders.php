<?php
require_once __DIR__ . '/../rdb.php';
$c = connect_db();
$res = $c->query("SELECT o.id, o.userId, o.productId, o.quantity, o.status, o.payment_method, o.created_at, s.title AS special_title, o.is_special_offer FROM orders o LEFT JOIN special_offers s ON o.specialOfferId = s.id ORDER BY o.created_at DESC LIMIT 10");
if (!$res) {
    echo 'Query failed: ' . $c->error . PHP_EOL;
    exit(1);
}
while ($r = $res->fetch_assoc()) {
    echo implode(' | ', $r) . PHP_EOL;
}
?>