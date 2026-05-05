<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
    exit;
}

/* =========================
GET FORM DATA
========================= */

$material = $_POST['material'];
$dimensions = $_POST['dimensions'] ?? '';
$instructions = $_POST['instructions'] ?? '';
$estimated_completion = $_POST['estimated_completion'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;

$customer_name = $_POST['customer_name'];
$order_type = $_POST['order_type'];

$branch = $_SESSION['branch_id'];
$user_id = $_SESSION['user_id'];

/* =========================
IMAGE UPLOAD (SAFE)
========================= */

$imagePath = null;

if (!empty($_FILES['reference_image']['name'])) {

    $targetDir = "../uploads/";
    $fileName = time() . "_" . basename($_FILES["reference_image"]["name"]);
    $targetFile = $targetDir . $fileName;

    move_uploaded_file($_FILES["reference_image"]["tmp_name"], $targetFile);

    $imagePath = $fileName;
}

/* =========================
INSERT INTO ORDERS
========================= */

$stmt = $conn->prepare("
INSERT INTO custom_orders (
    material,
    dimensions,
    instructions,
    estimated_completion,
    appointment_date,
    reference_image,
    status,
    branch_id,
    user_id,
    customer_name,
    order_type
)
VALUES (?, ?, ?, ?, ?, ?, 'Appointment', ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssisss",
    $material,
    $dimensions,
    $instructions,
    $estimated_completion,
    $appointment_date,
    $imagePath,
    $branch,
    $user_id,
    $customer_name,
    $order_type
);

$stmt->execute();

/* =========================
REDIRECT
========================= */

header('Location: ../dashboard/staff.php');
exit;