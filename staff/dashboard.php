<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { header("Location: ../index.php"); exit; }

function getCount($conn, $q) {
    $r = $conn->query($q);
    return $r ? $r->fetch_assoc()['total'] : 0;
}

$today     = getCount($conn, "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()");
$pending   = getCount($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status='Pending'");
$approved  = getCount($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status='Approved'");
$completed = getCount($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status='Completed'");

/* Today's schedule */
$schedule = $conn->query("SELECT * FROM appointments WHERE appointment_date = CURDATE() ORDER BY appointment_time ASC");

/* Pending approvals */
$pendingAppts = $conn->query("SELECT * FROM appointments WHERE status='Pending' ORDER BY id DESC LIMIT 8");

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
?>

<div class="rh-main">

    <!-- PAGE HEADER -->
    <?php
        $branch_id = $_SESSION['branch_id'] ?? 1;
        $branch_name = $branch_id == 1 ? 'Bautista (Cavite)' : 'Laguna (Biñan)';
    ?>
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>Staff Dashboard</h1>
            <p>Today is <strong><?= date('l, F d, Y') ?></strong> | Branch: <strong><i class="fas fa-code-branch me-1 text-amber"></i><?= $branch_name ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="appointment.php" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-1"></i>Appointments
            </a>
            <a href="project_management.php" class="btn btn-outline-secondary">
                <i class="fas fa-diagram-project me-1"></i>Projects
            </a>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-blue"><i class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="rh-stat-label">Today's Appointments</div>
                    <div class="rh-stat-value"><?= $today ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="rh-stat-label">Pending</div>
                    <div class="rh-stat-value"><?= $pending ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-green"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="rh-stat-label">Approved</div>
                    <div class="rh-stat-value"><?= $approved ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-purple"><i class="fas fa-flag-checkered"></i></div>
                <div>
                    <div class="rh-stat-label">Completed</div>
                    <div class="rh-stat-value"><?= $completed ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- TODAY'S SCHEDULE -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-calendar-day me-2 text-amber"></i>Today's Schedule</span>
                    <a href="appointment.php" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($schedule && $schedule->num_rows > 0): ?>
                        <ul class="list-group list-group-flush">
                        <?php while ($row = $schedule->fetch_assoc()): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-4">
                                <div>
                                    <div class="fw-700"><?= e(!empty($row['customer_name']) ? $row['customer_name'] : 'Walk-in') ?></div>
                                    <div class="text-muted small"><?= e($row['address'] ?? '') ?></div>
                                </div>
                                <span class="badge bg-dark">
                                    <?= date('h:i A', strtotime($row['appointment_time'])) ?>
                                </span>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-xmark fs-2 mb-2 d-block opacity-25"></i>
                            No appointments today
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PENDING APPROVALS -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-hourglass-half me-2 text-amber"></i>Pending Approvals</span>
                    <?php if ($pending > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $pending ?> pending</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if ($pendingAppts && $pendingAppts->num_rows > 0): ?>
                        <ul class="list-group list-group-flush">
                        <?php while ($row = $pendingAppts->fetch_assoc()): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between py-3 px-4">
                                <div>
                                    <div class="fw-700"><?= e(!empty($row['customer_name']) ? $row['customer_name'] : 'Walk-in') ?></div>
                                    <div class="text-muted small">
                                        <i class="far fa-calendar me-1"></i>
                                        <?= e($row['appointment_date']) ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="approve_appointment.php?id=<?= $row['id'] ?>"
                                       class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                    <a href="reject_appointment.php?id=<?= $row['id'] ?>"
                                       class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-check-double fs-2 mb-2 d-block opacity-25"></i>
                            No pending approvals
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /row -->

    <!-- QUICK ACTIONS -->
    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fas fa-bolt me-2 text-amber"></i>Quick Actions</div>
                <div class="card-body d-flex gap-2 flex-wrap">
                    <a href="appointment.php"         class="btn btn-outline-dark"><i class="fas fa-calendar-plus me-1"></i>Manage Appointments</a>
                    <a href="project_management.php"  class="btn btn-outline-dark"><i class="fas fa-diagram-project me-1"></i>Manage Projects</a>
                    <a href="../orders/orders.php"     class="btn btn-outline-dark"><i class="fas fa-file-lines me-1"></i>Custom Orders</a>
                    <a href="../inventory/staff_inventory.php" class="btn btn-outline-dark"><i class="fas fa-boxes-stacking me-1"></i>Inventory</a>
                    <a href="pos/index.php"            class="btn btn-outline-dark"><i class="fas fa-cash-register me-1"></i>POS Terminal</a>
                </div>
            </div>
        </div>
    </div>

</div>

</body></html>