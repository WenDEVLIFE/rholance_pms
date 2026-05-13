<?php
require_once '../../includes/auth_check.php';
include '../../config/database.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) {
    http_response_code(403); exit('Unauthorized');
}

$branch   = $_SESSION['branch_id'] ?? 1;
$orderId  = (int)$_POST['order_id'];
$welderId = (int)($_POST['welder_id'] ?? 0);
$estDate  = $_POST['estimated_completion'] ?? null;
$itemIds  = $_POST['item_id'] ?? [];
$quantities = $_POST['quantity'] ?? [];

$conn->begin_transaction();

try {
    /* 1. Update project details */
    if ($estDate) {
        $stmt = $conn->prepare("UPDATE custom_orders SET estimated_completion = ? WHERE id = ?");
        $stmt->bind_param("si", $estDate, $orderId);
        $stmt->execute();
    }

    /* 2. Assign welder via tasks table */
    if ($welderId > 0) {
        $conn->query("DELETE FROM tasks WHERE order_id = $orderId");
        $stmt = $conn->prepare("INSERT INTO tasks (order_id, task_name, status, assigned_to) VALUES (?, 'Fabrication', 'In Progress', ?)");
        $stmt->bind_param("ii", $orderId, $welderId);
        $stmt->execute();
    }

    /* 3. Handle Materials (Deduct from Inventory) */
    foreach ($itemIds as $idx => $itemId) {
        $itemId = (int)$itemId;
        $qty    = (int)($quantities[$idx] ?? 1);
        if ($itemId <= 0 || $qty <= 0) continue;

        // Get current price
        $priceRes = $conn->query("SELECT price FROM items WHERE id = $itemId");
        $itemData = $priceRes->fetch_assoc();
        $price = (float)($itemData['price'] ?? 0);
        $total = $price * $qty;

        // Record in order_items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, price, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiddd", $orderId, $itemId, $qty, $price, $total);
        $stmt->execute();

        // Deduct from branch inventory
        $stmt = $conn->prepare("UPDATE inventory SET current_stock = current_stock - ? WHERE item_id = ? AND branch_id = ?");
        $stmt->bind_param("iii", $qty, $itemId, $branch);
        $stmt->execute();
    }

    /* 4. Advance status if needed */
    $statusRes = $conn->query("SELECT status FROM custom_orders WHERE id = $orderId");
    $currentStatus = $statusRes->fetch_assoc()['status'];
    if ($currentStatus === 'Appointment') {
        $conn->query("UPDATE custom_orders SET status = 'Initial Payment' WHERE id = $orderId");
    }

    $conn->commit();
    header("Location: ../project_management.php?msg=Project assigned and inventory updated.");
} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../project_management.php?err=Error: " . $e->getMessage());
}
exit;
?>
