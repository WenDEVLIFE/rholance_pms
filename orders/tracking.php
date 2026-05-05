<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

$customerId = $_SESSION['user_id'];

$orders = $conn->query("
SELECT id, material, status
FROM custom_orders
WHERE customer_id = $customerId
ORDER BY created_at DESC
");
?>

<div class="main">

<h1>Order Tracking</h1>

<table>
<tr>
<th>Order</th>
<th>Material</th>
<th>Status</th>
</tr>

<?php while($o = $orders->fetch_assoc()): ?>
<tr>
<td>#<?= $o['id'] ?></td>
<td><?= $o['material'] ?></td>
<td><?= $o['status'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>