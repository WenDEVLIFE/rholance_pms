<?php
include __DIR__ . '/../includes/auth_check.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit; }
$cid = $_SESSION['user_id'];
$sf  = $_GET['status'] ?? 'ongoing';
$map = [
    'ongoing'  => "'Appointment','Initial Payment','On-going','For Delivery','Backjobs'",
    'finished' => "'Completed'",
    'old'      => "'Cancelled'"
];
$filter = $map[$sf] ?? $map['ongoing'];
$stmt = $conn->prepare("SELECT * FROM custom_orders WHERE customer_id = ? AND status IN ($filter) ORDER BY created_at DESC");
$stmt->bind_param("i",$cid); $stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1>My Ongoing Projects</h1>
            <p>Track all your custom fabrication orders with real-time status updates.</p>
        </div>
    </div>

    <!-- FILTER TABS -->
    <div class="rh-tabs mb-4">
        <a href="?status=ongoing"  class="rh-tab <?= $sf==='ongoing' ?'active':'' ?>">Ongoing Projects</a>
        <a href="?status=finished" class="rh-tab <?= $sf==='finished'?'active':'' ?>">Completed Projects</a>
        <a href="?status=old"      class="rh-tab <?= $sf==='old'     ?'active':'' ?>">Old Cancelled</a>
    </div>

    <!-- NOTIFICATION BAR -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
        <div class="card p-5 text-center text-muted border-0 shadow-sm">
            <i class="fas fa-folder-open fs-1 mb-3 opacity-25 d-block text-amber"></i>
            No projects in this category.
        </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($projects as $p):
            $pct = (int)($p['progress_percent'] ?? 0);
            if ($pct <= 0) {
                $pct = ['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$p['status']] ?? 0;
            }
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
        ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="rh-proj-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="rh-proj-thumb">
                        <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>"
                             onerror="this.src='../assets/images/no-image.png'" alt="">
                        <span class="badge <?= $cls ?> status-float"><?= $p['status'] ?></span>
                    </div>
                    <div class="rh-proj-body">
                        <h6 class="fw-800 text-light-emphasis"><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h6>
                        <p class="proj-meta">
                            <span class="badge bg-light text-dark fw-600 me-1"><?= htmlspecialchars($p['category'] ?? '') ?></span>
                            <?= htmlspecialchars($p['material'] ?? '') ?>
                        </p>

                        <!-- Progress Bar -->
                        <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;font-weight:700;">
                            <span>Progress</span><span><?= $pct ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height:7px;">
                            <div class="progress-bar bg-amber" style="width:<?= $pct ?>%"></div>
                        </div>

                        <!-- Payment Action Prompts -->
                        <?php if (($p['quote_status'] ?? '') === 'Approved' && ($p['payment_status'] ?? 'Unpaid') !== 'Paid'): ?>
                            <div class="bg-success-subtle p-3 rounded-3 mb-3 border border-success-subtle">
                                <div class="fw-800 text-success small mb-1">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Quotation Approved — Payment Required
                                </div>
                                <p class="text-muted mb-2" style="font-size:0.75rem;">
                                    Quoted price: <strong>₱<?= number_format($p['quoted_price'] ?? 0, 2) ?></strong>.
                                    Please upload your GCash or bank transfer receipt.
                                </p>
                                <?php if (($p['payment_status'] ?? '') === 'Pending Verification'): ?>
                                    <div class="btn btn-sm btn-warning w-100 fw-700 disabled">
                                        <i class="fas fa-clock me-1"></i>Receipt Submitted — Verifying
                                    </div>
                                <?php else: ?>
                                    <a href="../orders/view_order.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-success w-100 fw-700">
                                        <i class="fas fa-upload me-1"></i>Upload Payment Receipt
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php elseif (($p['quote_status'] ?? 'Pending Review') === 'Pending Review' && !empty($p['assigned_welder_id'])): ?>
                            <div class="bg-warning-subtle p-3 rounded-3 mb-3 border border-warning-subtle">
                                <div class="fw-800 text-warning small mb-1">
                                    <i class="fas fa-hourglass-half me-1"></i>Welder Visit Scheduled
                                </div>
                                <p class="text-muted mb-0" style="font-size:0.75rem;">
                                    Our welder will visit on <strong><?= $p['welder_visit_date'] ? date('M d, Y', strtotime($p['welder_visit_date'])) : 'TBD' ?></strong>
                                    at <?= htmlspecialchars($p['welder_visit_time'] ?? 'TBD') ?>. Quotation pending.
                                </p>
                            </div>
                        <?php elseif ($p['status'] === 'Initial Payment'): ?>
                            <div class="bg-primary-subtle p-3 rounded-3 mb-3 border border-primary-subtle">
                                <div class="fw-800 text-primary small mb-1"><i class="fas fa-wallet me-1"></i>Initial Downpayment Awaiting</div>
                                <p class="text-muted mb-2" style="font-size: 0.7rem;">Please send the 50% downpayment via GCash and upload the receipt proof below to begin fabrication.</p>
                                <a href="add_payment.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary w-100 fw-700">
                                    <i class="fas fa-upload me-1"></i>Upload GCash Proof
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rh-proj-footer border-top pt-2 mt-auto p-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="far fa-calendar me-1"></i><?= date('M d, Y',strtotime($p['created_at'])) ?></small>
                    <a href="project_details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning fw-700">Track Progress</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body></html>