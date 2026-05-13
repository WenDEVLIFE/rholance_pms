<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if (!isset($_SESSION['user_id'])) { exit("Unauthorized"); }

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? $_POST['appointment_id'] ?? null;

if (!$id) {
    header("Location: available_appointments.php");
    exit;
}

/* SECURITY: ensure owner and only cancel Pending */
$id = (int)$id;
$conn->query("UPDATE appointments SET status = 'Cancelled' WHERE id = $id AND user_id = $user_id AND status = 'Pending'");

header("Location: available_appointments.php?msg=Appointment cancelled");
exit;
?>