<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

$customerId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    die("Invalid order ID.");
}

$stmt = $conn->prepare("
    SELECT 
        co.id,
        co.status,
        co.created_at,
        co.reference_image,
        i.name AS item_name,
        oi.quantity,
        oi.price,
        oi.total_amount
    FROM custom_orders co
    LEFT JOIN order_items oi ON co.id = oi.order_id
    LEFT JOIN items i ON oi.item_id = i.id
    WHERE co.id = ? AND co.customer_id = ?
");

$stmt->bind_param("ii", $orderId, $customerId);
$stmt->execute();
$result = $stmt->get_result();

$order = null;
$items = [];

while ($row = $result->fetch_assoc()) {
    if (!$order) $order = $row;
    if (!empty($row['item_name'])) $items[] = $row;
}

if (!$order) {
    die("Order not found.");
}
?>

<div class="main">

<a href="my_orders.php" class="btn-back">← Back to My Orders</a>

<div class="order-container">

    <div class="order-header-box">
        <div>
            <h2>Order #<?= htmlspecialchars($orderId) ?></h2>
            <p class="order-date"><?= date('F d, Y', strtotime($order['created_at'])) ?></p>
        </div>

        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>">
            <?= htmlspecialchars($order['status']) ?>
        </span>
    </div>

    <div class="order-content">

        <div class="order-items card">
            <h3>Order Items</h3>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                <?php $total = 0; foreach ($items as $item): 
                    $total += $item['total_amount']; ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td>Php<?= number_format($item['price'], 2) ?></td>
                    <td><strong>Php<?= number_format($item['total_amount'], 2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="order-summary card">
            <h3>Summary</h3>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>Php<?= number_format($total, 2) ?></span>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span>Php<?= number_format($total, 2) ?></span>
            </div>

            <button onclick="window.print()" class="btn-primary">
                Print Receipt
            </button>
        </div>

    </div>

</div>

</div>