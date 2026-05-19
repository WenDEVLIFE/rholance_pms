<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'welder') { header("Location: ../index.php"); exit; }

$wid = $_SESSION['user_id'];
$branch = $_SESSION['branch_id'] ?? 1;
$branchName = $branch == 1 ? 'Cavite (Dasmariñas)' : 'Laguna (Biñan)';

// 1. Fetch NEW ASSIGNMENTS requiring initial inspection & specs input
$inspectionsQuery = $conn->prepare("
    SELECT DISTINCT co.id, co.project_name, co.status, co.created_at, co.image,
           u.name cust_name, u.phone cust_phone, u.address cust_address, co.instructions
    FROM tasks t
    JOIN custom_orders co ON co.id = t.order_id
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE t.assigned_to = ? 
      AND (co.material = 'TBD' OR co.dimensions = 'TBD' OR co.material IS NULL)
      AND co.status NOT IN ('Completed', 'Cancelled')
    ORDER BY co.created_at DESC
");
$inspectionsQuery->bind_param("i", $wid);
$inspectionsQuery->execute();
$inspections = $inspectionsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// 2. Fetch ACTIVE FABRICATION PROJECTS already set up
$activeProjectsQuery = $conn->prepare("
    SELECT DISTINCT co.id, co.project_name, co.status, co.created_at, co.image,
           u.name cust_name, u.phone cust_phone, u.address cust_address,
           co.material, co.dimensions, co.instructions, co.estimated_completion,
           co.progress_percent, co.progress_details,
           (SELECT SUM(oi.total_amount) FROM order_items oi WHERE oi.order_id = co.id) AS material_cost
    FROM tasks t
    JOIN custom_orders co ON co.id = t.order_id
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE t.assigned_to = ? 
      AND co.material != 'TBD' AND co.dimensions != 'TBD' AND co.material IS NOT NULL
      AND co.status NOT IN ('Completed', 'Cancelled')
    ORDER BY co.created_at DESC
");
$activeProjectsQuery->bind_param("i", $wid);
$activeProjectsQuery->execute();
$activeProjects = $activeProjectsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Fetch COMPLETED PROJECTS
$completedQuery = $conn->prepare("
    SELECT DISTINCT co.id, co.project_name, co.status, co.created_at, co.image, u.name cust_name
    FROM tasks t
    JOIN custom_orders co ON co.id = t.order_id
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE t.assigned_to = ? AND co.status = 'Completed'
    ORDER BY co.updated_at DESC
");
$completedQuery->bind_param("i", $wid);
$completedQuery->execute();
$completedList = $completedQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Fetch Inventory Materials for price breakdown/material allocation
$invQuery = $conn->query("
    SELECT i.id, i.name, i.price, COALESCE(inv.current_stock, 0) stock 
    FROM items i 
    LEFT JOIN inventory inv ON inv.item_id = i.id AND inv.branch_id = $branch 
    WHERE i.price IS NOT NULL 
    ORDER BY i.name ASC
");
$inventoryList = $invQuery ? $invQuery->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="rh-main">
    
    <!-- PAGE HEADER -->
    <div class="rh-page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1>Welder Workspace</h1>
            <p>Welcome back! You are tracking fabrication projects in <strong><?= $branchName ?></strong>.</p>
        </div>
    </div>

    <!-- NOTIFICATION BAR -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- TABS TO SEPARATE WORKFLOW STAGES -->
    <ul class="nav nav-pills gap-2 mb-4" id="welderTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-800 px-4 py-2 position-relative" id="inspections-tab" data-bs-toggle="pill" data-bs-target="#inspections" type="button" role="tab">
                <i class="fas fa-search-location me-2"></i>My Inspections / Setup
                <?php if (count($inspections) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size:0.65rem;">
                        <?= count($inspections) ?>
                    </span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-800 px-4 py-2 position-relative" id="active-tab" data-bs-toggle="pill" data-bs-target="#active" type="button" role="tab">
                <i class="fas fa-tools me-2"></i>Active Projects
                <?php if (count($activeProjects) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark border border-light" style="font-size:0.65rem;">
                        <?= count($activeProjects) ?>
                    </span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-800 px-4 py-2" id="completed-tab" data-bs-toggle="pill" data-bs-target="#completed" type="button" role="tab">
                <i class="fas fa-check-double me-2"></i>Completed Projects
            </button>
        </li>
    </ul>

    <!-- TAB PANELS -->
    <div class="tab-content" id="welderTabContent">
        
        <!-- STAGE 1: INSPECTIONS & INITIAL PROJECT SETUP -->
        <div class="tab-pane fade show active" id="inspections" role="tabpanel">
            <div class="row g-4">
                <?php if (empty($inspections)): ?>
                    <div class="col-12">
                        <div class="card p-5 text-center text-muted border-0 shadow-sm">
                            <i class="fas fa-calendar-check fs-1 d-block mb-3 opacity-25 text-amber"></i>
                            No pending site inspections or new custom designs awaiting setup.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($inspections as $ins): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="position-relative" style="height:150px; overflow:hidden; border-radius:8px 8px 0 0;">
                                <img src="../<?= $ins['image'] ?? 'assets/images/no-image.png' ?>" 
                                     class="w-100 h-100 object-fit-cover" onerror="this.src='../assets/images/no-image.png'" alt="Blueprint Preview">
                                <span class="badge bg-danger status-float position-absolute top-0 end-0 m-3 fs-7 shadow-sm">Waiting Setup</span>
                            </div>
                            <div class="card-body p-4 bg-white" style="border-radius:0 0 8px 8px;">
                                <h5 class="fw-800 text-light-emphasis mb-2"><?= htmlspecialchars($ins['project_name']) ?></h5>
                                
                                <div class="d-flex flex-column gap-2 mb-3 bg-light p-3 rounded-3" style="background: rgba(0,0,0,0.02) !important;">
                                    <div class="small text-muted"><i class="fas fa-user me-1 text-amber"></i>Client: <strong><?= htmlspecialchars($ins['cust_name']) ?></strong></div>
                                    <div class="small text-muted"><i class="fas fa-phone me-1 text-amber"></i>Phone: <strong><?= htmlspecialchars($ins['cust_phone'] ?? '—') ?></strong></div>
                                    <div class="small text-muted text-truncate" title="<?= htmlspecialchars($ins['cust_address'] ?? '') ?>">
                                        <i class="fas fa-map-marker-alt me-1 text-amber"></i>Loc: <strong><?= htmlspecialchars($ins['cust_address'] ?? 'Cavite/Laguna') ?></strong>
                                    </div>
                                </div>

                                <button class="btn btn-primary w-100 fw-800" onclick='openSetupModal(<?= json_encode($ins) ?>)'>
                                    <i class="fas fa-pen-ruler me-1"></i>Input Dimensions &amp; Specs
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- STAGE 2: ACTIVE PROJECTS (WITH DETAILED PROGRESS & MATERIAL ALLOCATION) -->
        <div class="tab-pane fade" id="active" role="tabpanel">
            <div class="row g-4">
                <?php if (empty($activeProjects)): ?>
                    <div class="col-12">
                        <div class="card p-5 text-center text-muted border-0 shadow-sm">
                            <i class="fas fa-tools fs-1 d-block mb-3 opacity-25 text-amber"></i>
                            No active fabrication projects in your queue. Input specifications on the first tab to begin.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeProjects as $p): 
                        $pct = (int)($p['progress_percent'] ?? 10);
                        $mCost = (float)$p['material_cost'];
                        $totalVal = $mCost * 1.5;
                        if ($totalVal <= 0) $totalVal = 5000.00;
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="position-relative" style="height:150px; overflow:hidden; border-radius:8px 8px 0 0;">
                                <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>" 
                                     class="w-100 h-100 object-fit-cover" onerror="this.src='../assets/images/no-image.png'" alt="Blueprint Preview">
                                <span class="badge bg-warning text-dark status-float position-absolute top-0 end-0 m-3 fs-7 shadow-sm"><?= htmlspecialchars($p['status']) ?></span>
                            </div>
                            <div class="card-body p-4 bg-white" style="border-radius:0 0 8px 8px;">
                                <h5 class="fw-800 text-light-emphasis mb-2"><?= htmlspecialchars($p['project_name']) ?></h5>
                                <div class="small text-muted mb-3">Client: <strong><?= htmlspecialchars($p['cust_name']) ?></strong></div>

                                <!-- Progress Gauge -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1 small fw-700 text-light-emphasis">
                                        <span>Build Progress</span>
                                        <span><?= $pct ?>%</span>
                                    </div>
                                    <div class="progress" style="height:7px; border-radius:3px;">
                                        <div class="progress-bar bg-amber" style="width: <?= $pct ?>%; border-radius:3px;"></div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-2 mb-3 bg-light p-3 rounded-3" style="background: rgba(0,0,0,0.02) !important; font-size:0.8rem;">
                                    <div><i class="fas fa-layer-group text-amber me-1"></i>Core: <?= htmlspecialchars($p['material']) ?></div>
                                    <div><i class="fas fa-maximize text-amber me-1"></i>Dims: <?= htmlspecialchars($p['dimensions']) ?></div>
                                    <div><i class="fas fa-calendar-alt text-amber me-1"></i>Due: <?= $p['estimated_completion'] ? date('M d, Y', strtotime($p['estimated_completion'])) : 'TBD' ?></div>
                                </div>

                                <button class="btn btn-warning w-100 fw-800 text-dark" onclick='openUpdateModal(<?= json_encode($p) ?>)'>
                                    <i class="fas fa-cog me-1"></i>Update Progress &amp; Materials
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- STAGE 3: COMPLETED PROJECTS -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <div class="row g-4">
                <?php if (empty($completedList)): ?>
                    <div class="col-12">
                        <div class="card p-5 text-center text-muted border-0 shadow-sm">
                            <i class="fas fa-check-double fs-1 d-block mb-3 opacity-25 text-amber"></i>
                            You haven't completed any customized fabrication projects yet.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($completedList as $comp): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="position-relative" style="height:150px; overflow:hidden; border-radius:8px 8px 0 0;">
                                <img src="../<?= $comp['image'] ?? 'assets/images/no-image.png' ?>" 
                                     class="w-100 h-100 object-fit-cover" onerror="this.src='../assets/images/no-image.png'" alt="Blueprint Preview">
                                <span class="badge bg-success status-float position-absolute top-0 end-0 m-3 fs-7 shadow-sm">Completed</span>
                            </div>
                            <div class="card-body p-4 bg-white" style="border-radius:0 0 8px 8px;">
                                <h5 class="fw-800 text-light-emphasis mb-1"><?= htmlspecialchars($comp['project_name']) ?></h5>
                                <div class="small text-muted mb-3">Client: <strong><?= htmlspecialchars($comp['cust_name']) ?></strong></div>
                                <a href="../orders/view_order.php?id=<?= $comp['id'] ?>" class="btn btn-outline-dark btn-sm w-100 fw-700">
                                    <i class="fas fa-eye me-1"></i>View Full Invoice Specs
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- SETUP INITIAL SPECIFICATIONS MODAL -->
<div class="modal fade" id="setupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-pen-ruler me-2 text-amber"></i>Input Design Specifications</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/welder_setup_details.php" method="POST">
                <input type="hidden" name="order_id" id="setupProjectId">
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <span class="text-muted small">Input design specifications for project</span>
                        <h5 class="fw-800 text-light-emphasis m-0" id="setupProjectName">—</h5>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-700">Core Raw Material</label>
                        <input type="text" name="material" class="form-control" placeholder="e.g. Stainless Steel 304 / Tubular Metal Frame" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-700">Actual Dimensions</label>
                        <input type="text" name="dimensions" class="form-control" placeholder="e.g. 10ft x 6ft x 4in" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-700">Welder Design Instructions</label>
                        <textarea name="instructions" class="form-control" rows="3" placeholder="Special requirements, base thickness, hinges placement details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-3 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800"><i class="fas fa-save me-1"></i>Register Project Specs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- UPDATE DETAILED PROGRESS & PRICING BREAKDOWN MODAL -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-cog me-2 text-amber"></i>Update Progress &amp; Breakdown</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/welder_update.php" method="POST">
                <input type="hidden" name="order_id" id="updateProjectId">
                <div class="modal-body p-4" style="max-height:550px; overflow-y:auto;">
                    
                    <div class="row g-3">
                        <div class="col-12 border-bottom pb-2">
                            <span class="text-muted small d-block">PROJECT UNDER REVIEW</span>
                            <h5 class="fw-800 text-light-emphasis m-0" id="updateProjectNameHeader">—</h5>
                        </div>

                        <!-- Progress Select (10%, 20%, 30%, ..., 100%) -->
                        <div class="col-6">
                            <label class="form-label small fw-700">Progress Percentage</label>
                            <select name="progress_pct" id="updateProgressPct" class="form-select fw-700 text-amber" required>
                                <?php for ($i = 10; $i <= 100; $i += 10): ?>
                                    <option value="<?= $i ?>"><?= $i ?>%</option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Status Selection -->
                        <div class="col-6">
                            <label class="form-label small fw-700">Logical Status Phase</label>
                            <select name="status" id="updateStatus" class="form-select fw-700" required>
                                <option value="Initial Payment">Initial Payment</option>
                                <option value="On-going">On-going</option>
                                <option value="For Delivery">For Delivery</option>
                                <option value="Backjobs">Backjobs</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <!-- Granular Progress details input -->
                        <div class="col-12">
                            <label class="form-label small fw-700">Granular Build Log Details <span class="text-muted font-normal">(What did you complete in this percentage?)</span></label>
                            <input type="text" name="progress_details" id="updateProgressDetails" class="form-control" placeholder="e.g. 20% - Metal frames layout completed. Joint rods prepped." required>
                        </div>

                        <!-- Estimated Completion Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Estimated Completion Date</label>
                            <input type="date" name="estimated_completion" id="updateEstDate" class="form-control" required>
                        </div>

                        <!-- Remarks Log -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-700">Tracking Notes / Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Anti-rust applied today. Ready for lacquer next.">
                        </div>

                        <!-- Pricing Breakdown / Raw Materials Allocation -->
                        <div class="col-12 border-top pt-3 mt-3">
                            <span class="text-muted d-block small fw-800 mb-2">RAW MATERIALS CHECKOUT (PRICING BREAKDOWN)</span>
                            <div class="alert bg-success-subtle text-success border-0 small mb-3">
                                <i class="fas fa-info-circle me-1"></i>Allocating raw materials here will dynamically build the customer invoice (raw materials + 50% labor) and automatically deduct inventory stock!
                            </div>
                            
                            <div id="materialRowsContainer">
                                <!-- JS Populated material rows -->
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-warning mt-2 fw-700" onclick="addNewMaterialRow()">
                                <i class="fas fa-plus me-1"></i>Allocate Raw Material
                            </button>
                        </div>

                    </div>

                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-3 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800"><i class="fas fa-save me-1"></i>Save Progress &amp; Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const inventoryData = <?= json_encode($inventoryList) ?>;
const setupModal = new bootstrap.Modal(document.getElementById('setupModal'));
const updateModal = new bootstrap.Modal(document.getElementById('updateModal'));

function openSetupModal(d) {
    document.getElementById('setupProjectId').value = d.id;
    document.getElementById('setupProjectName').textContent = d.project_name;
    setupModal.show();
}

function openUpdateModal(d) {
    document.getElementById('updateProjectId').value = d.id;
    document.getElementById('updateProjectNameHeader').textContent = d.project_name;
    document.getElementById('updateProgressPct').value = d.progress_percent || 10;
    document.getElementById('updateStatus').value = d.status;
    document.getElementById('updateProgressDetails').value = d.progress_details || '';
    document.getElementById('updateEstDate').value = d.estimated_completion || '';
    
    // Clear material allocation rows in modal
    document.getElementById('materialRowsContainer').innerHTML = '';
    
    updateModal.show();
}

function addNewMaterialRow() {
    const opts = inventoryData.map(i => `<option value="${i.id}">${i.name} (${i.stock} in stock) - ₱${i.price}</option>`).join('');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 mat-row align-items-center';
    row.innerHTML = `
        <div class="col-8">
            <select name="item_id[]" class="form-select form-select-sm" required>
                <option value="">-- Choose Raw Material --</option>
                ${opts}
            </select>
        </div>
        <div class="col-3">
            <input type="number" name="quantity[]" class="form-control form-control-sm text-center" value="1" min="1" required>
        </div>
        <div class="col-1">
            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mat-row').remove()">✕</button>
        </div>
    `;
    document.getElementById('materialRowsContainer').appendChild(row);
}
</script>
</body></html>
