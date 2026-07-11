<?php
$host     = 'localhost';
$dbname   = 'orchestr8_db';
$username = 'root';
$password = '';                    // ← Change this if you set a MySQL password

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>