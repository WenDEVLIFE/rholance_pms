<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_SESSION['role'], ['admin', 'staff'])) {
    $orderId = (int)$_POST['order_id'];
    $welderId = (int)$_POST['welder_id'];
    $visitDate = $conn->real_escape_string($_POST['visit_date']);
    $visitTime = $conn->real_escape_string($_POST['visit_time']);

    $conn->query("UPDATE custom_orders SET assigned_welder_id=$welderId, welder_visit_date='$visitDate', welder_visit_time='$visitTime', status='On-going' WHERE id=$orderId");
    header("Location: view_order.php?id=$orderId&msg=WelderAssigned");
    exit;
}
header("Location: ../index.php");
