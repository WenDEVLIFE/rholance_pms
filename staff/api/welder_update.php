<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'welder') {
    http_response_code(403); exit;
}

$orderId   = (int)$_POST['order_id'];
$status    = $conn->real_escape_string($_POST['status'] ?? '');
$pct       = (int)($_POST['progress_pct'] ?? 0);
$estDate   = $_POST['estimated_completion'] ?? null;
$remarks   = $conn->real_escape_string($_POST['remarks'] ?? '');

$allowed = ['Initial Payment','On-going','For Delivery','Backjobs','Completed'];
if (!in_array($status, $allowed)) {
    header("Location: ../welder_dashboard.php?error=invalid"); exit;
}

/* Update status & estimated completion */
$stmt = $conn->prepare("UPDATE custom_orders SET status = ?, estimated_completion = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("ssi", $status, $estDate, $orderId);
$stmt->execute();

/* Log remarks into transactions table as a note */
if (!empty($remarks)) {
    $stmt2 = $conn->prepare("INSERT INTO transactions (order_id, staff_id, remarks, status) VALUES (?, ?, ?, 'Pending')");
    $stmt2->bind_param("iis", $orderId, $_SESSION['user_id'], $remarks);
    $stmt2->execute();
}

header("Location: ../welder_dashboard.php?success=updated");
exit;
