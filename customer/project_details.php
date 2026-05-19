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

$pct = (int)($p['progress_percent'] ?? 0);
if ($pct <= 0) {
    $pct = ['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$p['status']] ?? 0;
}
$cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
?>

<div class="rh-main">

    <!-- BACK + HEADER -->
    <div class="rh-page-header">
        <a href="my_projects.php" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fas fa-arrow-left me-1"></i>Back to My Projects
        </a>
        <h1>Project Details</h1>
    </div>

    <!-- SUCCESS MESSAGE -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i>Proof of payment uploaded successfully! Staff will verify it shortly.</div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- LEFT COL -->
        <div class="col-12 col-lg-7">

            <!-- PROJECT INFO CARD -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-info-circle me-2 text-amber"></i>Project Information</span>
                    <span class="badge <?= $cls ?>"><?= $p['status'] ?></span>
                </div>
                <div class="card-body border-top">
                    <h4 class="fw-800 text-light-emphasis mb-1"><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h4>
                    <p class="text-muted small mb-3">Submitted <?= date('F d, Y',strtotime($p['created_at'])) ?></p>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="text-muted small fw-700">FABRICATION CATEGORY</div>
                            <div class="fw-600 text-light-emphasis"><?= htmlspecialchars($p['category'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-700">CORE RAW MATERIAL</div>
                            <div class="fw-600 text-light-emphasis"><?= htmlspecialchars($p['material'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-700">DIMENSIONS</div>
                            <div class="fw-600 text-light-emphasis"><?= htmlspecialchars($p['dimensions'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-700">ESTIMATED COMPLETION</div>
                            <div class="fw-600 text-light-emphasis text-amber">
                                <?= $p['estimated_completion'] ? date('M d, Y',strtotime($p['estimated_completion'])) : 'TBD (Following Welder Inspection)' ?>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS -->
                    <div class="d-flex justify-content-between mb-1 small fw-700 text-light-emphasis">
                        <span>Fabrication Progress</span><span><?= $pct ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:10px; border-radius:5px;">
                        <div class="progress-bar bg-amber" style="width:<?= $pct ?>%; border-radius:5px;"></div>
                    </div>

                    <?php if (!empty($p['progress_details'])): ?>
                        <div class="alert alert-warning border-0 p-3 rounded-3 mt-3" style="background: rgba(245,158,11,0.08); color: var(--rh-amber);">
                            <div class="fw-800 mb-1" style="font-size:0.85rem;"><i class="fas fa-hammer me-2"></i>Active Build Update from Welder</div>
                            <p class="mb-0 small fw-600 text-light-emphasis" style="font-size:0.75rem;"><?= htmlspecialchars($p['progress_details']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- MATERIALS BREAKDOWN / PRICE BREAKDOWN -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0"><i class="fas fa-list-ul me-2 text-amber"></i>Materials Pricing Breakdown</div>
                <div class="table-responsive border-top">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr><th>Material</th><th>Qty</th><th>Unit Price</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($matRows)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No materials checked out yet. Price breakdown will show after welder inspection.</td></tr>
                        <?php else: ?>
                            <?php foreach ($matRows as $m): ?>
                            <tr>
                                <td class="fw-600 text-light-emphasis"><?= htmlspecialchars($m['item_name']) ?></td>
                                <td><?= $m['quantity'] ?></td>
                                <td>₱<?= number_format($m['price'],2) ?></td>
                                <td class="text-end fw-700">₱<?= number_format($m['total_amount'],2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-800 border-top">
                                <td colspan="3" class="text-light-emphasis">Raw Materials Total</td>
                                <td class="text-end text-light-emphasis">₱<?= number_format($matTotal,2) ?></td>
                            </tr>
                            <tr class="table-warning fw-800">
                                <td colspan="3" class="text-amber">Estimated Contract Valuation (Incl. 50% Labor)</td>
                                <td class="text-end text-success">₱<?= number_format($matTotal * 1.5, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RIGHT COL -->
        <div class="col-12 col-lg-5">

            <!-- ASSIGNED WELDER TEAM -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0"><i class="fas fa-hard-hat me-2 text-amber"></i>Assigned Fabrication Welder</div>
                <div class="card-body border-top">
                    <?php if (empty($welderRows)): ?>
                        <p class="text-muted small mb-0">Welder team will be assigned following scheduling confirmation.</p>
                    <?php else: ?>
                        <?php foreach ($welderRows as $w): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rh-avatar" style="width:40px;height:40px;font-size:.9rem; background: var(--rh-amber); color: #000;">
                                <?= strtoupper(substr($w['name'],0,1)) ?>
                            </div>
                            <div>
                                <div class="fw-800 text-light-emphasis"><?= htmlspecialchars($w['name']) ?></div>
                                <div class="text-muted small">Assigned Fabricator</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PAYMENT UPLOAD PORTAL -->
            <?php if ($p['status'] === 'Initial Payment'): ?>
            <div class="card border-0 shadow-sm border-amber-light">
                <div class="card-header bg-primary text-white py-3">
                    <span class="fw-800"><i class="fas fa-credit-card me-2"></i>Initial 50% Downpayment Portal</span>
                </div>
                <div class="card-body text-center p-4">
                    <div class="mb-1 text-muted small">50% Downpayment Required</div>
                    <h3 class="fw-800 text-success mb-3">₱<?= number_format(($matTotal > 0 ? $matTotal * 1.5 : 5000) / 2, 2) ?></h3>
                    
                    <div class="alert bg-primary-subtle text-primary border-0 text-start small mb-3">
                        <i class="fas fa-info-circle me-1"></i>Please transfer via GCash to: <strong>0995 774 2174 (Rholance Trading)</strong>. Then click the button below to upload your transaction receipt.
                    </div>

                    <a href="add_payment.php?id=<?= $p['id'] ?>" class="btn btn-primary w-100 py-2 fw-800">
                        <i class="fas fa-upload me-2"></i>Upload GCash Screenshot
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm bg-light-subtle">
                <div class="card-header bg-white py-3 border-0"><i class="fas fa-receipt me-2 text-amber"></i>Billing Valuation</div>
                <div class="card-body border-top text-center p-4">
                    <span class="text-muted small d-block mb-1">Contract Valuation</span>
                    <h2 class="fw-800 text-success">₱<?= number_format($matTotal * 1.5, 2) ?></h2>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 mt-2">Fabrication Phase: <?= htmlspecialchars($p['status']) ?></span>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body></html>
