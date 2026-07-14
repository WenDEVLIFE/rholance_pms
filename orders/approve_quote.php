<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_SESSION['role'], ['admin', 'staff'])) {
    $orderId = (int)$_POST['order_id'];
    $action = $_POST['action'];

    $status = $action === 'approve' ? 'Approved' : 'Rejected';
    $paymentStatus = $action === 'approve' ? 'Pending Verification' : 'Unpaid';
    
    $conn->query("UPDATE custom_orders SET quote_status='$status', payment_status='$paymentStatus' WHERE id=$orderId");
    header("Location: view_order.php?id=$orderId&msg=QuoteUpdated");
    exit;
}
header("Location: ../index.php");
