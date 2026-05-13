<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'] ?? 1;

$result = $conn->query("
    SELECT a.*, u.name AS customer_name, b.name AS branch_name
    FROM appointments a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN branches b ON b.id = a.branch_id
    WHERE (a.branch_id = $branch OR a.branch_id IS NULL)
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>All Appointments</h1>
            <p>Viewing schedule for <strong><?= $branch == 1 ? 'Dasmariñas' : 'Biñan' ?> Branch</strong>.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Customer</th>
                        <th>Date & Time</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $statusCls = match($row['status']) {
                            'Pending'   => 'bg-warning text-dark',
                            'Approved'  => 'bg-primary',
                            'Completed' => 'bg-success',
                            'Cancelled' => 'bg-danger',
                            default     => 'bg-secondary'
                        };
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-700"><?= htmlspecialchars($row['customer_name'] ?? 'Guest Customer') ?></div>
                            <div class="text-muted small">Ref: #<?= $row['id'] ?></div>
                        </td>
                        <td>
                            <div class="fw-600"><?= date('M d, Y', strtotime($row['appointment_date'])) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($row['appointment_time']) ?></div>
                        </td>
                        <td><span class="text-muted small"><?= htmlspecialchars($row['branch_name'] ?? 'Any Branch') ?></span></td>
                        <td><span class="badge <?= $statusCls ?>"><?= $row['status'] ?></span></td>
                        <td class="text-end pe-4">
                            <a href="view_appointment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-eye me-1"></i>View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No appointments scheduled for this branch.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>