<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../auth/login.php');
    exit;
}

// Validate POST
if (!isset($_POST['task_id'], $_POST['new_status'])) {
    header('Location: staff_tasks.php');
    exit;
}

$task_id = (int) $_POST['task_id'];
$new_status = $_POST['new_status'];

// Allowed statuses
$allowed = ['Pending','In Progress','For Release','Completed'];
if (!in_array($new_status, $allowed)) {
    header('Location: staff_tasks.php');
    exit;
}

// Get current status
$stmt_check = $conn->prepare("SELECT status FROM tasks WHERE id = ?");
$stmt_check->bind_param("i", $task_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    header('Location: staff_tasks.php');
    exit;
}

$current = $result->fetch_assoc()['status'];

// Valid transitions
$valid_transitions = [
    'Pending' => ['In Progress'],
    'In Progress' => ['For Release'],
    'For Release' => ['Completed'],
    'Completed' => []
];

// Prevent skipping steps
if (!in_array($new_status, $valid_transitions[$current])) {
    header('Location: staff_tasks.php');
    exit;
}

// UPDATE TASK STATUS
$stmt = $conn->prepare("
    UPDATE tasks
    SET status = ?
    WHERE id = ?
");
$stmt->bind_param("si", $new_status, $task_id);
// GET ORDER ID FROM TASK
$stmt_order = $conn->prepare("SELECT order_id FROM tasks WHERE id = ?");
$stmt_order->bind_param("i", $task_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();
$order = $order_result->fetch_assoc();
$order_id = $order['order_id'];

// UPDATE TRANSACTION WHEN COMPLETED
if ($new_status === 'Completed') {

    $updateTransaction = $conn->prepare("
        UPDATE transactions 
        SET status = 'Paid'
        WHERE order_id = ?
    ");

    $updateTransaction->bind_param("i", $order_id);
    $updateTransaction->execute();
}
$stmt->execute();

// Redirect back
header('Location: staff_tasks.php');
exit;