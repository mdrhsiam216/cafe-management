<?php
session_start();
session_destroy();
header("Location: /diganto-cafe/php/login.php");
exit();
?>