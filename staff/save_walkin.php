<?php
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare("
        INSERT INTO appointments 
        (customer_name, appointment_date, appointment_time, address, landmark, contact_person, status)
        VALUES (?, ?, ?, ?, ?, ?, 'Approved')
    ");

    $stmt->bind_param(
        "ssssss",
        $_POST['customer_name'],
        $_POST['appointment_date'],
        $_POST['appointment_time'],
        $_POST['address'],
        $_POST['landmark'],
        $_POST['contact_person']
    );

    $stmt->execute();

    header("Location: appointment.php");
}