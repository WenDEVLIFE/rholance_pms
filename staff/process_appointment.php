<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { exit("Unauthorized"); }

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$status = $_GET['status'] ?? $_POST['status'] ?? '';

if ($id && $status) {
    $allowed = ['Approved', 'Rejected', 'Completed', 'Cancelled', 'Pending'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE appointments SET status = '$status' WHERE id = $id");
    }
}

// Redirect back to wherever we came from
$back = $_SERVER['HTTP_REFERER'] ?? 'appointment.php';
header("Location: $back");
exit;
?>
