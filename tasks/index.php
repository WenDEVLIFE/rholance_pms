<?php
session_start();

require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$userId = $_SESSION['user_id'];
$isCashier = ($_SESSION['role'] === 'staff');

// FETCH TASKS
$tasks = $conn->query("
    SELECT 
        t.id,
        t.task_name,
        t.status,
        o.material
    FROM tasks t
    JOIN custom_orders o ON t.order_id = o.id
    ORDER BY t.id DESC
");
?>

<div class="main">
<h1>Tasks</h1>

<div class="card">
<table class="table">
<thead>
<tr>
<th>Order</th>
<th>Task</th>
<th>Assigned</th>
<th>Status</th>
<th>Progress</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<?php while ($task = $tasks->fetch_assoc()): ?>

<?php
$status = $task['status'];

// STATUS STYLE
$class = 'badge-warning';
if ($status === 'In Progress') $class = 'badge-primary';
elseif ($status === 'For Release') $class = 'badge-info';
elseif ($status === 'Completed') $class = 'badge-success';

// PROGRESS
$progress = 25;
if ($status === 'In Progress') $progress = 50;
elseif ($status === 'For Release') $progress = 75;
elseif ($status === 'Completed') $progress = 100;

// GET ASSIGNED WELDERS
$assigned = $conn->query("
    SELECT u.name 
    FROM task_assignments ta
    JOIN users u ON ta.user_id = u.id
    WHERE ta.task_id = {$task['id']}
");
?>

<tr>
<td><?= $task['material'] ?></td>
<td><?= $task['task_name'] ?></td>

<!-- MULTIPLE ASSIGNED -->
<td>
<?php
if ($assigned->num_rows > 0) {
    while ($a = $assigned->fetch_assoc()) {
        echo "<div>{$a['name']}</div>";
    }
} else {
    echo "Unassigned";
}
?>
</td>

<!-- STATUS -->
<td>
<span class="badge <?= $class ?>">
<?= $status ?>
</span>
</td>

<!-- PROGRESS -->
<td>
<div class="progress">
<div class="progress-bar" style="width: <?= $progress ?>%;"></div>
</div>
</td>

<!-- ACTION -->
<td>

<?php if ($isCashier): ?>

<form method="POST" action="assign_task.php">
<input type="hidden" name="task_id" value="<?= $task['id'] ?>">

<select name="staff_id" required>
<option value="">Assign Welder</option>

<?php
// 🔥 ONLY WELDERS (MEN)
if ($branchId) {
    $welderQuery = $conn->query("
        SELECT id, name 
        FROM users 
        WHERE role = 'welder'
        AND branch_id = $branchId
    ");
} else {
    // fallback if branch is NULL
    $welderQuery = $conn->query("
        SELECT id, name 
        FROM users 
        WHERE role = 'welder'
    ");
}

while ($w = $welderQuery->fetch_assoc()):
?>
<option value="<?= $w['id'] ?>">
<?= htmlspecialchars($w['name']) ?> (ID: <?= $w['id'] ?>)
</option>
<?php endwhile; ?>
</select>

<button class="btn btn-secondary btn-sm">Add</button>
</form>

<?php else: ?>

<span class="text-muted">—</span>

<?php endif; ?>

</td>
</tr>

<?php endwhile; ?>
</tbody>
</table>
</div>
</div>