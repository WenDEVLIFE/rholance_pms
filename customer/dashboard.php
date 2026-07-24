<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }

$cid = $_SESSION['user_id'];

$s = ['active'=>0,'done'=>0,'total'=>0];
$appt = 0;

if (!$conn->connect_error) {
    $statsQ = $conn->prepare("
        SELECT
            SUM(status NOT IN ('Completed','Cancelled')) AS active,
            SUM(status = 'Completed')                    AS done,
            SUM(1)                                        AS total
        FROM custom_orders WHERE customer_id = ?
    ");
    if ($statsQ) {
        $statsQ->bind_param("i", $cid); $statsQ->execute();
        $s = $statsQ->get_result()->fetch_assoc();
    }

    $apptCount = $conn->prepare("SELECT COUNT(*) c FROM appointments WHERE user_id = ? AND status != 'Rejected'");
    if ($apptCount) {
        $apptCount->bind_param("i", $cid); $apptCount->execute();
        $appt = $apptCount->get_result()->fetch_assoc()['c'];
    }
}

/* Orders needing customer action */
$actionOrders = [];
$aQ = $conn->prepare("
    SELECT id, project_name, quote_status, payment_status, quoted_price, quoted_deadline, quoted_breakdown
    FROM custom_orders
    WHERE customer_id = ? AND status NOT IN ('Completed','Cancelled')
      AND (quote_status = 'Approved' AND payment_status != 'Paid')
    ORDER BY created_at DESC
");
if ($aQ) { $aQ->bind_param("i",$cid); $aQ->execute(); $actionOrders = $aQ->get_result()->fetch_all(MYSQLI_ASSOC); }
?>

<div class="rh-main">

    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?> 👋</h1>
            <p>Here's what's happening with your projects today.</p>
        </div>
        <a href="customize.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Custom Order
        </a>
    </div>

    <?php if (!empty($actionOrders)): ?>
    <!-- ACTION ALERTS -->
    <?php foreach ($actionOrders as $ao): ?>
    <div class="alert border-0 shadow-sm d-flex align-items-start gap-3 mb-3" style="background:rgba(34,197,94,0.1); border-left:4px solid #22c55e !important;">
        <i class="fas fa-file-invoice-dollar text-success fs-4 mt-1"></i>
        <div class="flex-grow-1">
            <div class="fw-800">Quotation Approved — Payment Needed</div>
            <div class="small text-muted mb-1"><?= htmlspecialchars($ao['project_name'] ?? 'Custom Project') ?></div>
            <div class="small">
                <strong>Quoted Price:</strong> ₱<?= number_format($ao['quoted_price'], 2) ?> &nbsp;|
                <strong>Deadline:</strong> <?= $ao['quoted_deadline'] ? date('M d, Y', strtotime($ao['quoted_deadline'])) : 'TBD' ?>
            </div>
            <?php if (!empty($ao['quoted_breakdown'])): ?>
            <div class="small text-muted mt-1"><?= nl2br(htmlspecialchars($ao['quoted_breakdown'])) ?></div>
            <?php endif; ?>
        </div>
        <a href="../orders/view_order.php?id=<?= $ao['id'] ?>" class="btn btn-sm btn-success fw-700 flex-shrink-0">Upload Receipt</a>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="my_projects.php?status=ongoing" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-diagram-project"></i></div>
                <div>
                    <div class="rh-stat-label">Active Projects</div>
                    <div class="rh-stat-value"><?= $s['active'] ?? 0 ?></div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="my_projects.php?status=finished" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-green"><i class="fas fa-circle-check"></i></div>
                <div>
                    <div class="rh-stat-label">Completed</div>
                    <div class="rh-stat-value"><?= $s['done'] ?? 0 ?></div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="available_appointments.php" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-blue"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="rh-stat-label">Appointments</div>
                    <div class="rh-stat-value"><?= $appt ?></div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="my_projects.php" class="rh-stat-card text-decoration-none d-flex">
                <div class="rh-stat-icon bg-purple"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="rh-stat-label">Total Orders</div>
                    <div class="rh-stat-value"><?= $s['total'] ?? 0 ?></div>
                </div>
            </a>
        </div>
    </div>

    <!-- HERO CARD -->
    <div class="card rh-glass mb-4 border-0" style="background:linear-gradient(135deg,#0F172A 0%,#1E293B 100%);">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="fw-800 text-white mb-1"><i class="fas fa-pen-ruler me-2 text-amber"></i>Start a Custom Order</h4>
                <p class="text-secondary mb-0" style="font-size:.9rem;">
                    Design your project — gates, railings, trusses, grills & more. Our team handles the rest.
                </p>
            </div>
            <a href="customize.php" class="btn btn-warning fw-700 px-4">
                <i class="fas fa-paper-plane me-2"></i>Submit Request
            </a>
        </div>
    </div>

    <!-- RECENT PROJECTS -->
    <?php
    $recProj = [];
    if (!$conn->connect_error) {
        $recent = $conn->prepare("SELECT * FROM custom_orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 4");
        if ($recent) {
            $recent->bind_param("i",$cid); $recent->execute();
            $recProj = $recent->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
    ?>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-800 mb-0">Recent Projects</h5>
        <a href="my_projects.php" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>

    <?php if (empty($recProj)): ?>
        <div class="card p-5 text-center text-muted">
            <i class="fas fa-folder-open fs-1 mb-3 opacity-25"></i>
            <p>No projects yet. <a href="customize.php">Submit your first order</a>.</p>
        </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($recProj as $p):
            $pct = (int)($p['progress_percent'] ?? 0);
            if ($pct <= 0) $pct = ['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$p['status']] ?? 0;
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
            $payBadge = match($p['payment_status'] ?? 'Unpaid') {
                'Paid'                 => '<span class="badge bg-success-subtle text-success ms-1">Paid</span>',
                'Pending Verification' => '<span class="badge bg-warning-subtle text-warning ms-1">Verifying</span>',
                'Approved'             => '<span class="badge bg-info-subtle text-info ms-1">Pay Now</span>',
                default                => ''
            };
        ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="rh-proj-card">
                <div class="rh-proj-thumb">
                    <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>" alt="Project"
                         onerror="this.src='../assets/images/no-image.png'">
                    <span class="badge <?= $cls ?> status-float"><?= $p['status'] ?></span>
                </div>
                <div class="rh-proj-body">
                    <h6><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h6>
                    <p class="proj-meta"><?= htmlspecialchars($p['category'] ?? '') ?></p>
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;font-weight:700;">
                        <span>Progress</span><span><?= $pct ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div class="rh-proj-footer">
                        <small class="text-muted"><?= date('M d, Y',strtotime($p['created_at'])) ?></small>
                        <a href="project_details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning fw-700">Details</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- PRODUCT CATALOG SECTION -->
    <?php include 'dashboard_products.php'; ?>

</div>

</body></html>