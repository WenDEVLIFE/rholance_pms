<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'staff') {
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO appointments 
    (customer_name, appointment_date, appointment_time, address, landmark, contact_person, branch_id, user_id, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
");

$stmt->bind_param(
    "ssssssii",
    $_POST['customer_name'],
    $_POST['appointment_date'],
    $_POST['appointment_time'],
    $_POST['address'],
    $_POST['landmark'],
    $_POST['contact_person'],
    $_SESSION['branch_id'],
    $_SESSION['user_id']
);

$stmt->execute();

header("Location: appointment.php");