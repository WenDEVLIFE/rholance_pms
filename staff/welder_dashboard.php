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

/* Fetch available inventory items for pricing estimations */
$items = $conn->query("SELECT i.id, i.name, i.price, COALESCE(inv.current_stock,0) stock FROM items i LEFT JOIN inventory inv ON inv.item_id=i.id AND inv.branch_id=$branch WHERE i.price IS NOT NULL ORDER BY i.name");
$itemList = $items ? $items->fetch_all(MYSQLI_ASSOC) : [];

/* 1. New assignments — assigned but no quote yet */
$newQ = $conn->prepare("
    SELECT co.id, co.project_name, co.status, co.created_at, co.image,
           co.welder_visit_date, co.welder_visit_time,
           co.customer_sketch, co.description,
           co.category, co.material, co.dimensions, co.instructions,
           co.welder_confirmed, co.customer_confirmed,
           u.name cust_name, u.email cust_email, u.phone cust_phone, u.address cust_address
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
           co.customer_sketch, co.welder_confirmed, co.customer_confirmed,
           co.contract_start_date, co.contract_terms, co.labor_cost,
           u.name cust_name, u.email cust_email, u.phone cust_phone, u.address cust_address
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
            <div class="rh-stat-card h-100" style="min-height:90px; align-items:flex-start;">
                <div class="rh-stat-icon bg-amber flex-shrink-0"><i class="fas fa-hard-hat"></i></div>
                <div>
                    <div class="rh-stat-label">Assigned Projects</div>
                    <div class="rh-stat-value"><?= $totalAssigned ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card h-100" style="min-height:90px; align-items:flex-start;">
                <div class="rh-stat-icon bg-blue flex-shrink-0"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="rh-stat-label">Quotes to Review</div>
                    <div class="rh-stat-value"><?= $pendingQuotes ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card h-100" style="min-height:90px; align-items:flex-start;">
                <div class="rh-stat-icon bg-purple flex-shrink-0"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="rh-stat-label">Pending Approval</div>
                    <div class="rh-stat-value"><?= $pendingProgress ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card h-100" style="min-height:90px; align-items:flex-start;">
                <div class="rh-stat-icon bg-green flex-shrink-0"><i class="fas fa-flag-checkered"></i></div>
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
        <?php foreach ($newAssignments as $p): 
            $timeAssigned = $p['welder_visit_time'] ? htmlspecialchars($p['welder_visit_time']) : 'TBD';
            $dateAssigned = $p['welder_visit_date'] ? date('F d, Y', strtotime($p['welder_visit_date'])) : 'TBD';
        ?>
        <div class="col-12 col-lg-6" id="assignment-card-<?= $p['id'] ?>">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                <!-- Header Box -->
                <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-800 fs-7" style="letter-spacing:0.5px;">
                        <i class="fas fa-calendar-check me-2 text-amber"></i>APPOINTMENT: <?= strtoupper($dateAssigned) ?>
                    </span>
                    <span class="badge bg-warning text-dark font-monospace fs-8">TIME ASSIGNED: <?= $timeAssigned ?></span>
                </div>
                
                <div class="card-body p-4 bg-white">
                    <div class="d-flex flex-column gap-2 mb-4">
                        <div class="d-flex border-bottom pb-2">
                            <span class="fw-800 text-muted small" style="width:140px; flex-shrink:0;">CLIENT NAME:</span>
                            <span class="fw-700 text-light-emphasis"><?= htmlspecialchars($p['cust_name'] ?? '—') ?></span>
                        </div>
                        <div class="d-flex border-bottom pb-2">
                            <span class="fw-800 text-muted small" style="width:140px; flex-shrink:0;">EMAIL:</span>
                            <span class="fw-600"><?= htmlspecialchars($p['cust_email'] ?? '—') ?></span>
                        </div>
                        <div class="d-flex border-bottom pb-2">
                            <span class="fw-800 text-muted small" style="width:140px; flex-shrink:0;">CONTACT NUMBER:</span>
                            <span class="fw-700 text-amber"><?= htmlspecialchars($p['cust_phone'] ?? '—') ?></span>
                        </div>
                        <div class="d-flex border-bottom pb-2">
                            <span class="fw-800 text-muted small" style="width:140px; flex-shrink:0;">BOOKED ABOUT:</span>
                            <span class="fw-800 text-success"><?= strtoupper(htmlspecialchars($p['category'] ?? 'Fabrication')) ?></span>
                        </div>
                        <div class="d-flex border-bottom pb-2">
                            <span class="fw-800 text-muted small" style="width:140px; flex-shrink:0;">ADDRESS:</span>
                            <span class="small fw-600"><?= htmlspecialchars($p['cust_address'] ?? '—') ?></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success fw-800 flex-fill py-2" onclick="dismissCard(<?= $p['id'] ?>)">EXIT</button>
                        <button type="button" class="btn btn-danger fw-800 flex-fill py-2" onclick='manageProjectHub(<?= json_encode($p) ?>)'>MANAGE</button>
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
                    <div class="border-top pt-3 d-flex gap-2">
                        <button type="button" class="btn btn-warning fw-800 flex-fill py-2" onclick='manageProjectHub(<?= json_encode($p) ?>)'>
                            <i class="fas fa-tasks me-1"></i>MANAGE PROJECT
                        </button>
                        <a href="../orders/view_order.php?id=<?= $p['id'] ?>" class="btn btn-outline-dark fw-700 px-3 d-flex align-items-center justify-content-center">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- 1. APPOINTMENT CONFIRMATION MODAL -->
<div class="modal fade" id="confirmAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="bg-dark text-white p-3 text-center">
                <div class="fw-800" style="letter-spacing:1px; font-size:.85rem;">APPOINTMENT CONFIRMATION</div>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-calendar-check text-warning mb-3" style="font-size: 3rem;"></i>
                <p class="small text-muted mb-3">Please confirm this appointment. Confirming notifies the customer that you will visit their site.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill fw-700" data-bs-dismiss="modal">CANCEL</button>
                    <a href="#" id="confirmApptBtn" class="btn btn-warning flex-fill fw-800">CONFIRM</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. PROJECT MANAGEMENT HUB MODAL (GATE PROJECT) -->
<div class="modal fade" id="projectHubModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="bg-dark text-white p-3 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-50 end-0 translate-middle-y me-3" data-bs-dismiss="modal"></button>
                <div class="fw-800 text-uppercase" style="letter-spacing:1px; font-size:.85rem;" id="hubHeaderTitle">
                    GATE PROJECT
                </div>
            </div>

            <!-- HUB MAIN PANEL -->
            <div id="hubMainPanel" class="hub-panel p-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border">
                        <div>
                            <div class="fw-800">PAYMENT</div>
                            <small class="text-muted" id="hubPaymentStatusText">Status: Unpaid</small>
                        </div>
                        <button class="btn btn-success fw-700 px-4" onclick="showHubSubPanel('hubPaymentPanel')">MANAGE</button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border">
                        <div>
                            <div class="fw-800">PROJECT CONTRACT (SLA / Timeline)</div>
                            <small class="text-muted" id="hubContractStatusText">Timeline: Not Set</small>
                        </div>
                        <button class="btn btn-success fw-700 px-4" onclick="showHubSubPanel('hubContractPanel')">MANAGE</button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border">
                        <div>
                            <div class="fw-800">PROJECT ESTIMATION (Pricing &amp; Inventory)</div>
                            <small class="text-muted" id="hubEstimationStatusText">Estimation: Not Set</small>
                        </div>
                        <button class="btn btn-success fw-700 px-4" onclick="showHubSubPanel('hubEstimationPanel')">MANAGE</button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border">
                        <div>
                            <div class="fw-800">PROJECT PROGRESS (Milestones &amp; Proof)</div>
                            <small class="text-muted" id="hubProgressStatusText">Progress: 0%</small>
                        </div>
                        <button class="btn btn-success fw-700 px-4" id="hubManageProgressBtn" onclick="showHubSubPanel('hubProgressPanel')">MANAGE</button>
                    </div>
                </div>
                <div class="modal-footer border-0 p-0 mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill fw-700" data-bs-dismiss="modal">BACK</button>
                    <button type="button" class="btn btn-primary flex-fill fw-800" data-bs-dismiss="modal">SAVE</button>
                </div>
            </div>

            <!-- SUB-PANEL 1: PAYMENT -->
            <div id="hubPaymentPanel" class="hub-panel p-4" style="display:none;">
                <h5 class="fw-800 mb-3 text-success"><i class="fas fa-credit-card me-2"></i>Payment Selection</h5>
                <form id="paymentOptionForm" onsubmit="savePaymentOption(event)">
                    <div class="mb-4">
                        <label class="form-label small fw-700">Payment Option Selection</label>
                        <select name="payment_type" id="paymentTypeSelect" class="form-select" required>
                            <option value="Initial Downpayment">Initial 50% Downpayment</option>
                            <option value="Full payment">Full payment</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill fw-700" onclick="showHubSubPanel('hubMainPanel')">BACK</button>
                        <button type="submit" class="btn btn-success flex-fill fw-800">SAVE</button>
                    </div>
                </form>
            </div>

            <!-- SUB-PANEL 2: CONTRACT SLA -->
            <div id="hubContractPanel" class="hub-panel p-4" style="display:none;">
                <h5 class="fw-800 mb-3 text-success"><i class="fas fa-file-contract me-2"></i>Project Contract SLA</h5>
                <form id="contractForm" onsubmit="saveContract(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-700">Project Start Date</label>
                            <input type="date" name="contract_start_date" id="contractStartDate" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Estimated Deadline</label>
                            <input type="date" name="quoted_deadline" id="contractDeadline" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">SLA Scope / Agreement details</label>
                        <textarea name="contract_terms" id="contractTerms" class="form-control" rows="4" placeholder="Enter specifications, scope, and timeline agreements..." required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill fw-700" onclick="showHubSubPanel('hubMainPanel')">BACK</button>
                        <button type="submit" class="btn btn-success flex-fill fw-800">SAVE</button>
                    </div>
                </form>
            </div>

            <!-- SUB-PANEL 3: ESTIMATION -->
            <div id="hubEstimationPanel" class="hub-panel p-4" style="display:none; max-height:80vh; overflow-y:auto;">
                <h5 class="fw-800 mb-1 text-success"><i class="fas fa-calculator me-2"></i>GATE PROJECT — PROJECT ESTIMATION</h5>
                <p class="text-muted small mb-3">Configure material breakdown costs and labor pricing.</p>
                
                <form id="estimationForm" onsubmit="saveEstimation(event)">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-700">Dimensions</label>
                            <input type="text" name="dimensions" id="estDimensions" class="form-control form-control-sm" placeholder="e.g. 5x7 feet" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Primary Material Type</label>
                            <input type="text" name="material" id="estMaterial" class="form-control form-control-sm" placeholder="e.g. Galvanized Iron / Stainless" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-800 text-success"><i class="fas fa-box me-1"></i>Materials Pricing Table</label>
                        <div id="estMatRowsContainer">
                            <!-- Rows added by JS -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addEstMatRow()">
                            <i class="fas fa-plus me-1"></i>Add Material Row
                        </button>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-700">Fabrication Labor (₱)</label>
                            <input type="number" step="0.01" name="labor_fabrication" id="estLaborFab" class="form-control form-control-sm labor-calc" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Installation Labor (₱)</label>
                            <input type="number" step="0.01" name="labor_installation" id="estLaborInst" class="form-control form-control-sm labor-calc" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Painting Labor (₱)</label>
                            <input type="number" step="0.01" name="labor_painting" id="estLaborPaint" class="form-control form-control-sm labor-calc" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Transpo &amp; Allowance (₱)</label>
                            <input type="number" step="0.01" name="labor_transpo" id="estLaborTrans" class="form-control form-control-sm labor-calc" value="0" required>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex justify-content-between small fw-700 mb-1">
                            <span>Materials Total:</span>
                            <span id="estMaterialsTotalSpan">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between small fw-700 mb-1">
                            <span>Labor Total:</span>
                            <span id="estLaborTotalSpan">₱0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-800 text-success">
                            <span>TOTAL COST ESTIMATION:</span>
                            <span id="estGrandTotalSpan">₱0.00</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill fw-700" onclick="showHubSubPanel('hubMainPanel')">BACK</button>
                        <button type="submit" class="btn btn-success flex-fill fw-800">SAVE</button>
                    </div>
                </form>
            </div>

            <!-- SUB-PANEL 4: PROGRESS -->
            <div id="hubProgressPanel" class="hub-panel p-4" style="display:none;">
                <h5 class="fw-800 mb-3 text-success"><i class="fas fa-chart-line me-2"></i>Submit Progress Milestone</h5>
                <form id="progressForm" enctype="multipart/form-data" onsubmit="saveProgress(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-700">Select Milestone Percentage</label>
                            <select name="percentage" id="progressPercentageSelect" class="form-select" required>
                                <?php for ($p_opt = 10; $p_opt <= 100; $p_opt += 10): ?>
                                    <option value="<?= $p_opt ?>"><?= $p_opt ?>% Complete</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-700">Photo Evidence Proof</label>
                            <input type="file" name="progress_image" id="progressImageInput" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-700">Justification / Details</label>
                        <textarea name="description" id="progressDescription" class="form-control" rows="3" placeholder="Explain the work accomplished at this stage..." required></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill fw-700" onclick="showHubSubPanel('hubMainPanel')">BACK</button>
                        <button type="submit" class="btn btn-success flex-fill fw-800">SAVE</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
const confirmApptModal = new bootstrap.Modal(document.getElementById('confirmAppointmentModal'));
const projectHubModal = new bootstrap.Modal(document.getElementById('projectHubModal'));
let currentProject = null;

const inventoryItems = <?= json_encode($itemList) ?>;

function dismissCard(id) {
    const card = document.getElementById('assignment-card-' + id);
    if (card) {
        card.style.transition = 'opacity 0.3s';
        card.style.opacity = '0';
        setTimeout(() => { card.remove(); }, 300);
    }
}

function manageProjectHub(project) {
    currentProject = project;
    
    // Check confirmation status
    if (parseInt(project.welder_confirmed) === 0) {
        document.getElementById('confirmApptBtn').href = 'confirm_welder_appt.php?id=' + project.id;
        confirmApptModal.show();
        return;
    }

    // Initialize Hub Panel Values
    document.getElementById('hubHeaderTitle').textContent = project.project_name || 'PROJECT HUB';
    
    // Set status descriptors
    document.getElementById('hubPaymentStatusText').textContent = 'Status: ' + (project.payment_status || 'Unpaid');
    document.getElementById('hubContractStatusText').textContent = project.contract_start_date ? ('Timeline: ' + project.contract_start_date + ' to ' + (project.quoted_deadline || 'TBD')) : 'Timeline: Not Set';
    document.getElementById('hubEstimationStatusText').textContent = project.quoted_price ? ('Quoted Price: ₱' + parseFloat(project.quoted_price).toLocaleString('en-US', {minimumFractionDigits:2})) : 'Estimation: Not Set';
    document.getElementById('hubProgressStatusText').textContent = 'Progress: ' + (project.progress_percent || 0) + '%';

    // Disable progress management if not paid yet
    const progBtn = document.getElementById('hubManageProgressBtn');
    if (project.payment_status === 'Paid') {
        progBtn.removeAttribute('disabled');
        progBtn.classList.remove('btn-secondary');
        progBtn.classList.add('btn-success');
    } else {
        progBtn.setAttribute('disabled', 'true');
        progBtn.classList.remove('btn-success');
        progBtn.classList.add('btn-secondary');
    }

    // Pre-populate contract sub-form
    document.getElementById('contractStartDate').value = project.contract_start_date || '';
    document.getElementById('contractDeadline').value = project.quoted_deadline || '';
    document.getElementById('contractTerms').value = project.contract_terms || '';

    // Pre-populate estimation sub-form
    document.getElementById('estDimensions').value = project.dimensions || '';
    document.getElementById('estMaterial').value = project.material || '';
    document.getElementById('estLaborFab').value = 0;
    document.getElementById('estLaborInst').value = 0;
    document.getElementById('estLaborPaint').value = 0;
    document.getElementById('estLaborTrans').value = 0;
    
    // Clear & populate items rows
    const container = document.getElementById('estMatRowsContainer');
    container.innerHTML = '';
    
    addEstMatRow(); // Start with one row

    showHubSubPanel('hubMainPanel');
    projectHubModal.show();
}

function showHubSubPanel(panelId) {
    document.querySelectorAll('.hub-panel').forEach(p => p.style.display = 'none');
    document.getElementById(panelId).style.display = 'block';
}

function addEstMatRow() {
    const opts = inventoryItems.map(i => `<option value="${i.id}" data-price="${i.price}">${i.name} (₱${parseFloat(i.price).toFixed(2)} - Stock: ${i.stock})</option>`).join('');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 est-mat-row align-items-center';
    row.innerHTML = `
        <div class="col-7">
            <select name="item_id[]" class="form-select form-select-sm est-item-select" onchange="calculateGrandTotal()" required>
                <option value="">-- Choose Material --</option>
                ${opts}
            </select>
        </div>
        <div class="col-3">
            <input type="number" name="quantity[]" class="form-control form-control-sm est-qty-input" value="1" min="1" onkeyup="calculateGrandTotal()" onchange="calculateGrandTotal()" required>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.est-mat-row').remove(); calculateGrandTotal();">✕</button>
        </div>
    `;
    document.getElementById('estMatRowsContainer').appendChild(row);
    calculateGrandTotal();
}

// Calculate running total values dynamically
document.querySelectorAll('.labor-calc').forEach(el => {
    el.addEventListener('input', calculateGrandTotal);
});

function calculateGrandTotal() {
    let materialsTotal = 0;
    document.querySelectorAll('.est-mat-row').forEach(row => {
        const sel = row.querySelector('.est-item-select');
        const qtyInput = row.querySelector('.est-qty-input');
        const opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            const price = parseFloat(opt.getAttribute('data-price')) || 0;
            const qty = parseInt(qtyInput.value) || 0;
            materialsTotal += price * qty;
        }
    });

    const fab = parseFloat(document.getElementById('estLaborFab').value) || 0;
    const inst = parseFloat(document.getElementById('estLaborInst').value) || 0;
    const paint = parseFloat(document.getElementById('estLaborPaint').value) || 0;
    const trans = parseFloat(document.getElementById('estLaborTrans').value) || 0;
    const laborTotal = fab + inst + paint + trans;

    const grandTotal = materialsTotal + laborTotal;

    document.getElementById('estMaterialsTotalSpan').textContent = '₱' + materialsTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('estLaborTotalSpan').textContent = '₱' + laborTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('estGrandTotalSpan').textContent = '₱' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
}

