<?php
include '../../includes/auth_check.php';
include '../../config/database.php';
include '../../includes/header.php';
include '../../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { header("Location: ../../index.php"); exit; }

$branch = $_SESSION['branch_id'] ?? 1;
$branchName = $branch == 1 ? 'Dasmariñas Branch (Cavite)' : 'Biñan Branch (Laguna)';

// Query active customized fabrication projects requiring payment
$projects = $conn->query("
    SELECT co.id, co.project_name, co.customer_name, co.status, co.image, co.category, co.created_at,
           COALESCE((SELECT SUM(oi.total_amount) FROM order_items oi WHERE oi.order_id = co.id), 0) AS material_cost
    FROM custom_orders co
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

    <!-- FEEDBACK MESSAGES -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['err']) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        
        <!-- LEFT PANEL: CUSTOM ACTIVE PROJECTS LIST -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-800 text-light-emphasis"><i class="fas fa-diagram-project me-2 text-amber"></i>Active Custom Projects</span>
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
                    <div class="row g-3" id="projectListContainer">
                        <?php foreach ($projectList as $p): 
                            $mCost = (float)$p['material_cost'];
                            // Standard contract price: Raw materials + 50% labor markup
                            $totalVal = $mCost * 1.5;
                            if ($totalVal <= 0) {
                                // Default minimum fallback for fresh assignments awaiting inspection
                                $totalVal = 5000.00;
                            }
                        ?>
                        <div class="col-12 col-md-6 pos-project-item" 
                             data-project-json='<?= json_encode([
                                 'id' => $p['id'],
                                 'name' => $p['project_name'],
                                 'customer' => $p['customer_name'] ?? 'Guest Client',
                                 'status' => $p['status'],
                                 'material_cost' => $mCost,
                                 'total' => $totalVal
                             ]) ?>'>
                            
                            <div class="card h-100 border p-3 rounded-3 d-flex flex-column justify-content-between shadow-sm hover-shadow cursor-pointer transition" 
                                 style="cursor:pointer; background: rgba(255,255,255,0.02);"
                                 onclick='selectProject(this)'>
                                
                                <div class="d-flex gap-3 mb-2">
                                    <img src="<?= BASE_URL ?><?= $p['image'] ?? 'assets/images/no-image.png' ?>" 
                                         class="rounded border object-fit-cover" style="width:60px; height:60px;"
                                         onerror="this.src='<?= BASE_URL ?>assets/images/no-image.png'">
                                    <div class="text-truncate">
                                        <span class="badge bg-amber-subtle text-amber mb-1 fw-700" style="font-size:0.65rem;"><?= htmlspecialchars($p['status']) ?></span>
                                        <h6 class="m-0 fw-800 text-light-emphasis text-truncate"><?= htmlspecialchars($p['project_name']) ?></h6>
                                        <span class="text-muted small">Client: <?= htmlspecialchars($p['customer_name'] ?? 'Guest Client') ?></span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                    <span class="text-muted small">Contract Valuation:</span>
                                    <span class="fw-800 text-success">₱<?= number_format($totalVal, 2) ?></span>
                                </div>

                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: BILLING & CHECKOUT DETAILS -->
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
                        
                        <!-- Empty Desk Warning -->
                        <div class="text-center py-5 text-muted" id="emptyCheckoutPrompt">
                            <i class="fas fa-hand-pointer fs-2 mb-3 d-block opacity-25 text-amber"></i>
                            Select an active custom project on the left to begin payment processing.
                        </div>

                        <!-- Selected Project Checkout Specs -->
                        <div class="d-none" id="checkoutPanel">
                            <span class="text-muted small fw-700 d-block mb-1">BILLING PROJECT</span>
                            <h4 class="fw-800 text-light-emphasis m-0" id="billingProjectName">—</h4>
                            <span class="text-muted small" id="billingCustomerName">Client: —</span>

                            <div class="bg-light p-3 rounded-3 my-4" style="background: rgba(0,0,0,0.08) !important;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-700">RAW MATERIAL METRIC</span>
                                    <span class="fw-600 text-light-emphasis" id="billingMaterialCost">₱0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-700">FABRICATION LABOR (50%)</span>
                                    <span class="fw-600 text-light-emphasis" id="billingLaborCost">₱0.00</span>
                                </div>
                                <hr class="my-2 opacity-25">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-800 text-light-emphasis">Contract valuation</span>
                                    <span class="fw-800 text-success fs-5" id="billingTotalVal">₱0.00</span>
                                </div>
                            </div>

                            <!-- Payment Options -->
                            <div class="mb-4">
                                <label class="form-label small fw-800">PAYMENT OPTION SELECTOR</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-amber w-100 py-3 active" id="btnDownpayment" onclick="setPaymentMode('down')">
                                            <div class="fw-800">50% Downpayment</div>
                                            <div class="small fw-700" id="downpaymentLabelVal">₱0.00</div>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-amber w-100 py-3" id="btnFullpayment" onclick="setPaymentMode('full')">
                                            <div class="fw-800">Full / Final Settlement</div>
                                            <div class="small fw-700" id="fullpaymentLabelVal">₱0.00</div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Amount Inputs -->
                            <div class="mb-4">
                                <label class="form-label small fw-800">PAYMENT AMOUNT RECEIVED (₱)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-transparent fw-800">₱</span>
                                    <input type="number" step="0.01" name="amount_paid" id="paymentInput" class="form-control fw-800 text-center text-success" placeholder="0.00" required>
                                </div>
                            </div>

                            <!-- Return Change -->
                            <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-success-subtle text-success rounded-3 d-none" id="changeContainer">
                                <span class="fw-800">Cash Change back:</span>
                                <h3 class="fw-800 m-0" id="changeAmount">₱0.00</h3>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer bg-white p-4 border-0">
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-800 animate-btn shadow" id="btnSubmitCheckout" disabled>
                            <i class="fas fa-check-circle me-2"></i>Complete Checkout Transaction
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<script>
let selectedProjData = null;
let activePaymentMode = 'down'; // 'down' or 'full'
let paymentAmountDue = 0;

