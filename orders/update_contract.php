<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'welder') {
    $orderId = (int)$_POST['order_id'];
    $startDate = $conn->real_escape_string($_POST['contract_start_date']);
    $deadline = $conn->real_escape_string($_POST['quoted_deadline']);
    $terms = $conn->real_escape_string($_POST['contract_terms']);

    $conn->query("UPDATE custom_orders SET 
        contract_start_date = '$startDate',
        quoted_deadline = '$deadline',
        contract_terms = '$terms'
        WHERE id = $orderId AND assigned_welder_id = {$_SESSION['user_id']}");
        
    echo "Success";
    exit;
}
echo "Error";
exit;
?>
