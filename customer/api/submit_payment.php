<?php
require_once '../../includes/auth_check.php';
include '../../config/database.php';

if ($_SESSION['role'] !== 'customer') { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)$_POST['order_id'];
    $method  = $conn->real_escape_string($_POST['payment_method']);
    $remarks = $conn->real_escape_string($_POST['remarks']);
    $uid     = $_SESSION['user_id'];

    $proofPath = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $targetDir = "../../uploads/payments/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $fileName = "pay_" . time() . "_" . $uid . "." . $ext;
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetDir . $fileName)) {
            $proofPath = "payments/" . $fileName;
        }
    }

    $sql = "INSERT INTO transactions (order_id, payment_method, remarks, payment_proof, status) 
            VALUES ($orderId, '$method', '$remarks', '$proofPath', 'Pending')";
    
    if ($conn->query($sql)) {
        header("Location: ../transactions.php?msg=Payment proof submitted! Pending verification.");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
