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

    $sql = "INSERT INTO transactions (order_id, payment_method, remarks, payment_proof, status, created_at) 
            VALUES ($orderId, '$method', '$remarks', '$proofPath', 'Paid', NOW())";
    
    if ($conn->query($sql)) {
        // Dynamically advance custom order status to Fabrication On-going upon downpayment submission!
        $updateOrder = $conn->prepare("
            UPDATE custom_orders 
            SET status = 'On-going', updated_at = NOW() 
            WHERE id = ? AND status = 'Initial Payment'
        ");
        $updateOrder->bind_param("i", $orderId);
        $updateOrder->execute();

        header("Location: ../transactions.php?msg=Payment proof submitted! Project status advanced to: On-going.");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
