<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SESSION['role'] !== 'welder') { exit("Unauthorized"); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $conn->query("UPDATE custom_orders SET welder_confirmed = 1 WHERE id = $id AND assigned_welder_id = {$_SESSION['user_id']}");
}

$back = $_SERVER['HTTP_REFERER'] ?? 'welder_dashboard.php';
header("Location: $back");
exit;
?>
