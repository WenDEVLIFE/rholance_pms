<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$projectId = (int)$_POST['project_id'];
$paymentMethod = $_POST['payment_method'];
$remarks = $_POST['remarks'];

// Handle File Upload
$targetDir = "../../uploads/payments/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$proofPath = "";
if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
    $fileName = time() . '_' . basename($_FILES["payment_proof"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFilePath)) {
            $proofPath = "payments/" . $fileName;
        }
    }
}

// Insert into transactions
$stmt = $conn->prepare("
    INSERT INTO transactions (order_id, remarks, status, payment_method, payment_proof, created_at) 
    VALUES (?, ?, 'Paid', ?, ?, NOW())
");
$stmt->bind_param("isss", $projectId, $remarks, $paymentMethod, $proofPath);

if ($stmt->execute()) {
    // Dynamically advance custom order status to Fabrication On-going upon downpayment submission!
    $updateOrder = $conn->prepare("
        UPDATE custom_orders 
        SET status = 'On-going', updated_at = NOW() 
        WHERE id = ? AND status = 'Initial Payment'
    ");
    $updateOrder->bind_param("i", $projectId);
    $updateOrder->execute();

    header("Location: ../project_details.php?id=$projectId&success=payment_submitted");
} else {
    header("Location: ../add_payment.php?id=$projectId&error=failed");
}
exit;
?>
