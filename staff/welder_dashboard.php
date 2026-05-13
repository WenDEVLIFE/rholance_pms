<?php
require_once '../includes/auth_check.php';
include '../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['role'] !== 'welder') { header("Location: ../index.php"); exit; }

$wid = $_SESSION['user_id'];
$list = [];

if (!$conn->connect_error) {
    $projs = $conn->prepare("
        SELECT co.*,u.name cust FROM tasks t
        JOIN custom_orders co ON co.id=t.order_id
        LEFT JOIN users u ON u.id=co.customer_id
        WHERE t.assigned_to=? ORDER BY FIELD(co.status,'On-going','Initial Payment','For Delivery','Backjobs','Appointment','Completed'), co.created_at DESC
    ");
    if ($projs) {
        $projs->bind_param("i",$wid); $projs->execute();
        $list = $projs->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    $statsStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN co.status = 'On-going' THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN co.status = 'Completed' THEN 1 ELSE 0 END) as done
        FROM tasks t
        JOIN custom_orders co ON co.id = t.order_id
        WHERE t.assigned_to = ?
    ");
    $statsStmt->bind_param("i", $wid); $statsStmt->execute();
    $ws = $statsStmt->get_result()->fetch_assoc();
} else {
    $ws = ['total'=>0,'ongoing'=>0,'done'=>0];
}

function pct($s){return['Appointment'=>10,'Initial Payment'=>30,'On-going'=>60,'For Delivery'=>85,'Backjobs'=>50,'Completed'=>100][$s]??0;}
?>

<div class="rh-main">
    <div class="rh-page-header">
        <h1>Welder Dashboard</h1>
        <p>Manage your assigned fabrication tasks and progress updates.</p>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-blue"><i class="fas fa-tasks"></i></div>
                <div><div class="rh-stat-label">Total Tasks</div><div class="rh-stat-value"><?= $ws['total'] ?? 0 ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-amber"><i class="fas fa-hammer"></i></div>
                <div><div class="rh-stat-label">On-going</div><div class="rh-stat-value"><?= $ws['ongoing'] ?? 0 ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-green"><i class="fas fa-check-double"></i></div>
                <div><div class="rh-stat-label">Completed</div><div class="rh-stat-value"><?= $ws['done'] ?? 0 ?></div></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="rh-stat-card">
                <div class="rh-stat-icon bg-purple"><i class="fas fa-clock"></i></div>
                <div><div class="rh-stat-label">Last Activity</div><div class="rh-stat-value" style="font-size:0.9rem;">Today</div></div>
            </div>
        </div>
    </div>

    <h5 class="fw-800 mb-3">My Assigned Projects</h5>


    <?php if (empty($list)): ?>
        <div class="card p-5 text-center text-muted">
            <i class="fas fa-tools fs-1 mb-3 opacity-25 d-block"></i>
            No projects assigned to you yet.
        </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($list as $p):
            $pc = pct($p['status']);
            $cls = 'badge-'.strtolower(str_replace([' ','/'],'-',$p['status']));
        ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="rh-proj-card">
                <div class="rh-proj-thumb">
                    <img src="../<?= $p['image']??'assets/images/no-image.png' ?>"
                         onerror="this.src='../assets/images/no-image.png'" alt="">
                    <span class="badge <?= $cls ?> status-float"><?= $p['status'] ?></span>
                </div>
                <div class="rh-proj-body">
                    <h6><?= htmlspecialchars($p['project_name']??'Project #'.$p['id']) ?></h6>
                    <p class="proj-meta"><i class="fas fa-user fa-xs text-amber me-1"></i><?= htmlspecialchars($p['cust']??'—') ?></p>

                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;font-weight:700;">
                        <span>Progress</span><span><?= $pc ?>%</span>
                    </div>
                    <div class="progress mb-3" style="height:7px;">
                        <div class="progress-bar" style="width:<?= $pc ?>%"></div>
                    </div>

                    <?php if ($p['estimated_completion']): ?>
                        <div class="small text-muted mb-2"><i class="far fa-calendar me-1"></i>Due: <?= date('M d, Y',strtotime($p['estimated_completion'])) ?></div>
                    <?php endif; ?>

                    <div class="rh-proj-footer">
                        <button class="btn btn-sm btn-primary w-100"
                            data-bs-toggle="modal" data-bs-target="#updateModal"
                            onclick='loadUpdate(<?= json_encode(["id"=>$p["id"],"name"=>$p["project_name"]??"Project #".$p["id"],"status"=>$p["status"],"pct"=>$pc,"est"=>$p["estimated_completion"]??""]) ?>)'>
                            <i class="fas fa-edit me-1"></i>Update Project
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- UPDATE MODAL -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2 text-amber"></i>Update: <span id="upName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api/welder_update.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="upId">

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="upStatus" class="form-select">
                            <?php foreach (['Initial Payment','On-going','For Delivery','Backjobs','Completed'] as $st): ?>
                            <option value="<?= $st ?>"><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between">
                            <span>Progress</span>
                            <span id="pctVal" class="text-amber fw-700">0%</span>
                        </label>
                        <input type="range" name="progress_pct" id="pctRange"
                               class="form-range" min="0" max="100" step="5" value="0"
                               oninput="document.getElementById('pctVal').textContent=this.value+'%'">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estimated Completion Date</label>
                        <input type="date" name="estimated_completion" id="upEst" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Update Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  placeholder="Describe work done, issues, next steps..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadUpdate(d) {
    document.getElementById('upId').value     = d.id;
    document.getElementById('upName').textContent = d.name;
    document.getElementById('upStatus').value = d.status;
    document.getElementById('pctRange').value = d.pct;
    document.getElementById('pctVal').textContent = d.pct + '%';
    document.getElementById('upEst').value    = d.est;
}
</script>
</body></html>
