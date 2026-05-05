<?php
require_once '../config/database.php';

$task_id = (int) $_POST['task_id'];
$user_id = (int) $_POST['staff_id'];

// INSERT MULTIPLE ASSIGNMENTS
$stmt = $conn->prepare("
    INSERT INTO task_assignments (task_id, user_id)
    VALUES (?, ?)
");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();

header("Location: index.php");
exit;
?>