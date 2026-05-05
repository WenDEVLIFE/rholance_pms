<?php
session_start();
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/auth_check.php';

/* =========================
   VALIDATE INPUT
========================= */
$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

if ($id <= 0 || empty($action)) {
    echo "error";
    exit;
}

/* =========================
   PREVENT ADMIN MODIFICATION
========================= */
$check = mysqli_query($conn, "SELECT role FROM users WHERE id = '$id'");
$user = mysqli_fetch_assoc($check);

if (!$user) {
    echo "error";
    exit;
}

if (strtolower($user['role']) === 'admin') {
    echo "error";
    exit;
}

/* =========================
   HANDLE ACTIONS
========================= */

switch ($action) {

    case 'block':
        $query = "
            UPDATE users 
            SET status = 'blocked' 
            WHERE id = '$id'
        ";
        break;

    case 'unblock':
        $query = "
            UPDATE users 
            SET status = 'active' 
            WHERE id = '$id'
        ";
        break;

    case 'archive':
        $query = "
            UPDATE users 
            SET status = 'archived' 
            WHERE id = '$id'
        ";
        break;

    case 'restore':
        $query = "
            UPDATE users 
            SET status = 'active' 
            WHERE id = '$id'
        ";
        break;

    default:
        echo "error";
        exit;
}

/* =========================
   EXECUTE QUERY
========================= */
if (mysqli_query($conn, $query)) {
    echo "success";
} else {
    echo "error";
}