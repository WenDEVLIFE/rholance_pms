<?php
require_once '../../includes/auth_check.php';
include '../../config/database.php';

if ($_SESSION['role'] !== 'customer') { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid     = $_SESSION['user_id'];
    $name    = $conn->real_escape_string($_POST['project_name']);
    $cat     = $conn->real_escape_string($_POST['category']);
    $mat     = $conn->real_escape_string($_POST['material']);
    $dim     = $conn->real_escape_string($_POST['dimensions']);
    $desc    = $conn->real_escape_string($_POST['description']);
    $branch  = $_SESSION['branch_id'] ?? 1;

    $imgPath = '';
    if (isset($_FILES['reference_image']) && $_FILES['reference_image']['error'] == 0) {
        $targetDir = "../../uploads/orders/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        $ext = pathinfo($_FILES['reference_image']['name'], PATHINFO_EXTENSION);
        $fileName = "order_" . time() . "_" . $cid . "." . $ext;
        if (move_uploaded_file($_FILES['reference_image']['tmp_name'], $targetDir . $fileName)) {
            $imgPath = "uploads/orders/" . $fileName;
        }
    }

    $sql = "INSERT INTO custom_orders (customer_id, project_name, category, material, dimensions, description, image, status, branch_id) 
            VALUES ($cid, '$name', '$cat', '$mat', '$dim', '$desc', '$imgPath', 'Appointment', $branch)";
    
    if ($conn->query($sql)) {
        header("Location: ../my_projects.php?msg=Order submitted! Please wait for approval.");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
