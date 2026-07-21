<?php
include '../../includes/auth_check.php';
include '../../config/database.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { header("Location: ../../index.php"); exit; }

$branch = $_SESSION['branch_id'] ?? 1;
$branchName = $branch == 1 ? 'Dasmariñas Branch (Cavite)' : 'Biñan Branch (Laguna)';

// Sales analytics
$today = date('Y-m-d');
$thisMonth = date('Y-m');
$thisYear = date('Y');

$salesDay   = $conn->query("SELECT COALESCE(SUM(amount_paid),0) as t FROM pos_payments WHERE DATE(paid_at)='$today' AND branch_id=$branch")->fetch_assoc()['t'] ?? 0;
$salesMonth = $conn->query("SELECT COALESCE(SUM(amount_paid),0) as t FROM pos_payments WHERE DATE_FORMAT(paid_at,'%Y-%m')='$thisMonth' AND branch_id=$branch")->fetch_assoc()['t'] ?? 0;
$salesYear  = $conn->query("SELECT COALESCE(SUM(amount_paid),0) as t FROM pos_payments WHERE YEAR(paid_at)='$thisYear' AND branch_id=$branch")->fetch_assoc()['t'] ?? 0;

// Fallback to transactions table if pos_payments doesn't exist
if (!$conn->query("SHOW TABLES LIKE 'pos_payments'")->num_rows) {
    $salesDay   = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM transactions WHERE DATE(created_at)='$today' AND status='Paid'")->fetch_assoc()['t'] ?? 0;
    $salesMonth = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM transactions WHERE DATE_FORMAT(created_at,'%Y-%m')='$thisMonth' AND status='Paid'")->fetch_assoc()['t'] ?? 0;
    $salesYear  = $conn->query("SELECT COALESCE(SUM(total_amount),0) as t FROM transactions WHERE YEAR(created_at)='$thisYear' AND status='Paid'")->fetch_assoc()['t'] ?? 0;
}

