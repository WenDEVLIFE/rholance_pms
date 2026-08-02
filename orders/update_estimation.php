<?php
require_once '../includes/auth_check.php';
include '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'welder') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$orderId = (int)$_POST['order_id'];
$dimensions = $conn->real_escape_string($_POST['dimensions']);
$material = $conn->real_escape_string($_POST['material']);

$laborFab = (float)($_POST['labor_fabrication'] ?? 0);
$laborInst = (float)($_POST['labor_installation'] ?? 0);
$laborPaint = (float)($_POST['labor_painting'] ?? 0);
$laborTrans = (float)($_POST['labor_transpo'] ?? 0);
$laborTotal = $laborFab + $laborInst + $laborPaint + $laborTrans;

$itemIds = $_POST['item_id'] ?? [];
$quantities = $_POST['quantity'] ?? [];

// Create alerts table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `inventory_alerts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) NOT NULL,
    `branch_id` INT(11) NOT NULL,
    `message` VARCHAR(255) NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

// Fetch order branch
$orderQ = $conn->query("SELECT branch_id, project_name FROM custom_orders WHERE id = $orderId");
$order = $orderQ ? $orderQ->fetch_assoc() : null;
if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}
$branchId = (int)$order['branch_id'];
$projectName = $order['project_name'];

// Calculate materials cost and deduct inventory
$materialsTotal = 0;
$itemsAllocated = [];

for ($i = 0; $i < count($itemIds); $i++) {
    $itemId = (int)$itemIds[$i];
    $qty = (int)$quantities[$i];
    
    if ($itemId > 0 && $qty > 0) {
        // Fetch item details
        $itemRes = $conn->query("SELECT name, price FROM items WHERE id = $itemId")->fetch_assoc();
        if ($itemRes) {
            $price = (float)$itemRes['price'];
            $itemTotal = $price * $qty;
            $materialsTotal += $itemTotal;
            $itemName = $itemRes['name'];

            $itemsAllocated[] = [
                'id' => $itemId,
                'name' => $itemName,
                'qty' => $qty,
                'price' => $price,
                'total' => $itemTotal
            ];

            // Deduct stock from inventory
            $invRes = $conn->query("SELECT current_stock FROM inventory WHERE item_id = $itemId AND branch_id = $branchId")->fetch_assoc();
            if ($invRes) {
                $newStock = max(0, (int)$invRes['current_stock'] - $qty);
                $conn->query("UPDATE inventory SET current_stock = $newStock WHERE item_id = $itemId AND branch_id = $branchId");
                
                // Low stock alert trigger
                if ($newStock < 5) {
                    $alertMsg = "Low Stock Warning: " . $conn->real_escape_string($itemName) . " is down to $newStock units in branch $branchId due to allocation for '$projectName'.";
                    $conn->query("INSERT INTO inventory_alerts (item_id, branch_id, message) VALUES ($itemId, $branchId, '$alertMsg')");
                }
            }
        }
    }
}

$grandTotal = $materialsTotal + $laborTotal;

// Write breakdown text
$breakdownText = "Materials Breakdown:\n";
foreach ($itemsAllocated as $item) {
    $breakdownText .= "- " . $item['name'] . " x" . $item['qty'] . " (₱" . number_format($item['total'], 2) . ")\n";
    
    // Save to order_items table to link itemized list
    $conn->query("INSERT INTO order_items (order_id, item_id, quantity, price, total_amount) VALUES ($orderId, {$item['id']}, {$item['qty']}, {$item['price']}, {$item['total']})");
}
$breakdownText .= "\nLabor Breakdown:\n";
$breakdownText .= "- Fabrication Labor: ₱" . number_format($laborFab, 2) . "\n";
$breakdownText .= "- Installation Labor: ₱" . number_format($laborInst, 2) . "\n";
$breakdownText .= "- Painting Labor: ₱" . number_format($laborPaint, 2) . "\n";
$breakdownText .= "- Transpo & Allowance: ₱" . number_format($laborTrans, 2) . "\n";

// Update custom order estimation details
$updateSQL = "UPDATE custom_orders SET 
    quoted_price = $grandTotal,
    labor_cost = $laborTotal,
    dimensions = '$dimensions',
    material = '$material',
    quoted_breakdown = '" . $conn->real_escape_string($breakdownText) . "',
    status = 'Initial Payment' 
    WHERE id = $orderId";

if ($conn->query($updateSQL)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
exit;
?>
