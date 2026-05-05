<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

$customerId = $_SESSION['user_id'];

$orders = mysqli_query($conn, "
    SELECT 
        co.id,
        co.material,
        co.dimensions,
        co.status,
        co.created_at,
        co.reference_image,
        i.name AS item_name,
        oi.quantity
    FROM custom_orders co
    LEFT JOIN order_items oi ON co.id = oi.order_id
    LEFT JOIN items i ON oi.item_id = i.id
    WHERE co.customer_id = '$customerId'
    ORDER BY co.created_at DESC
");

/* GROUP ITEMS */
$groupedOrders = [];

while ($row = mysqli_fetch_assoc($orders)) {
    $id = $row['id'];

    if (!isset($groupedOrders[$id])) {
        $groupedOrders[$id] = [
            'material' => $row['material'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'reference_image' => $row['reference_image'],
            'items' => []
        ];
    }

    if (!empty($row['item_name'])) {
        $groupedOrders[$id]['items'][] = [
            'name' => $row['item_name'],
            'qty' => $row['quantity']
        ];
    }
}
?>

<div class="main">

<h1>My Orders</h1>

<?php if (isset($_GET['cancel']) && $_GET['cancel'] === 'success'): ?>
    <div class="alert-success">Order cancelled successfully.</div>
<?php endif; ?>

<div class="orders-container">

<?php foreach ($groupedOrders as $id => $order): 
     $statusText = '';
$statusType = ''; // for styling

switch($order['status']){
    case 'Appointment':
        $statusText = 'Awaiting schedule confirmation';
        $statusType = 'info';
    break;

    case 'Initial Payment':
        $statusText = 'Waiting for initial payment';
        $statusType = 'warning';
    break;

    case 'On-going':
        $statusText = 'Work is currently in progress';
        $statusType = 'progress';
    break;

    case 'For Delivery':
        $statusText = 'Ready for delivery or pickup';
        $statusType = 'success';
    break;

    case 'Backjobs':
        $statusText = 'This order requires revision';
        $statusType = 'danger';
    break;

    case 'Cancelled':
        $statusText = 'Order has been cancelled';
        $statusType = 'danger';
    break;

    case 'Completed':
        $statusText = 'Order successfully completed';
        $statusType = 'success';
    break;
}

    $statusClass = strtolower(str_replace(' ', '-', $order['status']));
    $firstItem = $order['items'][0]['name'] ?? $order['material'];
    $totalItems = count($order['items']);
?>


<div class="order-card modern">

    <!-- TOP -->
    <div class="order-top">

        <div class="order-info">
            <h4>
                <i class="fa fa-box"></i> 
                <?= htmlspecialchars($firstItem) ?>
            </h4>

            <p class="order-meta">
                <?= $totalItems ?> item<?= $totalItems > 1 ? 's' : '' ?> • 
                <?= date('M d, Y', strtotime($order['created_at'])) ?>
            </p>
        </div>

        <span class="status status-<?= $statusClass ?>">
            <?= htmlspecialchars($order['status']) ?>
        </span>

        <div class="status-message status-<?= $statusType ?>">
    <?= htmlspecialchars($statusText) ?>
</div>

    </div>

    <!-- IMAGE -->
    <?php if (!empty($order['reference_image'])): ?>
        <div class="order-image">
            <img src="../assets/images/custom_orders/<?= htmlspecialchars($order['reference_image']) ?>">
        </div>
    <?php endif; ?>

    <!-- ACTIONS -->
<div class="order-actions">

    <a href="view_order.php?id=<?= urlencode($id) ?>" class="btn-view">
        View Details
    </a>

    <?php if (!in_array($order['status'], ['Cancelled', 'Completed'])): ?>
        <a href="track_order.php?id=<?= urlencode($id) ?>" class="btn-track">
            Track Order
        </a>
    <?php endif; ?>

    <?php if (in_array($order['status'], ['Appointment', 'Initial Payment'])): ?>
        <a href="#" class="btn-cancel" onclick="openCancelModal(<?= $id ?>)">
            Cancel
        </a>
    <?php endif; ?>

</div>

    <!-- ORDER ID -->
    <div class="order-id">
        Order ID: #<?= htmlspecialchars($id) ?>
    </div>

</div>

<?php endforeach; ?>

</div>

<!-- ✅ SINGLE MODAL -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <h3>Cancel Order</h3>
        <p>Are you sure you want to cancel this order?</p>

        <div class="modal-actions">
            <button onclick="closeCancelModal()" class="btn-secondary">No</button>
            <a id="confirmCancelBtn" class="btn-cancel">Yes, Cancel</a>
        </div>
    </div>
</div>

</div>


<!-- ✅ JAVASCRIPT -->
<script>
function openCancelModal(orderId) {
    document.getElementById('cancelModal').style.display = 'block';
    document.getElementById('confirmCancelBtn').href = "cancel_order.php?id=" + orderId;
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}
</script>