// Active projects
$projects = $conn->query("
    SELECT co.id, co.project_name, co.customer_name, co.status, co.image, co.category,
           co.created_at, co.quoted_price, co.quoted_breakdown, co.payment_status,
           co.progress_percent, co.assigned_welder_id,
           u.name welder_name, u.phone welder_phone,
           COALESCE((SELECT SUM(oi.total_amount) FROM order_items oi WHERE oi.order_id = co.id), 0) AS material_cost
    FROM custom_orders co
    LEFT JOIN users u ON u.id = co.assigned_welder_id
    WHERE (co.branch_id = $branch OR co.branch_id IS NULL)
      AND co.status NOT IN ('Completed', 'Cancelled')
    ORDER BY co.created_at DESC
");
$projectList = $projects ? $projects->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="rh-main">
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1>Project POS Terminal</h1>
            <p>Process customer downpayments and final balance payments for active fabrication projects in <strong><?= $branchName ?></strong></p>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['err']) ?></div>
    <?php endif; ?>

    <!-- SALES ANALYTICS STRIP -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm p-3 text-center pos-stat-card">
                <div class="small fw-800 text-muted mb-1"><i class="fas fa-calendar-day me-1 text-amber"></i>TODAY</div>
                <div class="fw-800 fs-5 text-success">₱<?= number_format($salesDay, 2) ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm p-3 text-center pos-stat-card">
                <div class="small fw-800 text-muted mb-1"><i class="fas fa-calendar-week me-1 text-amber"></i>THIS MONTH</div>
                <div class="fw-800 fs-5 text-primary">₱<?= number_format($salesMonth, 2) ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm p-3 text-center pos-stat-card">
                <div class="small fw-800 text-muted mb-1"><i class="fas fa-calendar me-1 text-amber"></i>THIS YEAR</div>
                <div class="fw-800 fs-5 text-warning">₱<?= number_format($salesYear, 2) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- LEFT PANEL: ACTIVE PROJECTS -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-800"><i class="fas fa-diagram-project me-2 text-amber"></i>Active Custom Projects</span>
                    <div style="width:220px;">
                        <input type="text" id="posSearch" class="form-control form-control-sm" placeholder="Search customer or project...">
                    </div>
                </div>
                <div class="card-body border-top p-3" style="max-height: 620px; overflow-y: auto;">
                    <?php if (empty($projectList)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-1 d-block mb-3 opacity-25 text-amber"></i>
                            No active fabrication projects requiring billing in this branch.
                        </div>
                    <?php else: ?>
                    <div class="d-flex flex-column gap-3" id="projectListContainer">
                        <?php foreach ($projectList as $p):
                            $mCost = (float)$p['material_cost'];
                            $quotedPrice = (float)($p['quoted_price'] ?? 0);
                            $totalVal = $quotedPrice > 0 ? $quotedPrice : ($mCost > 0 ? $mCost * 1.5 : 5000.00);
                            $progressPct = (int)($p['progress_percent'] ?? 0);

                            $statusColors = [
                                'Appointment'    => 'bg-secondary',
                                'Initial Payment'=> 'bg-info text-dark',
                                'On-going'       => 'bg-primary',
                                'For Delivery'   => 'bg-warning text-dark',
                                'Backjobs'       => 'bg-danger',
                                'Completed'      => 'bg-success',
                            ];
                            $sBadge = $statusColors[$p['status']] ?? 'bg-secondary';

                            $payStatus = $p['payment_status'] ?? 'Unpaid';
                            $payIcon = match($payStatus) {
                                'Paid'                 => '<i class="fas fa-check-circle text-success" title="Fully Paid"></i>',
                                'Pending Verification' => '<i class="fas fa-clock text-warning" title="Payment Verifying"></i>',
                                default                => '<i class="fas fa-circle-minus text-muted" title="Unpaid"></i>',
                            };

                            $projJson = json_encode([
                                'id'              => $p['id'],
                                'name'            => $p['project_name'] ?? 'Custom Project',
                                'customer'        => $p['customer_name'] ?? 'Guest Client',
                                'status'          => $p['status'],
                                'material_cost'   => $mCost,
                                'total'           => $totalVal,
                                'breakdown'       => $p['quoted_breakdown'] ?? '',
                                'welder'          => $p['welder_name'] ?? 'Not assigned',
                                'welder_phone'    => $p['welder_phone'] ?? '—',
                                'progress'        => $progressPct,
                                'payment_status'  => $payStatus,
                            ]);
                        ?>
                        <div class="pos-project-item" data-project-json='<?= htmlspecialchars($projJson, ENT_QUOTES) ?>'>
                            <div class="card border p-3 rounded-3 shadow-sm pos-project-card"
                                 style="cursor:pointer;" onclick="selectProject(this)">

                                <div class="d-flex gap-3 align-items-start">
                                    <!-- Thumbnail -->
                                    <img src="<?= BASE_URL ?><?= $p['image'] ?? 'assets/images/no-image.png' ?>"
                                         class="rounded border object-fit-cover flex-shrink-0"
                                         style="width:64px; height:64px;"
                                         onerror="this.src='<?= BASE_URL ?>assets/images/no-image.png'">

                                    <!-- Info -->
                                    <div class="flex-fill min-width-0">
                                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                            <h6 class="fw-800 mb-0 text-truncate"><?= htmlspecialchars($p['project_name'] ?? 'Custom Project') ?></h6>
                                            <span class="badge <?= $sBadge ?> flex-shrink-0"><?= htmlspecialchars($p['status']) ?></span>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($p['customer_name'] ?? 'Guest Client') ?>
                                            <?php if (!empty($p['welder_name'])): ?>
                                                &nbsp;·&nbsp;<i class="fas fa-hard-hat me-1"></i><?= htmlspecialchars($p['welder_name']) ?>
                                            <?php endif; ?>
                                            &nbsp;· <?= $payIcon ?>
                                        </div>

                                        <!-- Mini progress bar -->
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-fill" style="height:5px;">
                                                <div class="progress-bar bg-amber" style="width:<?= $progressPct ?>%; background:#f59e0b;"></div>
                                            </div>
                                            <span class="small text-muted"><?= $progressPct ?>%</span>
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="text-end flex-shrink-0">
                                        <div class="fw-800 text-success">₱<?= number_format($totalVal, 2) ?></div>
                                        <div class="small text-muted"><?= $quotedPrice > 0 ? 'Quoted' : 'Estimated' ?></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: CHECKOUT DESK -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white py-3">
                    <span class="fw-800"><i class="fas fa-cash-register me-2 text-amber"></i>Project Checkout Desk</span>
                </div>

                <form action="process_pos.php" method="POST" id="checkoutForm">
                    <input type="hidden" name="order_id" id="checkoutProjectId">
                    <input type="hidden" name="payment_type" id="checkoutPaymentType" value="full">
                    <input type="hidden" name="contract_total" id="checkoutContractTotal">

                    <div class="card-body p-4" id="billingBody">

                        <!-- Empty State -->
                        <div class="text-center py-5" id="emptyCheckoutPrompt">
                            <i class="fas fa-hand-pointer fs-2 mb-3 d-block opacity-25 text-amber"></i>
                            <p class="text-muted">Select an active custom project on the left to begin payment processing.</p>
                        </div>

                        <!-- Checkout Panel -->
                        <div class="d-none" id="checkoutPanel">

                            <!-- Project Summary -->
                            <div class="mb-3 pb-3 border-bottom">
                                <span class="text-muted small fw-700 d-block mb-1">BILLING PROJECT</span>
                                <h5 class="fw-800 m-0" id="billingProjectName">—</h5>
                                <span class="text-muted small" id="billingCustomerName">Client: —</span>
                            </div>

                            <!-- Assigned Worker Info -->
                            <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2);">
                                <i class="fas fa-hard-hat text-amber"></i>
                                <div>
                                    <div class="fw-800 small">ASSIGNED WELDER</div>
                                    <div class="fw-700" id="billingWelder">—</div>
                                    <div class="text-muted small" id="billingWelderPhone">—</div>
                                </div>
                            </div>

                            <!-- Price Estimation Breakdown -->
                            <div class="mb-3 p-3 rounded-3" style="background: rgba(0,0,0,0.04); border:1px solid rgba(0,0,0,0.08);">
                                <div class="fw-800 small mb-2 text-muted">PRICE ESTIMATION BREAKDOWN</div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-700">Raw Material Cost</span>
                                    <span class="fw-700" id="billingMaterialCost">₱0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-700">Fabrication Labor (50%)</span>
                                    <span class="fw-700" id="billingLaborCost">₱0.00</span>
                                </div>
                                <div id="breakdownDetails" class="small text-muted mb-2 fst-italic"></div>
                                <hr class="my-2 opacity-25">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-800">Contract Total</span>
                                    <span class="fw-800 text-success fs-5" id="billingTotalVal">₱0.00</span>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="mb-3">
                                <label class="form-label small fw-800">PAYMENT OPTION</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-amber w-100 py-2 active" id="btnDownpayment" onclick="setPaymentMode('down')">
                                            <div class="fw-800 small">50% Downpayment</div>
                                            <div class="fw-700 small" id="downpaymentLabelVal">₱0.00</div>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-amber w-100 py-2" id="btnFullpayment" onclick="setPaymentMode('full')">
                                            <div class="fw-800 small">Full / Final</div>
                                            <div class="fw-700 small" id="fullpaymentLabelVal">₱0.00</div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Input -->
                            <div class="mb-3">
                                <label class="form-label small fw-800">AMOUNT RECEIVED (₱)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-transparent fw-800">₱</span>
                                    <input type="number" step="0.01" name="amount_paid" id="paymentInput"
                                           class="form-control fw-800 text-center" placeholder="0.00" required>
                                </div>
                            </div>

                            <!-- Change -->
                            <div class="d-flex justify-content-between align-items-center p-3 bg-success-subtle text-success rounded-3 d-none" id="changeContainer">
                                <span class="fw-800">Cash Change:</span>
                                <h4 class="fw-800 m-0" id="changeAmount">₱0.00</h4>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-white p-3 border-0">
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-800 shadow" id="btnSubmitCheckout" disabled>
                            <i class="fas fa-check-circle me-2"></i>Complete Checkout Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.pos-stat-card { transition: transform .15s; }
