<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }
$cid = $_SESSION['user_id'];
$pid = (int)($_GET['id'] ?? 0);
if (!$pid) { header("Location: my_projects.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM custom_orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii",$pid,$cid); $stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header("Location: my_projects.php"); exit; }

$welders = $conn->prepare("SELECT u.name FROM tasks t JOIN users u ON u.id=t.assigned_to WHERE t.order_id=?");
$welders->bind_param("i",$pid); $welders->execute();
$welderRows = $welders->get_result()->fetch_all(MYSQLI_ASSOC);

$mats = $conn->prepare("SELECT oi.*,i.name item_name FROM order_items oi JOIN items i ON i.id=oi.item_id WHERE oi.order_id=?");
$mats->bind_param("i",$pid); $mats->execute();
$matRows = $mats->get_result()->fetch_all(MYSQLI_ASSOC);
$matTotal = array_sum(array_column($matRows,'total_amount'));

$pct = ['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$p['status']] ?? 0;
$cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
?>

<div class="rh-main">

    <!-- BACK + HEADER -->
    <div class="rh-page-header">
        <a href="my_projects.php" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Projects
        </a>
        <h1>Project Details</h1>
    </div>

    <div class="row g-4">

        <!-- LEFT COL -->
        <div class="col-12 col-lg-7">

            <!-- PROJECT INFO CARD -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-info-circle me-2 text-amber"></i>Project Information</span>
                    <span class="badge <?= $cls ?>"><?= $p['status'] ?></span>
                </div>
                <div class="card-body">
                    <h4 class="fw-800 mb-1"><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h4>
                    <p class="text-muted small mb-3">Submitted <?= date('F d, Y',strtotime($p['created_at'])) ?></p>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;font-weight:800;">Category</div>
                            <div class="fw-600"><?= htmlspecialchars($p['category'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;font-weight:800;">Material</div>
                            <div class="fw-600"><?= htmlspecialchars($p['material'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;font-weight:800;">Dimensions</div>
                            <div class="fw-600"><?= htmlspecialchars($p['dimensions'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;font-weight:800;">Est. Completion</div>
                            <div class="fw-600"><?= $p['estimated_completion'] ? date('M d, Y',strtotime($p['estimated_completion'])) : 'TBD' ?></div>
                        </div>
                    </div>

                    <!-- PROGRESS -->
                    <div class="d-flex justify-content-between mb-1" style="font-size:.8rem;font-weight:700;">
                        <span>Progress</span><span><?= $pct ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:10px;">
                        <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                    </div>

                    <?php if ($p['description'] || $p['instructions']): ?>
                    <div class="bg-light rounded-3 p-3">
                        <div class="text-muted mb-1" style="font-size:.7rem;font-weight:800;text-transform:uppercase;">Description</div>
                        <p class="mb-0 small"><?= nl2br(htmlspecialchars($p['description'] ?? $p['instructions'] ?? '')) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- MATERIALS TABLE -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-list-ul me-2 text-amber"></i>Materials Breakdown</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Material</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($matRows)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No materials listed yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($matRows as $m): ?>
                            <tr>
                                <td class="fw-600"><?= htmlspecialchars($m['item_name']) ?></td>
                                <td><?= $m['quantity'] ?></td>
                                <td>₱<?= number_format($m['price'],2) ?></td>
                                <td>₱<?= number_format($m['total_amount'],2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-warning fw-800">
                                <td colspan="3">Total Material Cost</td>
                                <td>₱<?= number_format($matTotal,2) ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RIGHT COL -->
        <div class="col-12 col-lg-5">

            <!-- ASSIGNED TEAM -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-hard-hat me-2 text-amber"></i>Assigned Team</div>
                <div class="card-body">
                    <?php if (empty($welderRows)): ?>
                        <p class="text-muted small mb-0">No personnel assigned yet.</p>
                    <?php else: ?>
                        <?php foreach ($welderRows as $w): ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rh-avatar" style="width:32px;height:32px;font-size:.75rem;">
                                <?= strtoupper(substr($w['name'],0,1)) ?>
                            </div>
                            <div><div class="fw-700 small"><?= htmlspecialchars($w['name']) ?></div>
                                 <div class="text-muted" style="font-size:.7rem;">Welder</div></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- EXPECTATION VS REALITY -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-eye me-2 text-amber"></i>Expectation vs Reality</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted mb-1" style="font-size:.7rem;font-weight:800;text-transform:uppercase;">Reference</div>
                            <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>"
                                 class="img-fluid rounded-3 border" style="height:120px;width:100%;object-fit:cover;"
                                 onerror="this.src='../assets/images/no-image.png'" alt="Expectation">
                        </div>
                        <div class="col-6">
                            <div class="text-muted mb-1" style="font-size:.7rem;font-weight:800;text-transform:uppercase;">Actual Progress</div>
                            <img src="../<?= $p['reference_image'] ?? 'assets/images/no-image.png' ?>"
                                 class="img-fluid rounded-3 border" style="height:120px;width:100%;object-fit:cover;"
                                 onerror="this.src='../assets/images/no-image.png'" alt="Reality">
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENT -->
            <div class="card">
                <div class="card-header"><i class="fas fa-credit-card me-2 text-amber"></i>Payment</div>
                <div class="card-body text-center">
                    <div class="mb-1 text-muted small">Estimated Total</div>
                    <div class="fw-800 fs-4 mb-3">₱<?= number_format($matTotal*1.5,2) ?> <small class="text-muted fs-6">incl. labor</small></div>
                    <a href="add_payment.php?id=<?= $p['id'] ?>" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-2"></i>Upload Payment Proof
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

</body></html>
