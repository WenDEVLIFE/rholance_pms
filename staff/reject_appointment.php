<?php
require_once '../config/database.php';

$id = (int) $_GET['id'];

// Get slot_id first
$res = $conn->query("SELECT slot_id FROM appointments WHERE id = $id");
$data = $res->fetch_assoc();

$slot_id = $data['slot_id'] ?? null;

// Reject appointment
$conn->query("UPDATE appointments SET status='Rejected' WHERE id=$id");

// 🔥 RELEASE SLOT
if ($slot_id) {
    $conn->query("UPDATE appointment_slots SET status='Available' WHERE id=$slot_id");
}

header("Location: appointment.php");
exit;