<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff','admin','welder'])) {
    http_response_code(403); exit('Unauthorized');
}

$orderId = (int)$_POST['order_id'];
$status  = $conn->real_escape_string($_POST['status'] ?? '');

$allowed = ['Appointment','Initial Payment','On-going','For Delivery','Backjobs','Completed','Cancelled'];
if (!in_array($status, $allowed)) {
    header("Location: ../project_management.php?error=invalid_status"); exit;
}

$conn->query("UPDATE custom_orders SET status = '$status', updated_at = NOW() WHERE id = $orderId");

$ref = $_SERVER['HTTP_REFERER'] ?? '../project_management.php';
header("Location: $ref");
exit;
