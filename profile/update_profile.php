<?php
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($conn)) {
    die("Database connection failed.");
}

$user_id = $_SESSION['user_id'];

// GET DATA
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// UPDATE QUERY
if (!empty($password)) {
    // HASH PASSWORD
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE users 
            SET name='$name', email='$email', password='$hashed' 
            WHERE id='$user_id'";
} else {
    $sql = "UPDATE users 
            SET name='$name', email='$email' 
            WHERE id='$user_id'";
}

// EXECUTE
mysqli_query($conn, $sql);

// UPDATE SESSION (IMPORTANT)
$_SESSION['name'] = $name;

// REDIRECT BACK
header("Location: settings.php");
exit;