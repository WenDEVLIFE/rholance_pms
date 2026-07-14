<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'welder') { header("Location: ../index.php"); exit; }

$wid    = $_SESSION['user_id'];
$branch = $_SESSION['branch_id'] ?? 1;
$branchName = $branch == 1 ? 'Cavite (Dasmariñas)' : 'Laguna (Biñan)';

/* My name */
$welderName = $_SESSION['name'] ?? 'Welder';

/* 1. New assignments — assigned but no quote yet */
$newQ = $conn->prepare("
    SELECT co.id, co.project_name, co.status, co.created_at, co.image,
           co.welder_visit_date, co.welder_visit_time,
           co.customer_sketch, co.description,
           co.category, co.material, co.dimensions, co.instructions,
           u.name cust_name, u.phone cust_phone, u.address cust_address
    FROM custom_orders co
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE co.assigned_welder_id = ?
      AND (co.quoted_price IS NULL OR co.quoted_price = 0)
      AND co.status NOT IN ('Completed','Cancelled')
    ORDER BY co.welder_visit_date ASC
");
$newQ->bind_param("i", $wid); $newQ->execute();
$newAssignments = $newQ->get_result()->fetch_all(MYSQLI_ASSOC);

/* 2. Active — quote submitted, payment done, work in progress */
$activeQ = $conn->prepare("
    SELECT co.id, co.project_name, co.status, co.created_at, co.image,
           co.quoted_price, co.quoted_deadline, co.quoted_breakdown,
           co.quote_status, co.payment_status,
           co.progress_percent, co.progress_details, co.progress_status,
           co.customer_sketch,
           u.name cust_name, u.phone cust_phone, u.address cust_address
    FROM custom_orders co
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE co.assigned_welder_id = ?
      AND co.quoted_price > 0
      AND co.status NOT IN ('Completed','Cancelled')
    ORDER BY co.created_at DESC
");
$activeQ->bind_param("i", $wid); $activeQ->execute();
$activeProjects = $activeQ->get_result()->fetch_all(MYSQLI_ASSOC);

/* 3. Completed */
$doneQ = $conn->prepare("
    SELECT co.id, co.project_name, co.status, co.created_at, co.image,
           u.name cust_name
    FROM custom_orders co
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE co.assigned_welder_id = ? AND co.status = 'Completed'
    ORDER BY co.created_at DESC LIMIT 10
");
$doneQ->bind_param("i", $wid); $doneQ->execute();
$completedList = $doneQ->get_result()->fetch_all(MYSQLI_ASSOC);

/* Counts */
$totalAssigned  = count($newAssignments) + count($activeProjects);
$pendingQuotes  = count(array_filter($activeProjects, fn($p) => ($p['quote_status'] ?? '') === 'Pending Review'));
$pendingProgress = count(array_filter($activeProjects, fn($p) => ($p['progress_status'] ?? '') === 'Pending Approval'));
?>

<div class="rh-main">

    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1><i class="fas fa-hard-hat me-2 text-amber"></i>Welder Workspace</h1>
            <p>Welcome, <strong><?= htmlspecialchars($welderName) ?></strong>! Branch: <strong><?= $branchName ?></strong></p>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-hard-hat"></i></div>
                <div>
                    <div class="rh-stat-label">Assigned Projects</div>
                    <div class="rh-stat-value"><?= $totalAssigned ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-blue"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="rh-stat-label">Quotes Pending Review</div>
                    <div class="rh-stat-value"><?= $pendingQuotes ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-purple"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="rh-stat-label">Progress Pending Approval</div>
                    <div class="rh-stat-value"><?= $pendingProgress ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-green"><i class="fas fa-flag-checkered"></i></div>
                <div>
                    <div class="rh-stat-label">Completed</div>
                    <div class="rh-stat-value"><?= count($completedList) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SECTION 1: NEW ASSIGNMENTS ==================== -->
    <?php if (!empty($newAssignments)): ?>
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="badge bg-warning text-dark px-3 py-2 fw-800"><?= count($newAssignments) ?> New</span>
        <h5 class="mb-0 fw-800">New Assignments — Submit Your Quotation</h5>
    </div>
    <div class="row g-3 mb-5">
        <?php foreach ($newAssignments as $p): ?>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <span class="fw-800 text-light-emphasis">
                        <i class="fas fa-calendar-check me-2 text-amber"></i>
                        <?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?>
                    </span>
                    <span class="badge bg-warning text-dark">Visit Scheduled</span>
                </div>
                <div class="card-body border-top">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">CUSTOMER</span>
                            <span class="fw-600"><?= htmlspecialchars($p['cust_name'] ?? '—') ?></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">PHONE</span>
                            <span class="fw-600"><?= htmlspecialchars($p['cust_phone'] ?? '—') ?></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">VISIT DATE</span>
                            <span class="fw-600 text-amber">
                                <?= $p['welder_visit_date'] ? date('M d, Y', strtotime($p['welder_visit_date'])) : 'TBD' ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block small fw-700">VISIT TIME</span>
                            <span class="fw-600"><?= htmlspecialchars($p['welder_visit_time'] ?? 'TBD') ?></span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block small fw-700">ADDRESS</span>
                            <span class="small"><?= htmlspecialchars($p['cust_address'] ?? '—') ?></span>
                        </div>
                    </div>

                    <?php if (!empty($p['customer_sketch'])): ?>
                    <div class="mb-3">
                        <span class="text-muted d-block small fw-700 mb-1">CUSTOMER SKETCH</span>
                        <a href="../<?= htmlspecialchars($p['customer_sketch']) ?>" target="_blank">
                            <img src="../<?= htmlspecialchars($p['customer_sketch']) ?>" class="img-fluid rounded border" style="max-height:120px;">
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($p['instructions'])): ?>
                    <div class="alert alert-light border small mb-3">
                        <strong>Instructions:</strong> <?= nl2br(htmlspecialchars($p['instructions'])) ?>
                    </div>
                    <?php endif; ?>

                    <!-- SUBMIT QUOTATION FORM -->
                    <div class="border-top pt-3">
                        <p class="small fw-700 text-amber mb-2"><i class="fas fa-file-invoice me-1"></i>After visiting, submit your price quote:</p>
                        <form action="../orders/submit_quote.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= $p['id'] ?>">
                            <div class="mb-2">
                                <label class="form-label small fw-700 mb-1">Quoted Price (₱)</label>
                                <input type="number" step="0.01" name="quoted_price" class="form-control form-control-sm" placeholder="e.g. 15000" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-700 mb-1">Estimated Deadline</label>
                                <input type="date" name="quoted_deadline" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-700 mb-1">Pricing Breakdown / Material Specs</label>
                                <textarea name="quoted_breakdown" class="form-control form-control-sm" rows="3" placeholder="e.g. Materials: ₱8,000 | Labor: ₱5,000 | Finishing: ₱2,000" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning fw-800 w-100">
                                <i class="fas fa-paper-plane me-1"></i>Submit Quotation to Cashier
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ==================== SECTION 2: ACTIVE PROJECTS ==================== -->
    <?php if (!empty($activeProjects)): ?>
    <h5 class="fw-800 mb-3"><i class="fas fa-fire me-2 text-amber"></i>Active Projects</h5>
    <div class="row g-3 mb-5">
        <?php foreach ($activeProjects as $p):
            $pct = (int)($p['progress_percent'] ?? 0);
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
        ?>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <span class="fw-800 text-light-emphasis">
                        <?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?>
                    </span>
                    <span class="badge <?= $cls ?>"><?= $p['status'] ?></span>
                </div>
                <div class="card-body border-top">

                    <!-- Quote Status -->
                    <div class="mb-3 p-2 rounded-3 small <?= $p['quote_status'] === 'Approved' ? 'bg-success-subtle' : 'bg-warning-subtle' ?>">
                        <strong>Quote:</strong> <?= htmlspecialchars($p['quote_status'] ?? 'Pending') ?> &nbsp;|&nbsp;
                        <strong>Payment:</strong> <?= htmlspecialchars($p['payment_status'] ?? 'Unpaid') ?>
                        <?php if ($p['payment_status'] === 'Paid'): ?>
                            <span class="badge bg-success ms-1">✓ Paid</span>
                        <?php endif; ?>
                    </div>

                    <!-- Progress -->
                    <div class="mb-1 d-flex justify-content-between small fw-700">
                        <span>Fabrication Progress</span>
                        <span class="text-amber"><?= $pct ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:8px; border-radius:4px;">
                        <div class="progress-bar bg-amber" style="width:<?= $pct ?>%; border-radius:4px;"></div>
                    </div>

                    <?php if (!empty($p['progress_details'])): ?>
                    <div class="small text-muted mb-3 p-2 bg-light rounded">
                        <strong>Last Update:</strong> <?= nl2br(htmlspecialchars($p['progress_details'])) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($p['customer_sketch'])): ?>
                    <div class="mb-3">
                        <span class="text-muted d-block small fw-700 mb-1">CUSTOMER SKETCH</span>
                        <a href="../<?= htmlspecialchars($p['customer_sketch']) ?>" target="_blank">
                            <img src="../<?= htmlspecialchars($p['customer_sketch']) ?>" class="img-fluid rounded border" style="max-height:100px;">
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="border-top pt-3">
                        <?php if ($p['payment_status'] === 'Paid'): ?>
                            <?php if (($p['progress_status'] ?? '') !== 'Pending Approval'): ?>
                            <!-- Update Progress Form -->
                            <form action="../orders/update_progress.php" method="POST">
                                <input type="hidden" name="order_id" value="<?= $p['id'] ?>">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <input type="number" name="progress_percent" min="0" max="100"
                                               value="<?= $pct ?>" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-8 small text-muted d-flex align-items-center">% completion</div>
                                </div>
                                <textarea name="progress_details" class="form-control form-control-sm mb-2" rows="2"
                                          placeholder="Progress update notes..." required></textarea>
                                <button type="submit" class="btn btn-sm btn-warning fw-700 w-100">
                                    <i class="fas fa-arrow-up me-1"></i>Submit Progress for Approval
                                </button>
                            </form>
                            <?php else: ?>
                            <div class="alert alert-warning border-0 py-2 small fw-700 text-center mb-0">
                                <i class="fas fa-hourglass-half me-1"></i>Progress submitted — awaiting cashier approval
                            </div>
                            <?php endif; ?>
                        <?php elseif (($p['payment_status'] ?? '') === 'Pending Verification'): ?>
                            <div class="alert alert-info border-0 py-2 small fw-700 text-center mb-0">
                                <i class="fas fa-clock me-1"></i>Customer receipt submitted — cashier is verifying payment
                            </div>
                        <?php elseif (($p['quote_status'] ?? '') === 'Approved'): ?>
                            <div class="alert alert-success border-0 py-2 small fw-700 text-center mb-0">
                                <i class="fas fa-check me-1"></i>Quote approved — waiting for customer payment
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary border-0 py-2 small fw-700 text-center mb-0">
                                <i class="fas fa-hourglass-half me-1"></i>Quotation pending cashier review
                            </div>
                        <?php endif; ?>

                        <div class="mt-2 text-center">
                            <a href="../orders/view_order.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye me-1"></i>Full Project Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ==================== SECTION 3: COMPLETED ==================== -->
    <?php if (!empty($completedList)): ?>
    <h5 class="fw-800 mb-3"><i class="fas fa-flag-checkered me-2 text-green"></i>Completed Projects</h5>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Project</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completedList as $p): ?>
                    <tr>
                        <td class="text-muted small">#<?= $p['id'] ?></td>
                        <td class="fw-700"><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></td>
                        <td class="small"><?= htmlspecialchars($p['cust_name'] ?? '—') ?></td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <a href="../orders/view_order.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($newAssignments) && empty($activeProjects) && empty($completedList)): ?>
    <div class="card border-0 shadow-sm p-5 text-center text-muted">
        <i class="fas fa-hard-hat fs-1 mb-3 opacity-25 d-block text-amber"></i>
        <p>No projects assigned to you yet. Check back with your cashier!</p>
    </div>
    <?php endif; ?>

</div>
</body></html>
