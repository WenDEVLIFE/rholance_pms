<?php
require_once '../config/database.php';
session_start();

$customerId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT MAX(updated_at) as last_update
    FROM custom_orders
    WHERE customer_id = ?
");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'last_update' => $result['last_update']
]);