<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'welder') {
    $orderId = (int)$_POST['order_id'];
    $price = (float)$_POST['quoted_price'];
    $deadline = $conn->real_escape_string($_POST['quoted_deadline']);
    $breakdown = $conn->real_escape_string($_POST['quoted_breakdown']);

    $conn->query("UPDATE custom_orders SET quoted_price=$price, quoted_deadline='$deadline', quoted_breakdown='$breakdown', quote_status='Pending Review' WHERE id=$orderId AND assigned_welder_id={$_SESSION['user_id']}");
    header("Location: view_order.php?id=$orderId&msg=QuoteSubmitted");
    exit;
}
header("Location: ../index.php");
