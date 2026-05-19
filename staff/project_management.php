<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) { header("Location: ../index.php"); exit; }

$branch = $_SESSION['branch_id'] ?? 1;
$sf = $_GET['status'] ?? 'all';

$where = ($sf !== 'all') ? "AND co.status = '" . $conn->real_escape_string($sf) . "'" : "";

$orders = $conn->query("
    SELECT co.id, co.project_name, co.category, co.material, co.status,
           co.estimated_completion, co.created_at, co.image,
           u.name AS customer_name, b.name AS branch_name,
           (SELECT GROUP_CONCAT(us.name SEPARATOR ', ')
            FROM tasks t JOIN users us ON us.id=t.assigned_to WHERE t.order_id=co.id) AS welders
    FROM custom_orders co
    LEFT JOIN users u ON u.id=co.customer_id
    LEFT JOIN branches b ON b.id=co.branch_id
    WHERE (co.branch_id=$branch OR co.branch_id IS NULL) $where
    ORDER BY co.created_at DESC
");

$welders = $conn->query("SELECT id,name FROM users WHERE role='welder' AND branch_id=$branch AND status='active' ORDER BY name");
$welderList = $welders->fetch_all(MYSQLI_ASSOC);

$items = $conn->query("SELECT i.id,i.name,i.price,COALESCE(inv.current_stock,0) stock FROM items i LEFT JOIN inventory inv ON inv.item_id=i.id AND inv.branch_id=$branch WHERE i.price IS NOT NULL ORDER BY i.name");
$itemList = $items->fetch_all(MYSQLI_ASSOC);

$statuses = ['all'=>'All','Appointment'=>'Appointment','Initial Payment'=>'Initial Payment',
             'On-going'=>'On-going','For Delivery'=>'For Delivery','Backjobs'=>'Backjobs',
             'Completed'=>'Completed'];
?>

<div class="rh-main">
    <div class="rh-page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1>Fabrication Project Management</h1>
            <p>Track active designs, assign expert welders, and monitor real-time completion stages.</p>
        </div>
    </div>

    <!-- STATUS FILTERS -->
    <div class="rh-tabs mb-4">
        <?php foreach ($statuses as $k => $v): ?>
            <a href="?status=<?= urlencode($k) ?>" class="rh-tab <?= $sf===$k?'active':'' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>

    <!-- PROJECT GRID -->
    <div class="row g-4">
    <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while ($o = $orders->fetch_assoc()):
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$o['status'])); 
            $progressPercent = [
                'Appointment' => 10,
                'Initial Payment' => 30,
                'On-going' => 60,
                'For Delivery' => 85,
                'Backjobs' => 50,
                'Completed' => 100,
                'Cancelled' => 0
            ][$o['status']] ?? 0;
        ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="rh-proj-card border-0 shadow-sm">
                <div class="rh-proj-thumb position-relative" style="height: 180px; overflow: hidden; border-radius: 8px 8px 0 0;">
                    <img src="../<?= $o['image'] ?? 'assets/images/no-image.png' ?>"
                         onerror="this.src='../assets/images/no-image.png'" class="w-100 h-100 object-fit-cover" alt="Project Design">
                    <span class="badge <?= $cls ?> status-float position-absolute top-0 end-0 m-3 fs-7 shadow-sm"><?= $o['status'] ?></span>
                </div>
                <div class="rh-proj-body p-4 bg-white" style="border-radius: 0 0 8px 8px;">
                    <span class="text-muted d-block small fw-700 mb-1" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.5px;"><?= htmlspecialchars($o['category'] ?? 'Customized Metal') ?></span>
                    <h5 class="fw-800 text-light-emphasis mb-2"><?= htmlspecialchars($o['project_name'] ?? 'Project #'.$o['id']) ?></h5>
                    
                    <div class="d-flex flex-column gap-2 mb-3">
                        <!-- Customer -->
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="fas fa-user-circle text-amber" style="width:16px;"></i>
                            <span>Client: <strong><?= htmlspecialchars($o['customer_name'] ?? 'Guest Customer') ?></strong></span>
                        </div>
                        <!-- Welder Team -->
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="fas fa-hard-hat text-amber" style="width:16px;"></i>
                            <span>Welder: <strong><?= htmlspecialchars($o['welders'] ?? 'None Assigned') ?></strong></span>
                        </div>
                        <!-- Expected Date -->
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="far fa-calendar-alt text-amber" style="width:16px;"></i>
                            <span>Due Date: <strong><?= $o['estimated_completion'] ? date('M d, Y', strtotime($o['estimated_completion'])) : 'TBD' ?></strong></span>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 small fw-700 text-light-emphasis">
                            <span>Project Progress</span>
                            <span><?= $progressPercent ?>%</span>
                        </div>
                        <div class="progress" style="height:6px; border-radius:3px;">
                            <div class="progress-bar bg-amber" style="width: <?= $progressPercent ?>%; border-radius:3px;"></div>
                        </div>
                    </div>

                    <div class="rh-proj-footer pt-3 border-top d-flex gap-2">
                        <!-- View details page -->
                        <a href="../orders/view_order.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-dark fw-700 flex-grow-1">
                            <i class="fas fa-eye me-1"></i>View Full Specifications
                        </a>
                        
                        <!-- Assign Welder / Materials -->
                        <button class="btn btn-sm btn-amber fw-700 px-3" onclick='openAssign(<?= json_encode(["id"=>$o["id"],"name"=>$o["project_name"]??"Project #".$o["id"]]) ?>)'>
                            <i class="fas fa-edit"></i> Assign
                        </button>
                        
                        <!-- Update status dropdown -->
                        <button class="btn btn-sm btn-light border fw-700" onclick='openStatus(<?= $o["id"] ?>, "<?= $o["status"] ?>")'>
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted border-0 shadow-sm">
                <i class="fas fa-folder-open fs-1 mb-3 opacity-25 d-block text-amber"></i>
                No active projects found in this filter branch.
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- ASSIGN MODAL -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-user-plus me-2 text-amber"></i>Assign Project Setup</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/assign_project.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="order_id" id="assignId">
                    <div class="mb-3">
                        <label class="form-label small fw-700">Project Selected</label>
                        <input type="text" id="assignNameInput" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-700">Assign Welder</label>
                        <select name="welder_id" class="form-select" required>
                            <option value="">-- Choose Fabrication Welder --</option>
                            <?php foreach ($welderList as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-700">Estimated Completion Date</label>
                        <input type="date" name="estimated_completion" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-700">Allocate Initial Raw Materials</label>
                        <div id="matRows">
                            <div class="row g-2 mb-2 mat-row align-items-center">
                                <div class="col-7">
                                    <select name="item_id[]" class="form-select form-select-sm">
                                        <option value="">-- Select Material --</option>
                                        <?php foreach ($itemList as $i): ?>
                                        <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?> (<?= $i['stock'] ?> in stock)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="quantity[]" class="form-control form-control-sm" value="1" min="1">
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mat-row').remove()">✕</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-amber mt-2" onclick="addMatRow()">
                            <i class="fas fa-plus me-1"></i>Add Material Row
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-3 fw-700" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-800"><i class="fas fa-save me-1"></i>Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- STATUS MODAL -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-800"><i class="fas fa-exchange-alt me-2 text-amber"></i>Update Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/update_project_status.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="order_id" id="statusId">
                    <label class="form-label small fw-700">Project Status</label>
                    <select name="status" id="statusSel" class="form-select fw-700">
                        <?php foreach (['Appointment','Initial Payment','On-going','For Delivery','Backjobs','Completed'] as $st): ?>
                        <option value="<?= $st ?>"><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-primary w-100 fw-800 py-2"><i class="fas fa-save me-1"></i>Update Project Phase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const itemData = <?= json_encode($itemList) ?>;
const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));

function openAssign(d) {
    document.getElementById('assignId').value = d.id;
    document.getElementById('assignNameInput').value = d.name;
    assignModal.show();
}
function openStatus(id, cur) {
    document.getElementById('statusId').value = id;
    document.getElementById('statusSel').value = cur;
    statusModal.show();
}
function addMatRow() {
    const opts = itemData.map(i => `<option value="${i.id}">${i.name} (${i.stock} in stock)</option>`).join('');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 mat-row align-items-center';
    row.innerHTML = `
        <div class="col-7"><select name="item_id[]" class="form-select form-select-sm"><option value="">-- Select --</option>${opts}</select></div>
        <div class="col-3"><input type="number" name="quantity[]" class="form-control form-control-sm" value="1" min="1"></div>
        <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mat-row').remove()">✕</button></div>
    `;
    document.getElementById('matRows').appendChild(row);
}
</script>
</body></html>
