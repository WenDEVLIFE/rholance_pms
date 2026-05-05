<?php
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($conn)) {
    die("DB connection failed.");
}

$user_id = $_SESSION['user_id'];

// CHECK FILE
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== 0) {
    die("Upload failed.");
}

// CREATE FOLDER IF NOT EXISTS
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// FILE INFO
$file = $_FILES['avatar'];
$filename = time() . '_' . basename($file['name']);
$targetPath = $uploadDir . $filename;

$allowed = ['image/jpeg', 'image/png', 'image/jpg'];

if (!in_array($file['type'], $allowed)) {
    die("Invalid file type.");
}

// MOVE FILE
if (move_uploaded_file($file['tmp_name'], $targetPath)) {

    // SAVE TO DATABASE
    mysqli_query($conn, "UPDATE users SET avatar='$filename' WHERE id='$user_id'");

    // UPDATE SESSION
    $_SESSION['avatar'] = $filename;

    header("Location: settings.php");
    exit;

} else {
    die("Failed to upload file.");
}