.pos-stat-card:hover { transform: translateY(-2px); }
.pos-project-card { transition: border-color .15s, box-shadow .15s; }
.pos-project-card:hover { box-shadow: 0 4px 16px rgba(245,158,11,.18) !important; border-color: rgba(245,158,11,.4) !important; }
.pos-project-card.selected { border-color: #f59e0b !important; background: rgba(245,158,11,0.06) !important; }
body.dark .pos-stat-card { background: #1e293b !important; }
body.dark .pos-project-card { background: #1e293b !important; border-color: #334155 !important; }
body.dark .pos-project-card.selected { border-color: #f59e0b !important; background: rgba(245,158,11,0.08) !important; }
</style>

<script>
let selectedProjData = null;
let activePaymentMode = 'down';
let paymentAmountDue = 0;

function selectProject(card) {
    document.querySelectorAll('.pos-project-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    const data = JSON.parse(card.closest('.pos-project-item').dataset.projectJson);
    selectedProjData = data;

    document.getElementById('emptyCheckoutPrompt').classList.add('d-none');
    document.getElementById('checkoutPanel').classList.remove('d-none');
    document.getElementById('btnSubmitCheckout').disabled = false;

    document.getElementById('checkoutProjectId').value = data.id;
    document.getElementById('checkoutContractTotal').value = data.total;

    document.getElementById('billingProjectName').textContent = data.name;
    document.getElementById('billingCustomerName').textContent = 'Client: ' + data.customer + ' · ' + data.status;

    document.getElementById('billingWelder').textContent = data.welder;
    document.getElementById('billingWelderPhone').textContent = data.welder_phone !== '—' ? '📞 ' + data.welder_phone : 'No phone on record';

    const mat   = parseFloat(data.material_cost);
    const labor = mat * 0.5;
    const total = parseFloat(data.total);

    document.getElementById('billingMaterialCost').textContent = '₱' + mat.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('billingLaborCost').textContent    = '₱' + labor.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('billingTotalVal').textContent     = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});

    const breakdown = document.getElementById('breakdownDetails');
    breakdown.textContent = data.breakdown ? 'Notes: ' + data.breakdown : '';

    const downVal = total / 2;
    document.getElementById('downpaymentLabelVal').textContent = '₱' + downVal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('fullpaymentLabelVal').textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});

    setPaymentMode('down');
    document.getElementById('paymentInput').value = '';
    document.getElementById('changeContainer').classList.add('d-none');
}

function setPaymentMode(mode) {
    activePaymentMode = mode;
    document.getElementById('checkoutPaymentType').value = mode;

    const btnDown = document.getElementById('btnDownpayment');
    const btnFull = document.getElementById('btnFullpayment');

    if (mode === 'down') {
        btnDown.classList.add('active');
        btnFull.classList.remove('active');
        paymentAmountDue = selectedProjData.total / 2;
    } else {
        btnFull.classList.add('active');
        btnDown.classList.remove('active');
        paymentAmountDue = selectedProjData.total;
    }
    calculateChange();
}

function calculateChange() {
    const payAmt = parseFloat(document.getElementById('paymentInput').value) || 0;
    const changeEl = document.getElementById('changeAmount');
    const changeContainer = document.getElementById('changeContainer');

    if (payAmt >= paymentAmountDue && paymentAmountDue > 0) {
        changeContainer.classList.remove('d-none');
        changeEl.textContent = '₱' + (payAmt - paymentAmountDue).toLocaleString('en-US', {minimumFractionDigits: 2});
    } else {
        changeContainer.classList.add('d-none');
    }
}

document.getElementById('paymentInput').addEventListener('input', calculateChange);

document.getElementById('posSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.pos-project-item').forEach(item => {
        const data = JSON.parse(item.dataset.projectJson);
        item.style.display = (data.name.toLowerCase().includes(q) || data.customer.toLowerCase().includes(q)) ? '' : 'none';
    });
});

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const payAmt = parseFloat(document.getElementById('paymentInput').value) || 0;
    if (payAmt < paymentAmountDue) {
        alert("Insufficient amount. Total due: ₱" + paymentAmountDue.toLocaleString());
        e.preventDefault();
    }
});
</script>
</body></html>