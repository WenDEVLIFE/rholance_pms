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
             'Completed'=>'Completed','Cancelled'=>'Cancelled'];
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>Project Management</h1>
        <p>Assign welders, materials, and track all custom orders.</p>
    </div>

    <!-- STATUS TABS -->
    <div class="rh-tabs mb-4">
        <?php foreach ($statuses as $k => $v): ?>
            <a href="?status=<?= urlencode($k) ?>" class="rh-tab <?= $sf===$k?'active':'' ?>"><?= $v ?></a>
        <?php endforeach; ?>
    </div>

    <!-- PROJECT GRID -->
    <div class="row g-3">
    <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while ($o = $orders->fetch_assoc()):
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$o['status'])); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="rh-proj-card">
                <div class="rh-proj-thumb">
                    <img src="../<?= $o['image'] ?? 'assets/images/no-image.png' ?>"
                         onerror="this.src='../assets/images/no-image.png'" alt="">
                    <span class="badge <?= $cls ?> status-float"><?= $o['status'] ?></span>
                </div>
                <div class="rh-proj-body">
                    <h6><?= htmlspecialchars($o['project_name'] ?? 'Project #'.$o['id']) ?></h6>
                    <p class="proj-meta">
                        <i class="fas fa-user fa-xs text-amber"></i> <?= htmlspecialchars($o['customer_name'] ?? '—') ?>
                        &nbsp;·&nbsp;
                        <i class="fas fa-map-marker-alt fa-xs text-amber"></i> <?= htmlspecialchars($o['branch_name'] ?? '—') ?>
                    </p>

                    <?php if ($o['welders']): ?>
                        <div class="badge badge-role-welder mb-2"><i class="fas fa-hard-hat me-1"></i><?= htmlspecialchars($o['welders']) ?></div>
                    <?php else: ?>
                        <div class="text-muted small mb-2">No welder assigned</div>
                    <?php endif; ?>

                    <?php if ($o['estimated_completion']): ?>
                        <div class="small text-muted mb-2"><i class="far fa-calendar me-1"></i>Due: <?= date('M d, Y',strtotime($o['estimated_completion'])) ?></div>
                    <?php endif; ?>

                    <div class="rh-proj-footer">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-dark" onclick='openAssign(<?= json_encode(["id"=>$o["id"],"name"=>$o["project_name"]??"Project #".$o["id"]]) ?>)'>
                                <i class="fas fa-user-plus me-1"></i>Assign
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick='openStatus(<?= $o["id"] ?>, "<?= $o["status"] ?>")'>
                                <i class="fas fa-exchange-alt me-1"></i>Status
                            </button>
                            <?php if (!in_array($o['status'],['Completed','Cancelled'])): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelProject(<?= $o['id'] ?>)">
                                <i class="fas fa-ban"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card p-5 text-center text-muted">
                <i class="fas fa-folder-open fs-1 mb-3 opacity-25 d-block"></i>
                No projects found.
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- ASSIGN MODAL -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-amber"></i>Assign Project: <span id="assignName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/assign_project.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="assignId">

                    <div class="mb-3">
                        <label class="form-label">Assign Welder</label>
                        <select name="welder_id" class="form-select">
                            <option value="">-- Select Welder --</option>
                            <?php foreach ($welderList as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estimated Completion</label>
                        <input type="date" name="estimated_completion" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Materials to Use</label>
                        <div id="matRows">
                            <div class="row g-2 mb-2 mat-row">
                                <div class="col-7">
                                    <select name="item_id[]" class="form-select form-select-sm">
                                        <option value="">-- Select --</option>
                                        <?php foreach ($itemList as $i): ?>
                                        <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?> (<?= $i['stock'] ?> in stock)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="quantity[]" class="form-control form-control-sm" value="1" min="1" placeholder="Qty">
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mat-row').remove()">✕</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-warning mt-1" onclick="addMatRow()">
                            <i class="fas fa-plus me-1"></i>Add Material
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- STATUS MODAL -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2 text-amber"></i>Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/update_project_status.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="statusId">
                    <label class="form-label">New Status</label>
                    <select name="status" id="statusSel" class="form-select">
                        <?php foreach (['Appointment','Initial Payment','On-going','For Delivery','Backjobs','Completed','Cancelled'] as $st): ?>
                        <option value="<?= $st ?>"><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update</button>
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
    document.getElementById('assignName').textContent = d.name;
    assignModal.show();
}
function openStatus(id, cur) {
    document.getElementById('statusId').value = id;
    document.getElementById('statusSel').value = cur;
    statusModal.show();
}
function cancelProject(id) {
    if (!confirm('Cancel this project?')) return;
    fetch('api/update_project_status.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'order_id='+id+'&status=Cancelled'})
        .then(() => location.reload());
}
function addMatRow() {
    const opts = itemData.map(i => `<option value="${i.id}">${i.name} (${i.stock} in stock)</option>`).join('');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 mat-row';
    row.innerHTML = `
        <div class="col-7"><select name="item_id[]" class="form-select form-select-sm"><option value="">-- Select --</option>${opts}</select></div>
        <div class="col-3"><input type="number" name="quantity[]" class="form-control form-control-sm" value="1" min="1"></div>
        <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mat-row').remove()">✕</button></div>
    `;
    document.getElementById('matRows').appendChild(row);
}
</script>
</body></html>
