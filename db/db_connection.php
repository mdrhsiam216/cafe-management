<?php
$host = 'localhost';
$dbname = 'cafe';
$username = 'root';
$password = '';

// Use mysqli for database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>