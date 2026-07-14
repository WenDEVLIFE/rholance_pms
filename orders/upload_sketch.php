<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'customer') {
    $orderId = (int)$_POST['order_id'];
    
    if (isset($_FILES['sketch']) && $_FILES['sketch']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/sketches/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filename = time() . '_' . basename($_FILES['sketch']['name']);
        $targetFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['sketch']['tmp_name'], $targetFile)) {
            $dbPath = 'uploads/sketches/' . $filename;
            $conn->query("UPDATE custom_orders SET customer_sketch='$dbPath' WHERE id=$orderId AND customer_id={$_SESSION['user_id']}");
        }
    }
    header("Location: view_order.php?id=$orderId&msg=SketchUploaded");
    exit;
}
header("Location: ../index.php");
