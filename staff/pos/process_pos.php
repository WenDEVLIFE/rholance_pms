<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $payment_type = $_POST['payment_type'] ?? 'full';
    $amount_paid = (float)$_POST['amount_paid'];
    $contract_total = (float)$_POST['contract_total'];
    $staff_id = $_SESSION['user_id'];

    if ($order_id > 0 && $amount_paid > 0) {
        
        $remarks = ($payment_type === 'down') 
            ? "50% Downpayment for fabrication start. Amount received: ₱" . number_format($amount_paid, 2)
            : "100% Full / Final payment received. Project signed off: ₱" . number_format($amount_paid, 2);

        // 1. Insert transaction record
        $stmt = $conn->prepare("
            INSERT INTO transactions (order_id, staff_id, remarks, total_amount, status, created_at)
            VALUES (?, ?, ?, ?, 'Paid', NOW())
        ");
        $stmt->bind_param("iisd", $order_id, $staff_id, $remarks, $amount_paid);
        $stmt->execute();

        // 2. Advance the custom project status based on transaction mode
        $newStatus = ($payment_type === 'down') ? 'On-going' : 'Completed';
        
        $stmt2 = $conn->prepare("
            UPDATE custom_orders 
            SET status = ? 
            WHERE id = ?
        ");
        $stmt2->bind_param("si", $newStatus, $order_id);
        $stmt2->execute();

        header("Location: index.php?msg=Payment transaction processed successfully! Project status advanced to: " . $newStatus);
        exit;
    } else {
        header("Location: index.php?err=Invalid project or amount paid!");
        exit;
    }
}
?>
