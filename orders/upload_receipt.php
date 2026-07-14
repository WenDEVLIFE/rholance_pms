<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'customer') {
    $orderId = (int)$_POST['order_id'];
    
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/receipts/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filename = time() . '_' . basename($_FILES['receipt']['name']);
        $targetFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetFile)) {
            $dbPath = 'uploads/receipts/' . $filename;
            $conn->query("UPDATE custom_orders SET payment_receipt='$dbPath' WHERE id=$orderId AND customer_id={$_SESSION['user_id']}");
        }
    }
    header("Location: view_order.php?id=$orderId&msg=ReceiptUploaded");
    exit;
}
header("Location: ../index.php");
