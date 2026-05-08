<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'welder') {
    header("Location: ../index.php"); exit;
}

$welderId = $_SESSION['user_id'];
$branch   = $_SESSION['branch_id'] ?? 1;

/* ── Projects assigned to this welder ── */
$projects = $conn->prepare("
    SELECT 
        co.id, co.project_name, co.category, co.material, co.status,
        co.estimated_completion, co.description, co.image, co.created_at,
        u.name AS customer_name
    FROM tasks t
    JOIN custom_orders co ON co.id = t.order_id
    LEFT JOIN users u ON u.id = co.customer_id
    WHERE t.assigned_to = ?
    ORDER BY FIELD(co.status,'On-going','Initial Payment','For Delivery','Backjobs','Appointment','Completed'), co.created_at DESC
");
$projects->bind_param("i", $welderId);
$projects->execute();
$projects = $projects->get_result()->fetch_all(MYSQLI_ASSOC);

/* Status → progress % */
function progressPct($status) {
    return ['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$status] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welder Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
.weld-page { padding:28px 32px; }
.weld-header { margin-bottom:28px; }
.weld-header h2 { margin:0 0 4px; font-size:22px; color:#0F172A; }
.weld-header p  { margin:0; color:#64748B; font-size:14px; }

/* KANBAN-STYLE ROWS */
.weld-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:20px; }

.weld-card { background:#fff; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.weld-thumb { height:140px; background:#F1F5F9; position:relative; overflow:hidden; }
.weld-thumb img { width:100%; height:100%; object-fit:cover; }
.weld-badge { position:absolute; top:10px; right:10px; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:800; color:#fff; }
.b-ong { background:#F59E0B; } .b-del { background:#10B981; } .b-back { background:#EF4444; }
.b-app { background:#64748B; } .b-pay { background:#3B82F6; } .b-done { background:#059669; }

.weld-body { padding:18px; }
.weld-body h3 { margin:0 0 4px; font-size:16px; color:#0F172A; }
.weld-body .cust { font-size:13px; color:#64748B; margin-bottom:14px; }
.prog-label { display:flex; justify-content:space-between; font-size:12px; font-weight:700; margin-bottom:6px; }
.prog-bar { height:8px; background:#E2E8F0; border-radius:4px; overflow:hidden; margin-bottom:16px; }
.prog-fill { height:100%; background:linear-gradient(90deg,#F59E0B,#D97706); transition:width .5s; }

.weld-meta { font-size:12px; color:#64748B; margin-bottom:14px; }
.weld-meta span { margin-right:12px; }

.btn-update { background:#0F172A; color:#fff; border:none; padding:9px 16px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; width:100%; }
.btn-update:hover { background:#1E293B; }

/* UPDATE MODAL */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:#fff; border-radius:20px; padding:32px; max-width:520px; width:100%; max-height:90vh; overflow-y:auto; position:relative; }
.modal-box h3 { margin:0 0 20px; font-size:18px; color:#0F172A; }
.modal-close { position:absolute; top:16px; right:16px; background:#F1F5F9; border:none; border-radius:50%; width:32px; height:32px; cursor:pointer; font-size:18px; }
.mform label { font-size:13px; font-weight:700; color:#374151; margin-bottom:6px; display:block; }
.mform input, .mform select, .mform textarea { width:100%; padding:10px 14px; border-radius:10px; border:1px solid #E2E8F0; font-family:inherit; font-size:14px; box-sizing:border-box; margin-bottom:14px; }
.mform input:focus, .mform select:focus { border-color:#F59E0B; outline:none; }
.prog-slider { -webkit-appearance:none; height:8px; border-radius:4px; background:#E2E8F0; }
.prog-slider::-webkit-slider-thumb { -webkit-appearance:none; width:20px; height:20px; border-radius:50%; background:#F59E0B; cursor:pointer; }
.submit-btn { background:#F59E0B; color:#fff; border:none; padding:12px; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; width:100%; }
.submit-btn:hover { background:#D97706; }
</style>
</head>
<body>

<div class="main weld-page">

    <div class="weld-header">
        <h2><i class="fas fa-hard-hat" style="color:#F59E0B;margin-right:8px;"></i>My Assigned Projects</h2>
        <p>Update progress, timeline, and materials for your assigned projects.</p>
    </div>

    <?php if (empty($projects)): ?>
        <div style="text-align:center;padding:80px;color:#94A3B8;">
            <i class="fas fa-tools" style="font-size:48px;margin-bottom:16px;display:block;opacity:.3;"></i>
            No projects assigned to you yet.
        </div>
    <?php else: ?>

    <div class="weld-grid">
        <?php foreach ($projects as $p): ?>
            <?php
                $pct = progressPct($p['status']);
                $badgeClass = match($p['status']) {
                    'On-going' => 'b-ong', 'For Delivery' => 'b-del', 'Backjobs' => 'b-back',
                    'Initial Payment' => 'b-pay', 'Completed' => 'b-done', default => 'b-app'
                };
            ?>
            <div class="weld-card">
                <div class="weld-thumb">
                    <img src="../<?= $p['image'] ?? 'assets/images/no-image.png' ?>" alt="Project" onerror="this.src='../assets/images/no-image.png'">
                    <span class="weld-badge <?= $badgeClass ?>"><?= $p['status'] ?></span>
                </div>
                <div class="weld-body">
                    <h3><?= htmlspecialchars($p['project_name'] ?? 'Project #' . $p['id']) ?></h3>
                    <p class="cust"><i class="fas fa-user" style="color:#F59E0B;"></i> <?= htmlspecialchars($p['customer_name'] ?? 'N/A') ?></p>

                    <div class="prog-label">
                        <span>Progress</span>
                        <span><?= $pct ?>%</span>
                    </div>
                    <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>

                    <div class="weld-meta">
                        <?php if ($p['category']): ?><span><i class="fas fa-tag"></i> <?= htmlspecialchars($p['category']) ?></span><?php endif; ?>
                        <?php if ($p['estimated_completion']): ?><span><i class="fas fa-calendar-check"></i> Due: <?= date('M d, Y', strtotime($p['estimated_completion'])) ?></span><?php endif; ?>
                    </div>

                    <button class="btn-update" onclick='openUpdate(<?= json_encode([
                        "id" => $p["id"],
                        "name" => $p["project_name"] ?? "Project #" . $p["id"],
                        "status" => $p["status"],
                        "pct" => $pct,
                        "est" => $p["estimated_completion"] ?? ""
                    ]) ?>)'>
                        <i class="fas fa-edit"></i> Update Project
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- UPDATE MODAL -->
<div class="modal-overlay" id="updateModal">
    <div class="modal-box">
        <button class="modal-close" onclick="document.getElementById('updateModal').classList.remove('open')">×</button>
        <h3><i class="fas fa-edit" style="color:#F59E0B;margin-right:8px;"></i>Update: <span id="modalProjectName"></span></h3>
        <form class="mform" action="api/welder_update.php" method="POST">
            <input type="hidden" name="order_id" id="modalOrderId">

            <label>Status</label>
            <select name="status" id="modalStatus">
                <option value="Initial Payment">Initial Payment</option>
                <option value="On-going">On-going</option>
                <option value="For Delivery">For Delivery</option>
                <option value="Backjobs">Backjobs</option>
                <option value="Completed">Completed</option>
            </select>

            <label>Progress: <span id="pctLabel">0</span>%</label>
            <input type="range" name="progress_pct" class="prog-slider" min="0" max="100" step="5" value="0" id="pctSlider"
                   oninput="document.getElementById('pctLabel').textContent = this.value">

            <label>Estimated Completion Date</label>
            <input type="date" name="estimated_completion" id="modalEstDate">

            <label>Notes / Update Remarks</label>
            <textarea name="remarks" rows="3" placeholder="Describe what was done, issues, next steps..."></textarea>

            <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Save Update</button>
        </form>
    </div>
</div>

<script>
function openUpdate(data) {
    document.getElementById('modalOrderId').value = data.id;
    document.getElementById('modalProjectName').textContent = data.name;
    document.getElementById('modalStatus').value = data.status;
    document.getElementById('pctSlider').value = data.pct;
    document.getElementById('pctLabel').textContent = data.pct;
    document.getElementById('modalEstDate').value = data.est;
    document.getElementById('updateModal').classList.add('open');
}
document.getElementById('updateModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>
