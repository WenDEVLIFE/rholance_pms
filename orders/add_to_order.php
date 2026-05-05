<?php
session_start();
include __DIR__ . '/../config/database.php';

/* SECURITY CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$customerId = $_SESSION['user_id'];

/* VALIDATE INPUT */
if (!isset($_POST['item_id'], $_POST['quantity'], $_POST['price'])) {
    die("Invalid request");
}

$item_id = (int) $_POST['item_id'];
$quantity = (int) $_POST['quantity'];
$price = (float) $_POST['price'];

/* PREVENT INVALID VALUES */
if ($quantity <= 0 || $price < 0) {
    die("Invalid quantity or price");
}

/* COMPUTE TOTAL */
$total_amount = $quantity * $price;

/* STEP 1: GET OR CREATE ACTIVE ORDER */
$orderQuery = mysqli_query($conn, "
    SELECT id 
    FROM custom_orders 
    WHERE customer_id = '$customerId' 
    AND status = 'Pending'
    LIMIT 1
");if(isset($_POST['new_order'])){
    
    // ALWAYS create new order
    $createOrder = mysqli_query($conn, "
        INSERT INTO custom_orders 
        (customer_id, branch_id, material, dimensions, status, created_at)
        VALUES 
        ('$customerId', '1', 'Standard Product Order', 'N/A', 'Pending', NOW())
    ");

    $order_id = mysqli_insert_id($conn);

} else {

    // USE EXISTING ORDER
    $orderQuery = mysqli_query($conn, "
        SELECT id 
        FROM custom_orders 
        WHERE customer_id = '$customerId' 
        AND status = 'Pending'
        LIMIT 1
    ");

    if (mysqli_num_rows($orderQuery) > 0) {
        $order = mysqli_fetch_assoc($orderQuery);
        $order_id = $order['id'];
    } else {

        $createOrder = mysqli_query($conn, "
            INSERT INTO custom_orders 
            (customer_id, branch_id, material, dimensions, status, created_at)
            VALUES 
            ('$customerId', '1', 'Standard Product Order', 'N/A', 'Pending', NOW())
        ");

        $order_id = mysqli_insert_id($conn);
    }
}

if (mysqli_num_rows($orderQuery) > 0) {
    $order = mysqli_fetch_assoc($orderQuery);
    $order_id = $order['id'];
} else {

    /* CREATE NEW ORDER */
    $createOrder = mysqli_query($conn, "
        INSERT INTO custom_orders 
        (customer_id, branch_id, material, dimensions, status, created_at)
        VALUES 
        ('$customerId', '1', 'Standard Product Order', 'N/A', 'Pending', NOW())
    ");

    if (!$createOrder) {
        die("Order creation failed: " . mysqli_error($conn));
    }

    $order_id = mysqli_insert_id($conn);
}

/* STEP 2: INSERT INTO ORDER ITEMS */
$insertItem = mysqli_query($conn, "
    INSERT INTO order_items 
    (order_id, item_id, quantity, price, total_amount, created_at)
    VALUES 
    ('$order_id', '$item_id', '$quantity', '$price', '$total_amount', NOW())
");

if (!$insertItem) {
    die("Insert failed: " . mysqli_error($conn));
}

/* SUCCESS → REDIRECT */
header("Location: ../customer/dashboard.php");
exit;
?>