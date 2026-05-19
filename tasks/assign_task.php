<?php
require_once '../config/database.php';

$task_id = (int) $_POST['task_id'];
$user_id = (int) $_POST['staff_id'];

if ($task_id && $user_id) {
    // 1. Insert to task_assignments
    $stmt = $conn->prepare("
        INSERT INTO task_assignments (task_id, user_id)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();

    // 2. Also update primary assigned_to on the tasks table
    $stmt2 = $conn->prepare("
        UPDATE tasks 
        SET assigned_to = ? 
        WHERE id = ?
    ");
    $stmt2->bind_param("ii", $user_id, $task_id);
    $stmt2->execute();
}

header("Location: index.php");
exit;
?>