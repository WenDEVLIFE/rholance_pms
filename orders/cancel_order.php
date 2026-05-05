<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';

$customerId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    die("Invalid order.");
}

/* CHECK OWNERSHIP + STATUS */
$stmt = $conn->prepare("
    SELECT status 
    FROM custom_orders 
    WHERE id = ? AND customer_id = ?
");

$stmt->bind_param("ii", $orderId, $customerId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

/* ALLOW ONLY CERTAIN STATUS */
if (!in_array($order['status'], ['Pending', 'Order Received'])) {
    die("Order cannot be cancelled.");
}

/* UPDATE STATUS */
$update = $conn->prepare("
    UPDATE custom_orders 
    SET status = 'Cancelled' 
    WHERE id = ? AND customer_id = ?
");

$update->bind_param("ii", $orderId, $customerId);
$update->execute();

/* REDIRECT BACK */
header("Location: my_orders.php");
exit;