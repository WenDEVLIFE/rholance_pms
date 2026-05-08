<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff','admin'])) {
    http_response_code(403); exit('Unauthorized');
}

$orderId  = (int)$_POST['order_id'];
$welderId = (int)($_POST['welder_id'] ?? 0);
$estDate  = $_POST['estimated_completion'] ?? null;
$itemIds  = $_POST['item_id'] ?? [];
$quantities = $_POST['quantity'] ?? [];

/* ── Update estimated completion ── */
if ($estDate) {
    $conn->prepare("UPDATE custom_orders SET estimated_completion = ? WHERE id = ?")->execute([$estDate, $orderId]) ?? 
    $conn->query("UPDATE custom_orders SET estimated_completion = '$estDate' WHERE id = $orderId");
    
    $stmt = $conn->prepare("UPDATE custom_orders SET estimated_completion = ? WHERE id = ?");
    $stmt->bind_param("si", $estDate, $orderId);
    $stmt->execute();
}

/* ── Assign welder via tasks table ── */
if ($welderId > 0) {
    /* Remove old task assignment for this order */
    $conn->query("DELETE FROM tasks WHERE order_id = $orderId");

    $stmt = $conn->prepare("INSERT INTO tasks (order_id, task_name, status, assigned_to) VALUES (?, 'Fabrication', 'In Progress', ?)");
    $stmt->bind_param("ii", $orderId, $welderId);
    $stmt->execute();
}

/* ── Insert order_items (materials) ── */
foreach ($itemIds as $idx => $itemId) {
    $itemId = (int)$itemId;
    $qty    = (int)($quantities[$idx] ?? 1);
    if ($itemId <= 0 || $qty <= 0) continue;

    /* Get price */
    $priceRes = $conn->query("SELECT price FROM items WHERE id = $itemId");
    $price = $priceRes ? (float)$priceRes->fetch_assoc()['price'] : 0;
    $total = $price * $qty;

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, price, total_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiddd", $orderId, $itemId, $qty, $price, $total);
    $stmt->execute();
}

/* ── Advance status to On-going if it was Appointment ── */
$currentStatus = $conn->query("SELECT status FROM custom_orders WHERE id = $orderId")->fetch_assoc()['status'];
if ($currentStatus === 'Appointment') {
    $conn->query("UPDATE custom_orders SET status = 'Initial Payment' WHERE id = $orderId");
}

header("Location: ../project_management.php?success=assigned");
exit;
