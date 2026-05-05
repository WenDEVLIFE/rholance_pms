<?php
require_once '../config/database.php';

$id = (int) $_GET['id'];

$conn->query("UPDATE appointments SET status='Completed' WHERE id=$id");

header("Location: appointment.php");
exit;