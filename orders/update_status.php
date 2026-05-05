<?php
require_once '../config/database.php';

$order_id = $_POST['order_id'];
$status   = $_POST['status'];

$conn->query("
UPDATE custom_orders
SET status = '$status'
WHERE id = $order_id
");

header("Location: orders.php");
exit;