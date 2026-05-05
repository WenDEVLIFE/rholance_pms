<?php
session_start();
include __DIR__ . '/../config/database.php';

$customerId = $_SESSION['user_id'];

$item_id = (int)$_POST['item_id'];
$price = (float)$_POST['price'];
$quantity = (int)$_POST['quantity'];

$total = $price * $quantity;

/* CREATE ORDER */
mysqli_query($conn, "
    INSERT INTO custom_orders 
    (customer_id, branch_id, material, dimensions, status, created_at)
    VALUES 
    ('$customerId', '1', 'Direct Purchase', 'N/A', 'Pending', NOW())
");

$order_id = mysqli_insert_id($conn);

/* INSERT ITEM */
mysqli_query($conn, "
    INSERT INTO transactions (order_id, total_amount, status)
    VALUES ('$order_id', $total, 'Pending')
");

/* INSERT TRANSACTION */
mysqli_query($conn, "
    INSERT INTO transactions (order_id, total_amount)
    VALUES ('$order_id', '$total')
");

header("Location: ../orders/my_orders.php");
exit;
?>