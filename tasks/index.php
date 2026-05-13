<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$userId = $_SESSION['user_id'];
$branchId = $_SESSION['branch_id'];
$isCashier = ($_SESSION['role'] === 'staff');

// FETCH TASKS
$tasks = $conn->query("
    SELECT 
        t.id,
        t.task_name,
        t.status,
        o.project_name,
        o.category,
        o.material
    FROM tasks t
    JOIN custom_orders o ON t.order_id = o.id
    WHERE o.branch_id = $branchId
    ORDER BY t.id DESC
");
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>Task Management</h1>
            <p>Track and assign fabrication tasks for your branch.</p>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Task Name</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($tasks && $tasks->num_rows > 0): ?>
                    <?php while ($task = $tasks->fetch_assoc()):
                        $status = $task['status'];
                        $class = match($status) {
                            'In Progress' => 'bg-primary',
                            'For Release' => 'bg-info text-dark',
                            'Completed'   => 'bg-success',
                            default       => 'bg-warning text-dark'
                        };
                        $progress = match($status) {
                            'In Progress' => 50,
                            'For Release' => 75,
                            'Completed'   => 100,
                            default       => 25
                        };

                        $assigned = $conn->query("
                            SELECT u.name 
                            FROM task_assignments ta
                            JOIN users u ON ta.user_id = u.id
                            WHERE ta.task_id = {$task['id']}
                        ");
                    ?>
                    <tr>
                        <td>
                            <div class="fw-700"><?= htmlspecialchars($task['project_name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($task['material']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($task['task_name']) ?></td>
                        <td>
                            <?php if ($assigned->num_rows > 0): ?>
                                <div class="d-flex flex-column gap-1">
                                    <?php while ($a = $assigned->fetch_assoc()): ?>
                                        <span class="badge bg-light text-dark border small w-fit"><?= htmlspecialchars($a['name']) ?></span>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small italic">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $class ?>"><?= $status ?></span></td>
                        <td style="min-width:120px;">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                            </div>
                        </td>
                        <td>
                            <?php if ($isCashier || $_SESSION['role'] === 'admin'): ?>
                                <form method="POST" action="assign_task.php" class="d-flex gap-1">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <select name="staff_id" class="form-select form-select-sm" required style="max-width:140px;">
                                        <option value="">Assign...</option>
                                        <?php
                                        $welderQuery = $conn->query("SELECT id, name FROM users WHERE role = 'welder' AND branch_id = $branchId AND status='active'");
                                        while ($w = $welderQuery->fetch_assoc()):
                                        ?>
                                        <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Add</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No tasks found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>