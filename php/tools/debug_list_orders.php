<?php
require_once __DIR__ . '/../rdb.php';
$c = connect_db();
$q = "SELECT o.id,o.quantity,o.status,p.name AS product_name,u.name AS customer_name FROM orders o LEFT JOIN products p ON o.productId=p.id LEFT JOIN users u ON o.userId=u.id ORDER BY o.created_at DESC";
$res = $c->query($q);
if (!$res) {
    echo 'Query error: ' . $c->error . PHP_EOL;
    exit(1);
}
$found = false;
while ($r = $res->fetch_assoc()) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    $found = true;
}
if (!$found) echo "--NO ROWS--" . PHP_EOL;
$c->close();

?>