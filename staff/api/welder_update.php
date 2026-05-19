<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'welder') {
    http_response_code(403); exit;
}

$orderId   = (int)$_POST['order_id'];
$status    = $conn->real_escape_string($_POST['status'] ?? '');
$pct       = (int)($_POST['progress_pct'] ?? 10);
$details   = $conn->real_escape_string($_POST['progress_details'] ?? '');
$estDate   = $_POST['estimated_completion'] ?? null;
$remarks   = $conn->real_escape_string($_POST['remarks'] ?? '');
$branch    = $_SESSION['branch_id'] ?? 1;

$allowed = ['Initial Payment','On-going','For Delivery','Backjobs','Completed'];
if (!in_array($status, $allowed)) {
    header("Location: ../welder_dashboard.php?error=Invalid status selection"); exit;
}

// 1. Update project status, estimated completion, and granular progress values
$stmt = $conn->prepare("
    UPDATE custom_orders 
    SET status = ?, 
        estimated_completion = ?, 
        progress_percent = ?, 
        progress_details = ?, 
        updated_at = NOW() 
    WHERE id = ?
");
$stmt->bind_param("ssisi", $status, $estDate, $pct, $details, $orderId);
$stmt->execute();

// 2. Log remarks into transactions table as a tracking history note
if (!empty($remarks)) {
    $note = "[Welder progress update ($pct%)]: " . $remarks;
    $stmt2 = $conn->prepare("
        INSERT INTO transactions (order_id, staff_id, remarks, status, created_at) 
        VALUES (?, ?, ?, 'Pending', NOW())
    ");
    $stmt2->bind_param("iis", $orderId, $_SESSION['user_id'], $note);
    $stmt2->execute();
}

// 3. Process Raw Materials / Breakdown of Prices allocation
if (isset($_POST['item_id']) && is_array($_POST['item_id'])) {
    $itemIds = $_POST['item_id'];
    $quantities = $_POST['quantity'] ?? [];

    for ($i = 0; $i < count($itemIds); $i++) {
        $itemId = (int)$itemIds[$i];
        $qty = (int)($quantities[$i] ?? 0);

        if ($itemId > 0 && $qty > 0) {
            // Fetch material price
            $itemQuery = $conn->query("SELECT price FROM items WHERE id = $itemId");
            if ($itemQuery && $itemQuery->num_rows > 0) {
                $itemInfo = $itemQuery->fetch_assoc();
                $price = (float)$itemInfo['price'];
                $total = $price * $qty;

                // Insert into order_items
                $insItem = $conn->prepare("
                    INSERT INTO order_items (order_id, item_id, quantity, price, total_amount, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $insItem->bind_param("iiidd", $orderId, $itemId, $qty, $price, $total);
                $insItem->execute();

                // Deduct stock from branch inventory
                $deductStock = $conn->prepare("
                    UPDATE inventory 
                    SET current_stock = GREATEST(0, current_stock - ?) 
                    WHERE item_id = ? AND branch_id = ?
                ");
                $deductStock->bind_param("iii", $qty, $itemId, $branch);
                $deductStock->execute();
            }
        }
    }
}

header("Location: ../welder_dashboard.php?success=Project progress, pricing materials and timeline updated successfully!");
exit;
?>