function selectProject(card) {
    // Highlight Card
    document.querySelectorAll('.pos-project-item .card').forEach(c => {
        c.style.borderColor = 'rgba(0,0,0,0.1)';
        c.style.background = 'rgba(255,255,255,0.02)';
    });
    const innerCard = card.querySelector('.card');
    innerCard.style.borderColor = 'var(--rh-amber)';
    innerCard.style.background = 'rgba(245,158,11,0.05)';

    // Load Data
    const data = JSON.parse(card.closest('.pos-project-item').dataset.projectJson);
    selectedProjData = data;

    // Toggle Panel
    document.getElementById('emptyCheckoutPrompt').classList.add('d-none');
    document.getElementById('checkoutPanel').classList.remove('d-none');
    document.getElementById('btnSubmitCheckout').disabled = false;

    // Set Text Values
    document.getElementById('checkoutProjectId').value = data.id;
    document.getElementById('checkoutContractTotal').value = data.total;
    
    document.getElementById('billingProjectName').textContent = data.name;
    document.getElementById('billingCustomerName').textContent = 'Client: ' + data.customer + ' (' + data.status + ')';

    const mat = parseFloat(data.material_cost);
    const labor = mat * 0.5;
    const total = data.total;

    document.getElementById('billingMaterialCost').textContent = '₱' + mat.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('billingLaborCost').textContent = '₱' + labor.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('billingTotalVal').textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});

    // Option Sub-labels
    const downVal = total / 2;
    document.getElementById('downpaymentLabelVal').textContent = '₱' + downVal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('fullpaymentLabelVal').textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2});

    // Reset payment values
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
    const changeAmountEl = document.getElementById('changeAmount');
    const changeContainer = document.getElementById('changeContainer');

    if (payAmt >= paymentAmountDue && paymentAmountDue > 0) {
        changeContainer.classList.remove('d-none');
        const change = payAmt - paymentAmountDue;
        changeAmountEl.textContent = '₱' + change.toLocaleString('en-US', {minimumFractionDigits: 2});
    } else {
        changeContainer.classList.add('d-none');
    }
}

document.getElementById('paymentInput').addEventListener('input', calculateChange);

// POS live filter matching customer name and project titles
document.getElementById('posSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.pos-project-item').forEach(item => {
        const data = JSON.parse(item.dataset.projectJson);
        const matchName = data.name.toLowerCase().includes(q);
        const matchCust = data.customer.toLowerCase().includes(q);
        item.style.display = (matchName || matchCust) ? '' : 'none';
    });
});

// Front end checkout submit check
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const payAmt = parseFloat(document.getElementById('paymentInput').value) || 0;
    if (payAmt < paymentAmountDue) {
        alert("Insufficient amount received. Total due is ₱" + paymentAmountDue.toLocaleString());
        e.preventDefault();
    }
});
</script>
</body></html>