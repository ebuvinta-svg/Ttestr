<?php
// Database configuration
$host = 'localhost';
$username = 'alikf142_Alipof';
$password = 'QuK1gCgN';
$database = 'alikf142_imagine';

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
