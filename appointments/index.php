<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/header.php';

$customerId = $_SESSION['user_id'];

$appointments = $conn->query("
SELECT id, material, appointment_date, status
FROM custom_orders
WHERE customer_id = $customerId
AND appointment_date IS NOT NULL
ORDER BY appointment_date ASC
");
?>

<div class="main">

<h1>Appointments</h1>

<table>
<tr>
<th>Order</th>
<th>Material</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php while($a = $appointments->fetch_assoc()): ?>
<tr>
<td>#<?= $a['id'] ?></td>
<td><?= $a['material'] ?></td>
<td><?= $a['appointment_date'] ?></td>
<td><?= $a['status'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>