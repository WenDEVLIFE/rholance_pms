<?php
session_start();
include __DIR__ . '/../config/database.php';

$customerId = $_SESSION['user_id'];

$material = $_POST['material'];
$dimensions = $_POST['dimensions'];

/* HANDLE IMAGE UPLOAD */
$imageName = null;

if (!empty($_FILES['reference_image']['name'])) {

    $targetDir = "../assets/images/custom_orders/";
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $imageName = time() . "_" . basename($_FILES['reference_image']['name']);
    $targetFile = $targetDir . $imageName;

    move_uploaded_file($_FILES['reference_image']['tmp_name'], $targetFile);
}

/* INSERT ORDER */
mysqli_query($conn, "
    INSERT INTO custom_orders 
    (customer_id, branch_id, material, dimensions, reference_image, status, created_at)
    VALUES 
    ('$customerId', '1', '$material', '$dimensions', '$imageName', 'Pending', NOW())
");

header("Location: ../orders/my_orders.php");
exit;
?>