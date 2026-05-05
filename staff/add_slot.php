<?php
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare("
        INSERT INTO appointment_slots (appointment_date, appointment_time, status)
        VALUES (?, ?, 'Available')
    ");

    $stmt->bind_param(
        "ss",
        $_POST['appointment_date'],
        $_POST['appointment_time']
    );

    $stmt->execute();

    header("Location: appointment.php");
}