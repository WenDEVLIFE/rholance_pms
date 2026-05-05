<?php
require_once '../config/database.php';

// ✅ Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid appointment ID.");
}

$id = (int) $_GET['id'];

// ✅ Prepare statement (SAFE)
$stmt = $conn->prepare("
    UPDATE appointments 
    SET status = 'Approved' 
    WHERE id = ?
");

$stmt->bind_param("i", $id);

// ✅ Execute with error check
if (!$stmt->execute()) {
    die("Error updating appointment: " . $stmt->error);
}

// ✅ Redirect
header("Location: appointment.php");
exit;