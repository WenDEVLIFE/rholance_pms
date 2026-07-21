<?php
// Dismiss an appointment notification from the pop-up (stores in session)
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false]); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $_SESSION['dismissed_appt_alerts'][] = $id;
}
echo json_encode(['ok' => true]);
