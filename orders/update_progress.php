<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'welder') {
    $orderId = (int)$_POST['order_id'];
    $pct = (int)$_POST['progress_percent'];
    $details = $conn->real_escape_string($_POST['progress_details']);
    
    $conn->query("UPDATE custom_orders SET progress_percent=$pct, progress_details='$details', progress_status='Pending Approval' WHERE id=$orderId AND assigned_welder_id={$_SESSION['user_id']}");
    header("Location: view_order.php?id=$orderId&msg=ProgressSubmitted");
    exit;
}
header("Location: ../index.php");
