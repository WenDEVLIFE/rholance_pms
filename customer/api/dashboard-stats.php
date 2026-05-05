<?php
include __DIR__ . '/../../config/database.php';
session_start();

/* SECURITY CHECK */
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "orders" => 0,
        "appointments" => 0
    ]);
    exit;
}

$customerId = $_SESSION['user_id'];

/* ================================
   ORDERS COUNT (REAL DATA)
================================ */
$stmtOrders = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM custom_orders 
    WHERE customer_id = ?
");
$stmtOrders->bind_param("i", $customerId);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result()->fetch_assoc();
$orderCount = $resultOrders['total'] ?? 0;


/* ================================
   APPOINTMENTS COUNT (FIXED)
================================ */
$stmtAppointments = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM appointments
    WHERE user_id = ?
    AND status IN ('Pending','Approved')
");
$stmtAppointments->bind_param("i", $customerId);
$stmtAppointments->execute();
$resultAppointments = $stmtAppointments->get_result()->fetch_assoc();
$appointmentCount = $resultAppointments['total'] ?? 0;


/* ================================
   RESPONSE (REAL-TIME)
================================ */
echo json_encode([
    "orders" => (int)$orderCount,
    "appointments" => (int)$appointmentCount
]);