function savePaymentOption(e) {
    e.preventDefault();
    const type = document.getElementById('paymentTypeSelect').value;
    
    const formData = new FormData();
    formData.append('order_id', currentProject.id);
    formData.append('payment_type', type);
    
    fetch('../orders/update_payment_option.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(() => {
        alert('Payment option updated successfully.');
        location.reload();
    });
}

function saveContract(e) {
    e.preventDefault();
    const start = document.getElementById('contractStartDate').value;
    const deadline = document.getElementById('contractDeadline').value;
    const terms = document.getElementById('contractTerms').value;
    
    const formData = new FormData();
    formData.append('order_id', currentProject.id);
    formData.append('contract_start_date', start);
    formData.append('quoted_deadline', deadline);
    formData.append('contract_terms', terms);
    
    fetch('../orders/update_contract.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(() => {
        alert('Project Contract saved.');
        showHubSubPanel('hubMainPanel');
        location.reload();
    });
}

function saveEstimation(e) {
    e.preventDefault();
    const form = document.getElementById('estimationForm');
    const formData = new FormData(form);
    formData.append('order_id', currentProject.id);
    
    fetch('../orders/update_estimation.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
        } else {
            alert('Project Cost Estimation saved successfully and inventory materials allocated.');
            location.reload();
        }
    })
    .catch(err => {
        alert('Error processing request.');
    });
}

function saveProgress(e) {
    e.preventDefault();
    const form = document.getElementById('progressForm');
    const formData = new FormData(form);
    formData.append('order_id', currentProject.id);
    
    fetch('../orders/submit_progress_milestone.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
        } else {
            alert('Progress milestone submitted successfully.');
            location.reload();
        }
    })
    .catch(err => {
        alert('Error uploading file.');
    });
}
</script>
</body></html>
