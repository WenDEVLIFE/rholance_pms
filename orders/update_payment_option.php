<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'welder') {
    $orderId = (int)$_POST['order_id'];
    $paymentType = $conn->real_escape_string($_POST['payment_type']);

    // Check if downpayment is selected
    // Note: in custom_orders, payment_status remains unpaid or updates, let's store the preference
    $conn->query("UPDATE custom_orders SET instructions = CONCAT(instructions, '\nPayment Option Selected: ', '$paymentType') WHERE id = $orderId");
    echo "Success";
    exit;
}
echo "Error";
exit;
?>
