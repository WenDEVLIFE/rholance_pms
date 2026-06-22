<?php
include '../config/database.php';
session_start();

/* ✅ SAFETY CHECK */
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

/* GET DATA */
$date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
$time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$landmark = mysqli_real_escape_string($conn, $_POST['landmark'] ?? '');
$contactPerson = mysqli_real_escape_string($conn, $_POST['contact_person'] ?? '');
$branch_id = (int)$_POST['branch_id'];
$requestedProject = mysqli_real_escape_string($conn, $_POST['requested_project'] ?? 'Custom Project');

/* 🔒 LOCATION VALIDATION */
$addr_lower = strtolower($address);
if (strpos($addr_lower, 'cavite') === false && strpos($addr_lower, 'laguna') === false) {
    header("Location: available_appointments.php?error=invalid_location");
    exit;
}

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

/* ✅ INSERT WITH CORRECT USER ID AND REQUESTED PROJECT TYPE */
mysqli_query($conn, "
    INSERT INTO appointments 
    (customer_name, appointment_date, appointment_time, address, landmark, contact_person, branch_id, user_id, status, requested_project, source)
    VALUES 
    ('$customer_name', '$date', '$time', '$address', '$landmark', '$contactPerson', '$branch_id', '$user_id', 'Pending', '$requestedProject', 'Online')
");

/* ✅ REDIRECT WITH SUCCESS */
header("Location: available_appointments.php?success=1");
exit;
?>