<?php
date_default_timezone_set('Asia/Manila');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "rholance_pms";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset (IMPORTANT for security & encoding)
$conn->set_charset("utf8mb4");
?>