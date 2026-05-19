<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) { exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch = $_SESSION['branch_id'] ?? 1;

    $stmt = $conn->prepare("
        INSERT INTO appointments 
        (customer_name, appointment_date, appointment_time, address, landmark, contact_person, status, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)
    ");

    $stmt->bind_param(
        "ssssssi",
        $_POST['customer_name'],
        $_POST['appointment_date'],
        $_POST['appointment_time'],
        $_POST['address'],
        $_POST['landmark'],
        $_POST['contact_person'],
        $branch
    );

    $stmt->execute();

    header("Location: appointment.php");
}
?>