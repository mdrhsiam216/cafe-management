<?php
require_once __DIR__ . '/../rdb.php';
$c = connect_db();
$res = $c->query("SELECT id,userId,productId,quantity,status,payment_method,created_at FROM orders ORDER BY created_at DESC LIMIT 10");
if (!$res) {
    echo 'Query failed: ' . $c->error . PHP_EOL;
    exit(1);
}
while ($r = $res->fetch_assoc()) {
    echo implode(' | ', $r) . PHP_EOL;
}
?>