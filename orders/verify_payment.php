<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_SESSION['role'], ['admin', 'staff'])) {
    $orderId = (int)$_POST['order_id'];
    $conn->query("UPDATE custom_orders SET payment_status='Paid', status='On-going' WHERE id=$orderId");
    header("Location: view_order.php?id=$orderId&msg=PaymentVerified");
    exit;
}
header("Location: ../index.php");
