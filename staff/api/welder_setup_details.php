<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'welder') {
    http_response_code(403); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $material = $conn->real_escape_string($_POST['material'] ?? '');
    $dimensions = $conn->real_escape_string($_POST['dimensions'] ?? '');
    $instructions = $conn->real_escape_string($_POST['instructions'] ?? '');
    
    if ($order_id > 0) {
        // 1. Update initial project specifications
        $stmt = $conn->prepare("
            UPDATE custom_orders 
            SET material = ?, dimensions = ?, instructions = ?, status = 'Initial Payment', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $material, $dimensions, $instructions, $order_id);
        $stmt->execute();
        
        // 2. Mark the initial task as completed
        $stmt2 = $conn->prepare("
            UPDATE tasks 
            SET status = 'Completed' 
            WHERE order_id = ? AND task_name LIKE '%Estimation%'
        ");
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        
        header("Location: ../welder_dashboard.php?success=Project details registered! Project is now active.");
        exit;
    }
}
header("Location: ../welder_dashboard.php?error=Invalid request");
exit;
?>
