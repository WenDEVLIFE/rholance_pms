<?php
require_once '../includes/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    exit;
}

require_once '../config/database.php';

$result = $conn->query("SELECT * FROM appointments ORDER BY appointment_date ASC");
?>

<h2>All Appointments</h2>

<table class="table">
<tr>
    <th>Customer</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['customer_name'] ?></td>
    <td><?= $row['appointment_date'] ?></td>
    <td><?= $row['appointment_time'] ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>

</table>