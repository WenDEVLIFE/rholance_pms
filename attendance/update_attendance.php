<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

date_default_timezone_set('Asia/Manila');

/* VALIDATE REQUEST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request";
    exit;
}

/* GET DATA */
$user_id = $_POST['user_id'] ?? null;
$status  = $_POST['status'] ?? null;
$selected_date = $_POST['date'] ?? date('Y-m-d');
$today = date('Y-m-d');

/* SECURITY */
if ($selected_date !== $today) {
    echo "Unauthorized";
    exit;
}

/* VALIDATION */
if (!in_array($status, ['Present', 'Absent'])) {
    echo "Invalid status";
    exit;
}

/* SAVE */
$stmt = $conn->prepare("
    INSERT INTO attendance (user_id, date, status)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status)
");

$stmt->bind_param("iss", $user_id, $selected_date, $status);
$stmt->execute();

/* SUCCESS RESPONSE */
echo "success";
exit;