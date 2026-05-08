<?php
include '../config/database.php';
session_start();

/* ✅ SAFETY CHECK */
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

/* GET DATA */
$date = $_POST['appointment_date'];
$time = $_POST['appointment_time'];
$address = $_POST['address'];
$landmark = $_POST['landmark'] ?? null;
$branch_id = $_POST['branch_id'] ?? null;

/* AUTO NAME FROM SESSION */
$customer_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Customer';

/* 🔒 PREVENT DOUBLE BOOKING */
$check = mysqli_query($conn, "
    SELECT * FROM appointments 
    WHERE appointment_date = '$date'
    AND appointment_time = '$time'
    AND branch_id = '$branch_id'
    AND status IN ('Pending','Completed')
");

if (mysqli_num_rows($check) > 0) {
    header("Location: available_appointments.php?error=slot_taken");
    exit;
}

/* ✅ INSERT WITH CORRECT USER ID */
mysqli_query($conn, "
    INSERT INTO appointments 
    (customer_name, appointment_date, appointment_time, address, landmark, branch_id, user_id, status)
    VALUES 
    ('$customer_name', '$date', '$time', '$address', '$landmark', '$branch_id', '$user_id', 'Pending')
");

/* ✅ REDIRECT WITH SUCCESS */
header("Location: available_appointments.php?success=1");
exit;