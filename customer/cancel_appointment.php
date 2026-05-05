<?php
include '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$id = $_POST['appointment_id'] ?? null;

if (!$id) exit("Invalid");

/* SECURITY: ensure owner */
mysqli_query($conn, "
    UPDATE appointments 
    SET status = 'Cancelled'
    WHERE id = '$id' AND user_id = '$user_id'
");

echo "success";