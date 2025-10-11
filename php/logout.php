<?php
session_start();
session_destroy();
header("Location: /cafe-management/php/login.php");

exit();
?>