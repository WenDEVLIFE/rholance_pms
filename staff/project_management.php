<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if (!in_array($_SESSION['role'], ['staff','admin'])) {
    header("Location: ../index.php"); exit;
}

$branch = $_SESSION['branch_id'] ?? 1;
$statusFilter = $_GET['status'] ?? 'all';

$where = ($statusFilter !== 'all') ? "AND co.status = '" . $conn->real_escape_string($statusFilter) . "'" : "";

$orders = $conn->query("
    SELECT 
        co.id, co.project_name, co.category, co.material, co.status,
        co.estimated_completion, co.created_at, co.image,
        u.name AS customer_name, u.email AS customer_email,
        b.name AS branch_name,
        (SELECT GROUP_CONCAT(us.name SEPARATOR ', ') FROM tasks t JOIN users us ON us.id = t.assigned_to WHERE t.order_id = co.id) AS assigned_welders
    FROM custom_orders co
    LEFT JOIN users u ON u.id = co.customer_id
    LEFT JOIN branches b ON b.id = co.branch_id
    WHERE (co.branch_id = $branch OR co.branch_id IS NULL)
    $where
    ORDER BY co.created_at DESC
");

/* Welders for assignment dropdown */
$welders = $conn->query("SELECT id, name FROM users WHERE role = 'welder' AND branch_id = $branch AND status = 'active' ORDER BY name ASC");
$welderList = $welders->fetch_all(MYSQLI_ASSOC);

/* Items for material assignment */
$itemsRes = $conn->query("SELECT i.id, i.name, i.price, COALESCE(inv.current_stock,0) AS stock FROM items i LEFT JOIN inventory inv ON inv.item_id = i.id AND inv.branch_id = $branch WHERE i.price IS NOT NULL ORDER BY i.name ASC");
$itemList = $itemsRes->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Management – Staff</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.pm-page { padding:28px 32px; }
.pm-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.pm-header h2 { margin:0; font-size:22px; color:#0F172A; }

/* STATUS TABS */
.status-tabs { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
.status-tab { padding:7px 18px; border-radius:20px; border:2px solid #E2E8F0; background:#fff; color:#64748B; font-weight:600; font-size:13px; text-decoration:none; transition:all .2s; }
.status-tab:hover { border-color:#F59E0B; color:#F59E0B; }
.status-tab.active { background:#0F172A; border-color:#0F172A; color:#fff; }

/* PROJECT CARDS GRID */
.proj-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(340px,1fr)); gap:20px; }
.proj-card { background:#fff; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.05); transition:box-shadow .25s; }
.proj-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.1); }
.proj-thumb { height:140px; background:#F1F5F9; overflow:hidden; position:relative; }
.proj-thumb img { width:100%; height:100%; object-fit:cover; }
.proj-status { position:absolute; top:10px; right:10px; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase; color:#fff; }
.s-appointment     { background:#64748B; }
.s-initial-payment { background:#3B82F6; }
.s-on-going        { background:#F59E0B; }
.s-for-delivery    { background:#10B981; }
.s-backjobs        { background:#EF4444; }
.s-completed       { background:#059669; }
.s-cancelled       { background:#94A3B8; }

.proj-body { padding:18px; }
.proj-body h3 { margin:0 0 4px; font-size:16px; color:#0F172A; }
.proj-body .meta { font-size:13px; color:#64748B; margin-bottom:12px; }
.proj-info-row { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
.proj-tag { font-size:12px; background:#F1F5F9; border-radius:8px; padding:4px 10px; color:#475569; }
.welder-badge { font-size:12px; background:#FEF3C7; color:#92400E; border-radius:8px; padding:4px 10px; font-weight:700; }
.proj-actions { display:flex; gap:8px; flex-wrap:wrap; }
.btn-sm { padding:7px 14px; border-radius:10px; font-size:13px; font-weight:700; border:none; cursor:pointer; transition:all .2s; }
.btn-assign  { background:#0F172A; color:#fff; }  .btn-assign:hover  { background:#1E293B; }
.btn-cancel  { background:#FEE2E2; color:#991B1B; } .btn-cancel:hover  { background:#FECACA; }
.btn-status  { background:#DBEAFE; color:#1E40AF; } .btn-status:hover  { background:#BFDBFE; }

/* MODAL */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:20px; padding:32px; max-width:560px; width:100%; max-height:90vh; overflow-y:auto; position:relative; box-shadow:0 20px 60px rgba(0,0,0,.2); }
.modal-box h3 { margin:0 0 20px; font-size:18px; color:#0F172A; }
.modal-close { position:absolute; top:16px; right:16px; background:#F1F5F9; border:none; border-radius:50%; width:32px; height:32px; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center; }
.modal-form label { font-size:13px; font-weight:700; color:#374151; margin-bottom:6px; display:block; }
.modal-form select, .modal-form input, .modal-form textarea { width:100%; padding:10px 14px; border-radius:10px; border:1px solid #E2E8F0; font-family:inherit; font-size:14px; box-sizing:border-box; margin-bottom:16px; }
.modal-form select:focus, .modal-form input:focus { border-color:#F59E0B; outline:none; }
.material-row { display:grid; grid-template-columns:1fr auto auto; gap:10px; align-items:center; margin-bottom:10px; }
.material-row input[type=number] { width:70px; }
.btn-add-mat { background:#F59E0B; color:#fff; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-weight:700; }
.btn-remove  { background:#FEE2E2; color:#991B1B; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; }
</style>
</head>
<body>

<div class="main pm-page">

    <div class="pm-header">
        <h2><i class="fa-solid fa-diagram-project" style="color:#F59E0B;margin-right:8px;"></i>Project Management</h2>
    </div>

    <!-- STATUS TABS -->
    <div class="status-tabs">
        <?php $statuses = ['all'=>'All','Appointment'=>'Appointment','Initial Payment'=>'Initial Payment','On-going'=>'On-going','For Delivery'=>'For Delivery','Backjobs'=>'Backjobs','Completed'=>'Completed','Cancelled'=>'Cancelled']; ?>
        <?php foreach ($statuses as $key => $label): ?>
            <a href="?status=<?= urlencode($key) ?>" class="status-tab <?= $statusFilter === $key ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <!-- PROJECT CARDS -->
    <div class="proj-grid">
        <?php if ($orders && $orders->num_rows > 0): ?>
            <?php while ($o = $orders->fetch_assoc()): ?>
                <?php $cls = 's-' . strtolower(str_replace([' ','/'], ['-','-'], $o['status'])); ?>
                <div class="proj-card">
                    <div class="proj-thumb">
                        <img src="../<?= $o['image'] ?? 'assets/images/no-image.png' ?>" alt="Project" onerror="this.src='../assets/images/no-image.png'">
                        <span class="proj-status <?= $cls ?>"><?= $o['status'] ?></span>
                    </div>
                    <div class="proj-body">
                        <h3><?= htmlspecialchars($o['project_name'] ?? 'Custom Project #' . $o['id']) ?></h3>
                        <p class="meta">
                            <i class="fas fa-user" style="color:#F59E0B;"></i> <?= htmlspecialchars($o['customer_name'] ?? 'Unknown') ?>
                            &nbsp;·&nbsp;
                            <i class="fas fa-map-marker-alt" style="color:#F59E0B;"></i> <?= htmlspecialchars($o['branch_name'] ?? 'N/A') ?>
                        </p>
                        <div class="proj-info-row">
                            <?php if ($o['category']): ?><span class="proj-tag"><?= htmlspecialchars($o['category']) ?></span><?php endif; ?>
                            <?php if ($o['material']): ?><span class="proj-tag"><?= htmlspecialchars($o['material']) ?></span><?php endif; ?>
                            <?php if ($o['estimated_completion']): ?><span class="proj-tag"><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($o['estimated_completion'])) ?></span><?php endif; ?>
                        </div>
                        <?php if ($o['assigned_welders']): ?>
                            <div class="welder-badge" style="margin-bottom:12px;"><i class="fas fa-hard-hat"></i> <?= htmlspecialchars($o['assigned_welders']) ?></div>
                        <?php else: ?>
                            <div style="font-size:12px;color:#94A3B8;margin-bottom:12px;">No welder assigned yet</div>
                        <?php endif; ?>

                        <div class="proj-actions">
                            <button class="btn-sm btn-assign" onclick="openAssign(<?= $o['id'] ?>, '<?= htmlspecialchars(addslashes($o['project_name'] ?? '')) ?>')">
                                <i class="fas fa-user-plus"></i> Assign
                            </button>
                            <button class="btn-sm btn-status" onclick="openStatus(<?= $o['id'] ?>, '<?= $o['status'] ?>')">
                                <i class="fas fa-exchange-alt"></i> Status
                            </button>
                            <?php if (!in_array($o['status'], ['Completed','Cancelled'])): ?>
                            <button class="btn-sm btn-cancel" onclick="cancelProject(<?= $o['id'] ?>)">
                                <i class="fas fa-ban"></i> Cancel
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px;color:#94A3B8;">
                <i class="fas fa-folder-open" style="font-size:48px;margin-bottom:16px;display:block;opacity:.3;"></i>
                No projects found.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── ASSIGN MODAL ── -->
<div class="modal-overlay" id="assignModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('assignModal')">×</button>
        <h3><i class="fas fa-user-plus" style="color:#F59E0B;margin-right:8px;"></i>Assign Project: <span id="assignProjectName"></span></h3>
        <form class="modal-form" action="api/assign_project.php" method="POST">
            <input type="hidden" name="order_id" id="assignOrderId">

            <label>Assign Welder</label>
            <select name="welder_id">
                <option value="">-- Select Welder --</option>
                <?php foreach ($welderList as $w): ?>
                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Estimated Completion Date</label>
            <input type="date" name="estimated_completion">

            <label>Materials to Use</label>
            <div id="materialRows">
                <div class="material-row">
                    <select name="item_id[]">
                        <option value="">-- Select Material --</option>
                        <?php foreach ($itemList as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?> (Stock: <?= $item['stock'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity[]" placeholder="Qty" min="1" value="1">
                    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
                </div>
            </div>
            <button type="button" class="btn-add-mat" onclick="addMaterialRow()">+ Add Material</button>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-sm btn-assign" style="padding:12px 24px;font-size:15px;width:100%;">
                    <i class="fas fa-save"></i> Save Assignment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── STATUS MODAL ── -->
<div class="modal-overlay" id="statusModal">
    <div class="modal-box" style="max-width:400px;">
        <button class="modal-close" onclick="closeModal('statusModal')">×</button>
        <h3><i class="fas fa-exchange-alt" style="color:#F59E0B;margin-right:8px;"></i>Update Status</h3>
        <form class="modal-form" action="api/update_project_status.php" method="POST">
            <input type="hidden" name="order_id" id="statusOrderId">
            <label>New Status</label>
            <select name="status">
                <option value="Appointment">Appointment</option>
                <option value="Initial Payment">Initial Payment</option>
                <option value="On-going">On-going</option>
                <option value="For Delivery">For Delivery</option>
                <option value="Backjobs">Backjobs</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <button type="submit" class="btn-sm btn-status" style="padding:12px;font-size:15px;width:100%;">
                <i class="fas fa-save"></i> Update Status
            </button>
        </form>
    </div>
</div>

<script>
const itemsData = <?= json_encode($itemList) ?>;

function openAssign(id, name) {
    document.getElementById('assignOrderId').value = id;
    document.getElementById('assignProjectName').textContent = name || '#' + id;
    document.getElementById('assignModal').classList.add('open');
}

function openStatus(id, currentStatus) {
    document.getElementById('statusOrderId').value = id;
    const sel = document.querySelector('#statusModal select[name=status]');
    if (sel) sel.value = currentStatus;
    document.getElementById('statusModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function cancelProject(id) {
    if (!confirm('Are you sure you want to cancel this project?')) return;
    fetch('api/update_project_status.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'order_id=' + id + '&status=Cancelled'
    }).then(() => location.reload());
}

function addMaterialRow() {
    const opts = itemsData.map(i => `<option value="${i.id}">${i.name} (Stock: ${i.stock})</option>`).join('');
    const row = document.createElement('div');
    row.className = 'material-row';
    row.innerHTML = `
        <select name="item_id[]"><option value="">-- Select Material --</option>${opts}</select>
        <input type="number" name="quantity[]" placeholder="Qty" min="1" value="1">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
    `;
    document.getElementById('materialRows').appendChild(row);
}

/* Close modal on overlay click */
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>
</body>
</html>
