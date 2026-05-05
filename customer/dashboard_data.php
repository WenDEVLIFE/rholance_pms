<?php
session_start();
include __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$customerId = $_SESSION['user_id'];

/* Orders count */
$orders = $conn->query("
SELECT COUNT(*) as total 
FROM custom_orders 
WHERE customer_id = $customerId
")->fetch_assoc()['total'];

/* Appointments count */
$appointments = $conn->query("
SELECT COUNT(*) as total
FROM custom_orders
WHERE customer_id = $customerId
AND appointment_date IS NOT NULL
")->fetch_assoc()['total'];

/* Latest orders */
$latestOrders = $conn->query("
SELECT material,status,created_at
FROM custom_orders
WHERE customer_id = $customerId
ORDER BY created_at DESC
LIMIT 5
");

$data = [
    "orders" => $orders,
    "appointments" => $appointments,
    "latest_orders" => $latestOrders->fetch_all(MYSQLI_ASSOC)
];

echo json_encode($data);