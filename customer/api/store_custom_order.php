<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../index.php");
    exit;
}

$customerId = $_SESSION['user_id'];
$projectName = $_POST['project_name'];
$category = $_POST['category'];
$material = $_POST['material'];
$dimensions = $_POST['dimensions'];
$description = $_POST['description'];

// Handle File Upload
$targetDir = "../../uploads/custom_orders/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$imagePath = "";
if (isset($_FILES['reference_image']) && $_FILES['reference_image']['error'] == 0) {
    $fileName = time() . '_' . basename($_FILES["reference_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($_FILES["reference_image"]["tmp_name"], $targetFilePath)) {
            $imagePath = "uploads/custom_orders/" . $fileName;
        }
    }
}

$stmt = $conn->prepare("INSERT INTO custom_orders (customer_id, project_name, category, material, dimensions, description, instructions, image, order_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'online', 'Appointment')");
$stmt->bind_param("isssssss", $customerId, $projectName, $category, $material, $dimensions, $description, $description, $imagePath);

if ($stmt->execute()) {
    header("Location: ../dashboard.php?success=order_placed");
} else {
    header("Location: ../customize.php?error=failed");
}
exit;
?>
